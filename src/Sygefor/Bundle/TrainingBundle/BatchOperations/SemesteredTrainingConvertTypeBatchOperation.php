<?php

namespace Sygefor\Bundle\TrainingBundle\BatchOperations;

use Sygefor\Bundle\CoreBundle\BatchOperations\ConvertTypeBatchOperation as BaseConvertTypeBatchOperation;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;

class SemesteredTrainingConvertTypeBatchOperation extends BaseConvertTypeBatchOperation
{
    protected function getObjectList(array $idList = array())
    {
        return SemesteredTraining::getTrainingsByIds($idList, $this->em);
    }
}
