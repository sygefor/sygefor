<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 4/7/17
 * Time: 2:40 PM.
 */
namespace Sygefor\Bundle\MyCompanyBundle\Entity\Term;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\AbstractTheme;

/**
 * Theme.
 *
 * @ORM\Table(name="theme")
 * @ORM\Entity
 */
class Theme extends AbstractTheme
{

}
