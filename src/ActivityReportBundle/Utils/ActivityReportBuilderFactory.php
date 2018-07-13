<?php

namespace ActivityReportBundle\Utils;

use Doctrine\ORM\EntityManager;
use Elastica\Index;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ActivityReportBuilder.
 */
class ActivityReportBuilderFactory
{
    /** @var Index $index */
    protected $index;

    /** @var EntityManager $em */
    protected $em;

    /**
     * @param Index         $index
     * @param EntityManager $em
     */
    public function __construct(Index $index, EntityManager $em)
    {
        $this->index = $index;
        $this->em = $em;
    }

    /**
     * @param Request $request
     *
     * @return ActivityReportBuilder
     */
    public function getBuilder(Request $request)
    {
        return new ActivityReportBuilder($this->index, $this->em, $request);
    }
}
