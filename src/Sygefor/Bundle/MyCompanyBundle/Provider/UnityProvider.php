<?php

namespace Sygefor\Bundle\MyCompanyBundle\Provider;


use Sygefor\Bundle\MyCompanyBundle\Entity\Institution;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/27/16
 * Time: 2:54 PM
 */
class UnityProvider
{
    /**
     * @param AbstractTrainee $trainee
     * @return Institution|null
     */
    public function find(AbstractTrainee $trainee)
    {
        return null;
    }
}