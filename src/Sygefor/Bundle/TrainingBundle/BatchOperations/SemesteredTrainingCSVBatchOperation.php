<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/04/14
 * Time: 10:43
 */

namespace Sygefor\Bundle\TrainingBundle\BatchOperations;


use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;

use Volcanus\Csv\Writer;
use Sygefor\Bundle\ListBundle\BatchOperations\CSVBatchOperation as BaseCSVBatchOperation;

class SemesteredTrainingCSVBatchOperation extends BaseCSVBatchOperation
{
    protected function getObjectList(array $idList = array())
    {
        return SemesteredTraining::getSemesteredTrainingsByIds($idList, $this->em);
    }
}
