<?php

namespace Sygefor\Bundle\TaxonomyBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Constraints\CallbackValidator;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TaxonomyController
 * @package Sygefor\Bundle\TaxonomyBundle\Controller
 * @Route("/admin/taxonomy")
 */
class TaxonomyController extends Controller
{
    /**
     * @Route("/", name="taxonomy.index")
     * @Template()
     */
    public function indexAction()
    {
        if( $this->get('security.context')->isGranted('VIEW', 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface') || $this->get('security.context')->isGranted('VIEW', 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface') ) {
            return array('vocabularies' => $this->getVocabulariesList());
        } else {
            throw new AccessDeniedException('');
        }
    }

    /**
     * @Route("/{id}/view/{organization_id}", name="taxonomy.view_by_org",requirements={"organization_id" = "\d+"})
     * @Template("SygeforTaxonomyBundle:Taxonomy:view.html.twig")
     * @Security("is_granted('VIEW', 'Sygefor\\Bundle\\TaxonomyBundle\\Vocabulary\\LocalVocabularyInterface')")
     */
    public function viewOrganizationVocabularyAction($id, $organization_id)
    {
        /**
         * @var AbstractTerm $vocabulary
         */
        $vocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($id);
        $sortable = $vocabulary::orderBy() == 'position';

        /**
         * @var ObjectRepository $repo
         */
        $orgRepo = $this->getDoctrine()->getManager()->getRepository('SygeforCoreBundle:Organization');
        $organization = $orgRepo->find($organization_id);

        $userCanViewAll = true;

        if (!$vocabulary->isNational()) {
            $vocabulary->setOrganization($organization);
        }

        //checking if user is allowed here
        if ($this->get('security.context')->isGranted('VIEW', $vocabulary)) {
            if (!$vocabulary->isNational()) {
                $organizations = $orgRepo->findAll();
                if (null == $organization) {
                    throw new EntityNotFoundException("l'urfist demandée n'existe pas");
                }
                //checking access to another org vocabulary
                $altVocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($id);
                foreach ($organizations as $org) {
                    if ($org->getId() != $organization_id) {
                        $altVocabulary->setOrganization($org);
                        if (!$this->get('security.context')->isGranted('VIEW', $vocabulary)) {
                            $userCanViewAll = false;
                        }
                    }
                }

                $terms = $this->getRootTerms($vocabulary, $organization);
            } else {
                return $this->redirect($this->generateUrl('taxonomy.view', array('id' => $id)));
            }

            return array(
                'organization' => $organization,
                'organizations' => $userCanViewAll ? $organizations : null,
                'id' => $id,
                'vocabulary' => $vocabulary,
                'depth' => method_exists($vocabulary, 'getChildren') ? 2 : 1,
                'terms' => $terms,
                'vocabularies' => $this->getVocabulariesList(),
                'sortable' => $sortable
            );
        } else {
            throw new AccessDeniedException("Accès non autorisé");
        }
    }

