<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Elastica\Filter\Term;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class TaxonomyController.
 *
 * @Route("/api/taxonomy")
 */
class TaxonomyController extends Controller
{
    /**
     * Return a public list of terms for a specific(s) vocabulary(ies).
     *
     * @Route("/get/{vocabularies}", name="api.taxonomy.get", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"})
     */
    public function getAction($vocabularies, Request $request)
    {
        $em         = $this->get('doctrine')->getManager();
        $public_map = array(
            'organization'      => 'Sygefor\Bundle\CoreBundle\Entity\Organization',
            'title'             => 'sygefor_trainee.vocabulary_title',
            'publicType'        => 'sygefor_trainee.vocabulary_public_type',
            'inscriptionStatus' => 'sygefor_trainee.vocabulary_inscription_status',
            'presenceStatus'    => 'sygefor_trainee.vocabulary_presence_status',
            'theme'             => 'sygefor_training.vocabulary_theme',
        );

        $return       = array();
        $vocabularies = explode(',', $vocabularies);
        foreach ($vocabularies as $key) {
            // special case : institution
            if ($key === 'institution') {
                $return[$key] = $this->getInstitutions();
                continue;
            }

            if (!isset($public_map[$key])) {
                throw new \Exception('This taxonomy does not exist : ' . $key);
            }
            $id = $public_map[$key];

            // specific case : organization
            if (class_exists($id)) {
                $return[$key] = $em->getRepository($id)->findBy(array(), array('name' => 'asc'));
                continue;
            }
            // get vocabulary && order parameter
            $vocabulary = $this->get('sygefor_core.vocabulary_registry')->getVocabularyById($id);
            $order      = $vocabulary::orderBy();

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
            }
            else {
                $params = array('private' => false);
                if ($organization) {
                    $params['organization'] = $organization;
                }
                $return[$key] = $repository->findBy($params, array($order => 'asc'));
            }
        }

        return $return;
    }

    /**
     * workaround : retrieve institutions list from es.
     */
    function getInstitutions()
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_institution.search');
        $search->setSize(99999);

        // limit available source fields
        $search->setSource(array('id', 'name', 'organization.id'));

        // only validated institutions
        $filter = new Term(array('validated' => true));
        $search->filterQuery($filter);

        $data  = $search->search();
        $items = $data['items'];

        return $items;
    }
}
