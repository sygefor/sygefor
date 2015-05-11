<?php
namespace Sygefor\Bundle\CoreBundle\Search;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SearchServiceInterface
 * @package Sygefor\Bundle\CoreBundle\Search
 */
interface SearchServiceInterface
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function handleRequest(Request $request);
}