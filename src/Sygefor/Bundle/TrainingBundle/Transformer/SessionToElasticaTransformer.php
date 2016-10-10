<?php

namespace Sygefor\Bundle\TrainingBundle\Transformer;

use Doctrine\ORM\EntityManager;
use Elastica\Document;
use Sygefor\Bundle\CoreBundle\Transformer\ModelToElasticaTransformer;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SessionToElasticaTransformer.
 */
class SessionToElasticaTransformer extends ModelToElasticaTransformer
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @param Container $container
     * @param array     $options
     */
    public function __construct(Container $container, $options = array())
    {
        $this->container = $container;
        parent::__construct($options);
    }

    /**
     * @param AbstractSession $session
     * @param array   $fields
     *
     * @return Document
     */
    function transform($session, array $fields)
    {
        $document = parent::transform($session, $fields);

        /*
         * Add inscriptionStats
         */
        if($session instanceof AbstractSession) {
            /** @var EntityManager $em */
            $em    = $this->container->get('doctrine')->getManager();
            $stats = array();
            if($session->getRegistration() > AbstractSession::REGISTRATION_DEACTIVATED) {
                $query = $em
                  ->createQuery('SELECT s, count(i) FROM SygeforInscriptionBundle:Term\\InscriptionStatus s
                    JOIN SygeforInscriptionBundle:AbstractInscription i WITH i.inscriptionStatus = s
                    WHERE i.session = :session
                    GROUP BY s.id')
                  ->setParameter('session', $session);

                $result = $query->getResult();
                foreach($result as $status) {
                    $stats[] = array(
                      'id'     => $status[0]->getId(),
                      'name'   => $status[0]->getName(),
                      'status' => $status[0]->getStatus(),
                      'count'  => (int) $status[1],
                    );
                }
            }
            $document->set('inscriptionStats', $stats);

            /*
             * HACK
             * ActivityReport : replace null by "Autre"
             */
            if($session instanceof AbstractSession) {
                $stats = $document->get('participantsStats');
                foreach($stats as $key => $stat) {
                    if( ! $stat['geographicOrigin']) {
                        $stats[$key]['geographicOrigin'] = 'Autre';
                    }
                }
                $document->set('participantsStats', $stats);
            }
        }

        return $document;
    }
}