    /**
     * @Route("/{id}/view", name="taxonomy.view")
     * @Template("SygeforTaxonomyBundle:Taxonomy:view.html.twig")
     */
    public function viewVocabularyAction($id)
    {
        /**
         * @var AbstractTerm $vocabulary
         */
        $vocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($id);
        $sortable = $vocabulary::orderBy() == 'position';

        //local vocabulary
        if (!$vocabulary->isNational())  {
            $userOrg = $this->getUser()->getOrganization();
            //if user has no organization but can view local vocabularies, lets redirect him to dedicated action
            if ($this->get('security.context')->isGranted('VIEW', 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface')) {

                //$org = $this->getDoctrine()->getManager()->getRepository('SygeforCoreBundle:Organization')->findOneBy(array());
                if ($userOrg) { //at least one organization was found
                    return $this->redirect($this->generateUrl('taxonomy.view_by_org', array('id' => $id, 'organization_id' => $userOrg->getId())));
                } else {
                    return $this->redirect($this->generateUrl('sygefor_taxonomy.list'));
                }
            } else {
                if (!empty($userOrg)) { //otherwise if he belongs to an organization. Lets show him vocabulary terms for this organization
                    $vocabulary->setOrganization($userOrg);
                    if ($this->get('security.context')->isGranted('VIEW', $vocabulary)) {
                        $terms = $this->getRootTerms($vocabulary, $userOrg);
                    } else {
                        return $this->redirect($this->generateUrl('taxonomy.view_by_org', array('id' => $id, 'organization_id' => $userOrg)));
                    }

                } else { //user is not granted rights to be here
                    throw new AccessDeniedException("Accès non autorisé");
                }
            }

        } else {
            if ($this->get('security.context')->isGranted('VIEW', 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface')) {
                //vocabulary is national. If user has appropriate rights, lets show him terms
                $terms = $this->getRootTerms($vocabulary);
            } else {
                throw new AccessDeniedException("Accès non autorisé");
            }
        }

        return array(
            'id' => $id,
            'vocabulary' => $vocabulary,
            'depth' => method_exists($vocabulary, 'getChildren') ? 2 : 1,
            'terms' => $terms,
            'vocabularies' => $this->getVocabulariesList(),
            'sortable' => $sortable
        );
    }

    /**
     * @Route("/{id}/organization/{organization_id}/add", name="taxonomy.addLocal")
     * @Route("/{id}/organization/{organization_id}/edit", name="taxonomy.editLocal")
     * @Route("/{id}/add", name="taxonomy.addNational")
     * @Route("/{id}/edit/{term_id}", name="taxonomy.editNational")
     * @Template("SygeforTaxonomyBundle:Taxonomy:edit.html.twig")
     */
    public function editVocabularyTermAction(Request $request, $id, $organization_id = null, $term_id = null)
    {
        /**
         * @var NationalVocabularyInterface $vocabulary
         */
        $vocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($id);
        $organization = null;

        //setting organization if id given in url
        if (!$vocabulary->isNational()){

            if ($organization_id != null) {
                /**
                 * @var ObjectRepository $repo
                 */
                $organization = $this->getDoctrine()->getManager()->find('SygeforCoreBundle:Organization', $organization_id);
                $vocabulary->setOrganization($organization);
            } else {
                $vocabulary->setOrganization($this->getUser()->getOrganization());
            }
        }

        if ($this->get('security.context')->isGranted('EDIT', $vocabulary)) {
            $vocClass = get_class($vocabulary);

            if ($term_id != null) {
                $vocTerm = $this->getDoctrine()->getManager()->find(get_class($vocabulary),$term_id);
            } else {
                $vocTerm = new $vocClass;
            }

            //if organization exists, setting it in term to be created
            if ($organization != null) {
                $vocTerm->setOrganization($organization);
            }

            // protected term
            if($vocTerm->getName() == 'Autre') {
                throw new AccessDeniedException();
            }

            $formType = 'vocabulary';
            //checking if for specific form type exists for vocabulary
            if (method_exists($vocabulary, 'getFormType')) {
                $formType = $vocabulary::getFormType();
            }

            $form = $this->createForm( $formType, $vocTerm) ;

            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($vocTerm);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('success', 'Le terme a bien été mis à jour.');

                    $organization_id = null;
                    if (!$vocabulary->isNational()) {
                        $organization_id = $vocTerm->getOrganization()->getId();
                    }

                    if ($organization_id) {
                        return $this->redirect($this->generateUrl('taxonomy.view_by_org', array('organization_id'=>$organization_id,'id'=>$id)));
                    } else {
                        return $this->redirect($this->generateUrl('taxonomy.view', array('id'=>$id)));
                    }
                }
            }

            return array(
                'vocabulary' => $vocabulary,
                'term' => $vocTerm,
                'id' => $id,
                'form' => $form->createView(),
                'vocabularies' => $this->getVocabulariesList()
            );
        } else {
            throw new AccessDeniedException('Accès non autorisé');
        }
    }

    /**
     * @Route("/{id}/remove/{term_id}", name="taxonomy.remove")
     * @Template("SygeforTaxonomyBundle:Taxonomy:remove.html.twig")
     */
    public function removeAction($id,$term_id,Request $request)
    {
        $vocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($id);
        $vocClass = get_class($vocabulary);
        $vocTerm = $this->getDoctrine()->getManager()->getRepository($vocClass)->find($term_id);
        if(!$vocTerm) {
            throw new NotFoundHttpException();
        }

        // protected term
        if($vocTerm->getName() == 'Autre') {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $count = $this->get('sygefor_taxonomy.vocabulary_registry')->getTermUsages($this->getDoctrine()->getManager(), $vocTerm);

        $formB = $this->createFormBuilder(null, array("validation_groups" => array('taxonomy_term_remove')));
        $constraint = new NotBlank(array("message" => "Vous devez sélectionner un terme de substitution"));
        $constraint->addImplicitGroupName('taxonomy_term_remove');

        // build query
        $queryBuilder = $em->createQueryBuilder('s')
          ->select('t')
          ->from($vocClass, 't')
          ->where('t.id != :id')->setParameter('id', $term_id)
          ->orderBy('t.' . $vocabulary::orderBy());

        // local : limit to local terms
        if(!$vocabulary->isNational()) {
            $queryBuilder
              ->andWhere('t.organization = :organization')
              ->setParameter('organization', $vocTerm->getOrganization());
        }

        //if entities are linked to current
        if ($count > 0) {
            $formB
                ->add('term', 'entity',
                    array(
                      'class' => $vocClass,
                      'expanded' => true,
                      'label' => 'Terme de substitution',
                      'required' => false,
                      'constraints' => $constraint,
                      'query_builder' => $queryBuilder
                )
            );
        }
        $form = $formB->getForm();

        if (!$this->get('security.context')->isGranted('REMOVE', $vocTerm)) {
            throw new AccessDeniedException("");
        }

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()){
                $em = $this->getDoctrine()->getManager();

                if ( $form->has('term')) {
                    $this->get('sygefor_taxonomy.vocabulary_registry')->replaceTermInUsages(
                        $this->getDoctrine()->getManager(),
                        $vocTerm,
                        $form->get('term')->getData());
                }
                $em->remove($vocTerm);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Le terme a bien été supprimé.');
                if (!$vocabulary->isNational()) {
                    return $this->redirect($this->generateUrl('taxonomy.view_by_org', array('id' => $id, 'organization_id' => $vocTerm->getOrganization()->getId())));
                } else {
                    return $this->redirect($this->generateUrl('taxonomy.view', array('id' => $id)));
                }

            }

        }

