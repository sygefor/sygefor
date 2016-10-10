<?php

namespace Sygefor\Bundle\CoreBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Entity\Term\TreeTrait;
use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TraineeBundle\Entity\Term\Disciplinary;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TaxonomyController.
 *
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
        if ($this->get('security.context')->isGranted('VIEW', 'Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface')) {
            return array('vocabularies' => $this->getVocabulariesList());
        }
        else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param AbstractTerm $term
     * @param Organization $organization
     *
     * @Route("/{vocabularyId}/view/{organizationId}", name="taxonomy.view", defaults={"organizationId" = null})
     * @Security("is_granted('VIEW', 'Sygefor\\Bundle\\CoreBundle\\Vocabulary\\VocabularyInterface')")
     * @ParamConverter("organization", class="SygeforCoreBundle:Organization", options={"id" = "organizationId"}, isOptional="true")
     * @Template("SygeforCoreBundle:Taxonomy:view.html.twig")
     *
     * @throws EntityNotFoundException
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewVocabularyAction($vocabularyId, $organization = null)
    {
        /** @var AbstractTerm $abstractVocabulary */
        $abstractVocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($vocabularyId);
        $abstractVocabulary->setVocabularyId($vocabularyId);
        // for mixed vocabularies
        $canEditNationalTerms = $this->get('security.context')->isGranted('VIEW', $abstractVocabulary);

        if ($abstractVocabulary->getVocabularyStatus() === VocabularyInterface::VOCABULARY_LOCAL && !$organization) {
            return $this->redirect($this->generateUrl('taxonomy.view', array('vocabularyId' => $vocabularyId, 'organizationId' => $this->get('security.context')->getToken()->getUser()->getOrganization()->getId())));
        }

        // set organization to abstract vocabulary to check access rights
        $abstractVocabulary->setOrganization($organization);
        if (!$this->get('security.context')->isGranted('VIEW', $abstractVocabulary) && !$canEditNationalTerms) {
            // organization required for local vocabularies
            throw new AccessDeniedException('');
        }

        // needed for template organization tabs
        $organizations = array();
        if ($abstractVocabulary->getVocabularyStatus() !== VocabularyInterface::VOCABULARY_NATIONAL) {
            $alterOrganizations = $this->getDoctrine()->getManager()->getRepository('SygeforCoreBundle:Organization')->findAll();
            $alterAbstractVocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($vocabularyId);
            foreach ($alterOrganizations as $alterOrganization) {
                $alterAbstractVocabulary->setOrganization($alterOrganization);
                if ($this->get('security.context')->isGranted('VIEW', $alterAbstractVocabulary)) {
                    $organizations[$alterOrganization->getId()] = $alterOrganization;
                }
            }
        }
        $terms = $this->getRootTerms($abstractVocabulary, $organization);
        if ($organization) {
            foreach ($terms as $key => $term) {
                if (!$term->getOrganization()) {
                    unset($terms[$key]);
                }
            }
        }

        return array(
            'organization' => $organization,
            'organizations' => $organizations,
            'canEditNationalTerms' => $canEditNationalTerms,
            'terms' => $terms,
            'vocabulary' => $abstractVocabulary,
            'vocabularies' => $this->getVocabulariesList(),
            'sortable' => $abstractVocabulary::orderBy() === 'position',
            'depth' => method_exists($abstractVocabulary, 'getChildren') ? 2 : 1,
        );
    }

    /**
     * @Route("/{vocabularyId}/edit/{id}/{organizationId}", name="taxonomy.edit", defaults={"id" = null, "organizationId" = null})
     * @Security("is_granted('EDIT', 'Sygefor\\Bundle\\CoreBundle\\Vocabulary\\VocabularyInterface')")
     * @Template("SygeforCoreBundle:Taxonomy:edit.html.twig")
     */
    public function editVocabularyTermAction(Request $request, $vocabularyId, $organizationId, $id = null)
    {
        $organization = null;
        if ($organizationId) {
            $organization = $this->getDoctrine()->getManager()->getRepository('SygeforCoreBundle:Organization')->find($organizationId);
        }
        $term = null;
        $abstractVocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($vocabularyId);
        $abstractVocabulary->setVocabularyId($vocabularyId);
        $termClass = get_class($abstractVocabulary);
        $em = $this->getDoctrine()->getManager();

        // find term
        if ($id) {
            $term = $em->find($termClass, $id);
        }

        // create term if not found
        if (!$term) {
            $term = new $termClass();
            $term->setOrganization($organization);
        }

        if (!$this->get('security.context')->isGranted('EDIT', $term)) {
            throw new AccessDeniedException();
        }

        // get term from
        $formType = VocabularyType::class;
        if (method_exists($abstractVocabulary, 'getFormType')) {
            $formType = $abstractVocabulary::getFormType();
        }

        $form = $this->createForm($formType, $term);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $term->setOrganization($organization);
                $em->persist($term);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Le terme a bien été enregistré.');

                $organization_id = null;
                if ($organization) {
                    $organization_id = $organization->getId();
                }

                return $this->redirect($this->generateUrl('taxonomy.view', array('vocabularyId' => $vocabularyId, 'organizationId' => $organization_id)));
            }
        }

        return array(
            'vocabulary' => $abstractVocabulary,
            'organization' => $organization,
            'term' => $term,
            'id' => $id,
            'form' => $form->createView(),
            'vocabularies' => $this->getVocabulariesList(),
        );
    }

    /**
     * @Route("/{vocabularyId}/remove/{id}", name="taxonomy.remove")
     * @Security("is_granted('REMOVE', 'Sygefor\\Bundle\\CoreBundle\\Vocabulary\\VocabularyInterface')")
     * @Template("SygeforCoreBundle:Taxonomy:remove.html.twig")
     */
    public function removeAction(Request $request, $vocabularyId, $id)
    {
        $abstractVocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($vocabularyId);
        $abstractVocabulary->setVocabularyId($vocabularyId);
        $termClass = get_class($abstractVocabulary);
        $em = $this->getDoctrine()->getManager();

        // find term
        $term = $em->find($termClass, $id);
        if (!$term) {
            throw new NotFoundHttpException();
        }

        // protected term because needed for special system operations
        if ($term->isLocked()) {
            throw new AccessDeniedException("This term can't be removed");
        }

        if (!$this->get('security.context')->isGranted('REMOVE', $term)) {
            throw new AccessDeniedException();
        }

        // get term usage
        $count = $this->get('sygefor_core.vocabulary_registry')->getTermUsages($em, $term);

        $formB = $this->createFormBuilder(null, array('validation_groups' => array('taxonomy_term_remove')));
        $constraint = new NotBlank(array('message' => 'Vous devez sélectionner un terme de substitution'));
        $constraint->addImplicitGroupName('taxonomy_term_remove');

        // build query
        $queryBuilder = $em->createQueryBuilder('s')
            ->select('t')
            ->from($termClass, 't')
            ->where('t.id != :id')->setParameter('id', $id)
            ->orderBy('t.' . $abstractVocabulary::orderBy());
        if ($term->getOrganization()) {
            $queryBuilder
                ->andWhere('t.organization = :organization')
                ->setParameter('organization', $term->getOrganization());
        }
        $queryBuilder->orWhere('t.organization is null');

        //if entities are linked to current
        if ($count > 0) {
            $required = !empty($abstractVocabulary::$replacementRequired);
            $formB
                ->add('term', 'entity',
                    array(
                        'class' => $termClass,
                        'expanded' => true,
                        'label' => 'Terme de substitution',
                        'required' => $required,
                        'constraints' => $required ? $constraint : null,
                        'query_builder' => $queryBuilder,
                        'empty_value' => $required ? null : '- Aucun -',
                    )
                );
        }

        $organization_id = null;
        if ($term->getOrganization()) {
            $organization_id = $term->getOrganization()->getId();
        }

        $form = $formB->getForm();
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($form->has('term')) {
                    $newTerm = $form->get('term')->getData();
                    if ($newTerm) {
                        $this->get('sygefor_core.vocabulary_registry')->replaceTermInUsages(
                            $em,
                            $term,
                            $newTerm);
                    }
                }
                $em->remove($term);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Le terme a bien été supprimé.');

                return $this->redirect($this->generateUrl('taxonomy.view', array('vocabularyId' => $vocabularyId, 'organizationId' => $organization_id)));
            }

        }

        return array(
            'vocabulary' => $abstractVocabulary,
            'organization' => $term->getOrganization(),
            'organization_id' => $organization_id,
            'term' => $term,
            'vocabularies' => $this->getVocabulariesList(),
            'count' => $count,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{vocabulary}/terms/order", name="taxonomy.terms_order", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method({"POST"})
     * @Rest\View
     */
    public function termsOrderAction($vocabulary, Request $request)
    {
        $abstractVocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($vocabulary);
        $abstractVocabulary->setVocabularyId($vocabulary);
        $termClass = get_class($abstractVocabulary);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository($termClass);
        $serialized = $request->get('serialized');
        $process = function ($objects, $parent = null) use ($em, $repository, &$process) {
            $pos = 0;
            foreach ($objects as $object) {
                /** @var TreeTrait $entity */
                $entity = $repository->find($object['id']);
                if (method_exists($entity, 'setParent')) {
                    $entity->setParent($parent);
                }
                if (method_exists($entity, 'setPosition')) {
                    $entity->setPosition($pos++);
                }
                //$entity->setParent($parent);
                $em->persist($entity);
                if (isset($object['children'])) {
                    $process($object['children'], $entity);
                }
            }
        };

        $process($serialized);
        $em->flush();
    }

    /**
     * Return the terms for a specified vocabulary, filter by an organization
     * For tree vocabulary, only root ones.
     *
     * @param $vocabulary
     * @param null $organization
     *
     * @return mixed
     */
    private function getRootTerms($vocabulary, $organization)
    {
        $class = get_class($vocabulary);
        $repository = $this->getDoctrine()->getManager()->getRepository($class);

        if ($repository instanceof NestedTreeRepository) {
            $qb = $repository->getRootNodesQueryBuilder('position');
        }
        else {
            $qb = $repository->createQueryBuilder('node');
            $qb->orderBy('node.' . $vocabulary::orderBy(), 'ASC');
        }

        if ($vocabulary->getVocabularyStatus() !== VocabularyInterface::VOCABULARY_NATIONAL) {
            if ($organization) {
                $qb->where('node.organization = :organization')
                    ->setParameter('organization', $organization)
                    ->orWhere('node.organization is null');
            }
            else {
                $qb->where('node.organization is null');
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array list of allowed vocabularies, grouped by existing groups
     */
    private function getVocabulariesList()
    {
        $vocRegistry = $this->get('sygefor_core.vocabulary_registry');
        $vocsGroups = $vocRegistry->getGroups();
        $userOrg = $this->get('security.context')->getToken()->getUser()->getOrganization();

        //getting vocabularies list, grouped by vocabularies groups
        $vocNames = array();
        foreach ($vocsGroups as $group => $vocs) {
            foreach ($vocs as $vid => $voc) {
                if ($voc->getVocabularyStatus() !== VocabularyInterface::VOCABULARY_NATIONAL && !empty($userOrg)) {
                    $voc->setOrganization($userOrg);
                }
                if ($this->get('security.context')->isGranted('VIEW', $voc)) {
                    $label = $vocRegistry->getVocabularyLabel($vid);
                    $voc->setVocabularyLabel($label);
                    $vocNames[] = array('id' => $vid, 'vocabulary' => $voc, 'name' => $voc->getVocabularyLabel(), 'scope' => $voc->getVocabularyStatus());
                }
            }
        }

        //ordering list
        usort($vocNames, function ($a, $b) {
            return $a['vocabulary']->getVocabularyLabel() > $b['vocabulary']->getVocabularyLabel();
        });

        return $vocNames;
    }

    /**
     * @Route("/get_terms/{vocabularyId}", name="taxonomy.get", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     */
    public function getTermsAction($vocabularyId)
    {
        /*
         * @var AbstractTerm
         */
        $vocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($vocabularyId);
        if (!$vocabulary) {
            throw new \InvalidArgumentException('This vocabulary does not exists.');
        }

        $userOrg = $this->getUser()->getOrganization();
        $terms = $this->getRootTerms($vocabulary, $userOrg);

        return $terms;
    }
}
