<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/04/14
 * Time: 10:41
 */

namespace Sygefor\Bundle\TrainingBundle\BatchOperations;


use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\ListBundle\BatchOperations\MailingBatchOperation as BaseMailingBatchOperation;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;

/**
 * Class MailingBatchOperation
 * @package Sygefor\Bundle\TrainingBundle\BatchOperations
 */
class SemesteredTrainingMailingBatchOperation extends BaseMailingBatchOperation
{

    /**
     * Getting objects list
     * @internal param array $idList
     * @return mixed
     */
    protected function getObjectList()
    {
        return SemesteredTraining::getSemesteredTrainingsByIds($this->idList, $this->em);
    }
}
