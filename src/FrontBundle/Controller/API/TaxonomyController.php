<?php

namespace FrontBundle\Controller\API;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sygefor\Bundle\CoreBundle\Entity\AbstractOrganization;
use Sygefor\Bundle\CoreBundle\Entity\Term\VocabularyInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\ApiBundle\Controller\TaxonomyController as ApiTaxonomyController;

/**
 * Class TaxonomyController.
 *
 * @Route("/api/taxonomy")
 */
class TaxonomyController extends ApiTaxonomyController
{
    /**
     * Return a public list of terms for a specific(s) vocabulary(ies).
     *
     * @Route("/get/{vocabularies}", name="api.taxonomy.get", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"})
     */
    public function getAction($vocabularies, Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $public_map = array(
            'organization' => AbstractOrganization::class,
            'title' => 'sygefor_core.vocabulary_title',
            'inscriptionStatus' => 'sygefor_core.vocabulary_inscription_status',
            'presenceStatus' => 'sygefor_core.vocabulary_presence_status',
            'theme' => 'sygefor_training.vocabulary_theme',
            'publicType' => 'sygefor_trainee.vocabulary_public_type',
        );

        $return = array();
        $vocabularies = explode(',', $vocabularies);
        foreach ($vocabularies as $key) {
            if (!isset($public_map[$key])) {
                throw new \Exception('This taxonomy does not exist : '.$key);
            }
            $id = $public_map[$key];

            // specific case : organization
            if (class_exists($id)) {
                $return[$key] = $em->getRepository($id)->findBy(array(), array('name' => 'asc'));
                continue;
            }
            // get vocabulary && order parameter
            $vocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($id);
            $order = $vocabulary::orderBy();

            $repository = $em->getRepository(get_class($vocabulary));
            // allow organization parameter if the vocabulary is not national
            $organization = null;
            if ($vocabulary->getVocabularyStatus() !== VocabularyInterface::VOCABULARY_NATIONAL) {
                $organization = $request->get('organization');
            }

            if ($repository instanceof NestedTreeRepository) {
                $qb = $repository->getRootNodesQueryBuilder($order, 'asc');
                $qb->andWhere('node.private = 0');
                $return[$key] = $qb->getQuery()->getResult();
            } else {
                $params = array('private' => false);
                if ($organization) {
                    $params['organization'] = $organization;
                }
                $return[$key] = $repository->findBy($params, array($order => 'asc'));
            }
        }

        return $return;
    }
}