        return array(
            'vocabulary' => $vocabulary,
            'id' => $id,
            'term' => $vocTerm,
            'vocabularies' => $this->getVocabulariesList(),
            'count'=>$count,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/{id}/terms/order", name="taxonomy.terms_order", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("vocabulary")
     * @Method({"POST"})
     * @Rest\View
     */
    public function termsOrderAction($vocabulary, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(get_class($vocabulary));
        $serialized = $request->get('serialized');
        $process = function($objects, $parent = null) use ($em, $repository, &$process) {
            $pos = 0;
            foreach($objects as $object) {
                /** @var TreeTrait $entity */
                $entity = $repository->find($object['id']);
                if(method_exists($entity, 'setParent')) {
                    $entity->setParent($parent);
                }
                if(method_exists($entity, 'setPosition')) {
                    $entity->setPosition($pos++);
                }
                //$entity->setParent($parent);
                $em->persist($entity);
                if(isset($object['children'])) {
                    $process($object['children'], $entity);
                }
            }
        };
        $process($serialized);
        $em->flush();
    }

    /**
     * Return the terms for a specified vocabulary, filter by an organization
     * For tree vocabulary, only root ones
     *
     * @param $vocabulary
     * @param null $organization
     * @return mixed
     */
    private function getRootTerms($vocabulary, $organization = null)
    {
        $class = get_class($vocabulary);
        $repository = $this->getDoctrine()->getManager()->getRepository($class);

        if($repository instanceof NestedTreeRepository) {
            $qb = $repository->getRootNodesQueryBuilder('position');
        } else {
            $qb = $repository->createQueryBuilder('node');
            $qb->orderBy('node.' . $vocabulary::orderBy(), 'ASC');
        }

        if($organization) {
            $qb->where('node.organization = :organization')
              ->setParameter('organization', $organization);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array list of allowed vocabularies, grouped by existing groups
     */
    private function getVocabulariesList()
    {
        $vocRegistry = $this->get('sygefor_taxonomy.vocabulary_registry');
        $vocsGroups = $vocRegistry->getGroups();

        $userOrg = $this->get("security.context")->getToken()->getUser()->getOrganization();

        //getting vocabularies list, grouped by vocabularies groups
        $vocNames = array () ;
        foreach ($vocsGroups as $group => $vocs) {
            $vocNames[$group] = array ();
            foreach ($vocs as $vid => $voc) {

                if (!$voc->isNational() && !empty ($userOrg)) {
                    $voc->setOrganization($userOrg);
                }

                if ($this->get('security.context')->isGranted('VIEW', $voc)) {
                    $vocNames[$group] []= array ('id'=>$vid, 'vocabulary'=>$voc, 'scope'=>( $voc->isNational() ) ? 'N' : 'L' );
                }
            }
            //ordering list
            usort($vocNames[$group], function ($a, $b){
                return ($a['vocabulary']->getVocabularyName() > $b['vocabulary']->getVocabularyName()) ? 1 : -1;
            });
        }

        return $vocNames;
    }

    /**
     * @Route("/get_terms/{vocabularyId}", name="taxonomy.get", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     */
    public function getTermsAction($vocabularyId)
    {
        /**
         * @var AbstractTerm $vocabulary
         */
        $vocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($vocabularyId);
        if(!$vocabulary) {
            throw new \InvalidArgumentException("This vocabulary does not exists.");
        }

        //local vocabulary
        if (!$vocabulary->isNational())  {
            //getting local vocabulary
            $userOrg = $this->getUser()->getOrganization();
            $terms = $this->getRootTerms($vocabulary, $userOrg);
        } else {
            $terms = $this->getRootTerms($vocabulary);
        }

        return $terms;
    }
}
