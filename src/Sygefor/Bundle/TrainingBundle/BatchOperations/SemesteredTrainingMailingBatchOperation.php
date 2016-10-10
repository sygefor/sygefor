<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/04/14
 * Time: 10:41.
 */
namespace Sygefor\Bundle\TrainingBundle\BatchOperations;

use Sygefor\Bundle\CoreBundle\BatchOperations\MailingBatchOperation as BaseMailingBatchOperation;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;

/**
 * Class MailingBatchOperation.
 */
class SemesteredTrainingMailingBatchOperation extends BaseMailingBatchOperation
{
    /**
     * Getting objects list.
     *
     * @param array $idList
     *
     * @return \Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining[]
     */
    protected function getObjectList($idList)
    {
        return SemesteredTraining::getSemesteredTrainingsByIds($this->idList, $this->em);
    }
}
