<?php
namespace Sygefor\Bundle\TrainingBundle\Transformer;

use Doctrine\ORM\EntityManager;
use Elastica\Document;
use Sygefor\Bundle\ElasticaBundle\Transformer\ModelToElasticaTransformer;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SessionToElasticaTransformer
 * @package Sygefor\Bundle\TrainingBundle\Transformer
 */
class SessionToElasticaTransformer extends ModelToElasticaTransformer
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @param Container $container
     * @param array $options
     */
    public function __construct(Container $container, $options = array())
    {
        $this->container = $container;
        parent::__construct($options);
    }

    /**
     * @param Session $session
     * @param array $fields
     * @return Document
     */
    function transform($session, array $fields)
    {
        $document = parent::transform($session, $fields);

        /**
         * Add inscriptionStats
         */
        if($session instanceof Session) {
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine')->getManager();
            $stats = array();
            if($session->getRegistration() > Session::REGISTRATION_DEACTIVATED) {
                $query = $em
                  ->createQuery('SELECT s, count(i) FROM SygeforTraineeBundle:Term\\InscriptionStatus s
                    JOIN SygeforTraineeBundle:Inscription i WITH i.inscriptionStatus = s
                    WHERE i.session = :session
                    GROUP BY s.id')
                  ->setParameter("session", $session);

                $result = $query->getResult();
                foreach($result as $status) {
                    $stats[] = array(
                      'id' => $status[0]->getId(),
                      'name' => $status[0]->getName(),
                      'status' => $status[0]->getStatus(),
                      'count' => (int)$status[1]
                    );
                }
            }
            $document->set('inscriptionStats', $stats);

            /**
             * HACK
             * ActivityReport : replace null disciplinary by "Autre"
             */
            if($session instanceof Session) {
                $summaries = $document->get('participantsSummaries');
                foreach($summaries as $key => $summary) {
                    if(!$summary['disciplinaryDomain']) {
                        $summaries[$key]['disciplinaryDomain'] = 'Autre';
                    }
                }
                $document->set('participantsSummaries', $summaries);
            }
        }


        return $document;
    }
}
