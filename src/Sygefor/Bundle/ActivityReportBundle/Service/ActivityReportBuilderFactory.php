<?php

namespace Sygefor\Bundle\ActivityReportBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Elastica\Index;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ActivityReportBuilder
 * @package Sygefor\Bundle\ActivityReportBundle\Service
 */
class ActivityReportBuilderFactory
{
    /**
     * @var Index
     */
    protected $index;

    /**
     * @var EntityManager
     */
    protected $em;


    /**
     * @param Index $index
     */
    public function __construct(Index $index, EntityManager $em)
    {
        $this->index = $index;
        $this->em = $em;
    }

    /**
     * Get count
     */
    public function getBuilder(Request $request)
    {
        $builder = new ActivityReportBuilder($this->index, $this->em, $request);
        return $builder;
    }

}
