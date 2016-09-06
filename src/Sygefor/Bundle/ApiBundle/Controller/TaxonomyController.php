<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaxonomyController
 * @package Sygefor\Bundle\TaxonomyBundle\Controller
 * @Route("/api/taxonomy")
 */
class TaxonomyController extends Controller
{
    /**
     * Return a public list of terms for a specific(s) vocabulary(ies)
     *
     * @Route("/get/{vocabularies}", name="api.taxonomy.get", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"})
     */
    public function getAction($vocabularies, Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $public_map = array(
          'organization' => 'Sygefor\Bundle\CoreBundle\Entity\Organization',
          'title' => 'sygefor_trainee.vocabulary_title',
          'publicType' => 'sygefor_core.vocabulary_publictype',
          'teachingCursus' => 'sygefor_training.vocabulary_teachingcursus',
          'disciplinary' => 'sygefor_core.vocabulary_disciplinary',
          'competenceField' => 'sygefor_trainer.vocabulary_competence_field',
          'inscriptionStatus' => 'sygefor_trainee.vocabulary_inscription_status',
          'presenceStatus' => 'sygefor_trainee.vocabulary_presence_status',
          'institution' => 'sygefor_training.vocabulary_institution',
          'theme' => 'sygefor_training.vocabulary_theme',
          'evaluationCriterion' => 'sygefor_trainee.vocabulary_evaluation_criterion'
        );

        $return = array();
        $vocabularies = explode(",", $vocabularies);
        foreach($vocabularies as $key) {
            if(!isset($public_map[$key])) {
                throw new \Exception("This taxonomy does not exist : " . $key);
            }
            $id = $public_map[$key];

            // specific case : organization
            if(class_exists($id)) {
                $return[$key] = $em->getRepository($id)->findBy(array(), array('name' => 'asc'));
                continue;
            }

            // get vocabulary && order parameter
            $vocabulary = $this->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById($id);
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
