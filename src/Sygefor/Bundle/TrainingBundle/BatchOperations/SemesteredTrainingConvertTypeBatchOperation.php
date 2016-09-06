<?php

namespace Sygefor\Bundle\TrainingBundle\BatchOperations;


use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;

use Volcanus\Csv\Writer;
use Sygefor\Bundle\ListBundle\BatchOperations\ConvertTypeBatchOperation as BaseConvertTypeBatchOperation;

class SemesteredTrainingConvertTypeBatchOperation extends BaseConvertTypeBatchOperation
{
    protected function getObjectList(array $idList = array())
    {
        return SemesteredTraining::getTrainingsByIds($idList, $this->em);
    }
}
