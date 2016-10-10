<?php
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;


use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TraineeBundle\Entity\Term\Disciplinary;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class LoadDisciplinary
 * @package Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM
 */
class LoadDisciplinary extends AbstractTermLoad
{
    static $class = Disciplinary::class;

    public function getTerms()
    {
        return array();
    }
}
