<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 22/05/14
 * Time: 11:11.
 */
namespace Sygefor\Bundle\CoreBundle\BatchOperation;

/**
 * Interface BatchOperationModalConfigInterface.
 */
interface BatchOperationModalConfigInterface
{
    /**
     * @return array modal window modal config options
     */
    public function getModalConfig($options = array());
}
