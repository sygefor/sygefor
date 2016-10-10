<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/04/14
 * Time: 10:43.
 */
namespace Sygefor\Bundle\TrainingBundle\BatchOperations;

use Sygefor\Bundle\CoreBundle\BatchOperations\CSVBatchOperation as BaseCSVBatchOperation;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;

class SemesteredTrainingCSVBatchOperation extends BaseCSVBatchOperation
{
    /**
     * @param $idList
     *
     * @return \Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining[]
     */
    protected function getObjectList($idList)
    {
        return SemesteredTraining::getSemesteredTrainingsByIds($idList, $this->em);
    }
}
