<?php
namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TraineeBundle\Entity\Term\EvaluationCriterion;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadEvaluationCriterion
 * @package Sygefor\Bundle\TraineeBundle\DataFixtures\ORM
 */
class LoadEvaluationCriterion extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $criterias = array(
          "Clarté de l'intitulé du stage",
          "Durée du stage",
          "Horaires, gestion du temps",
          "Accueil",
          "Méthodes pédagogiques utilisées",
          "Documentation fournie",
          "Clarté/intérêt des explications",
          "Proportion aspects théoriques/exercices",
          "Apport d'informations et de connaissances nouvelles",
          "Intérêt pratique du stage pour votre activité professionnelle",
        );

        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TraineeBundle\Entity\Term\EvaluationCriterion');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        foreach($criterias as $position => $name) {
            $criteria = new EvaluationCriterion();
            $criteria->setId(++$this->autoId);
            $criteria->setName($name);
            $criteria->setPosition($position);
            $manager->persist($criteria) ;
        }
        $manager->flush();
    }
}
