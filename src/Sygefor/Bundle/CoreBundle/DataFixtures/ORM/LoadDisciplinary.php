<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53
 */

namespace Sygefor\Bundle\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadDisciplinary
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadDisciplinary extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $name
     * @return Disciplinary
     */
    public function loadOneEntry(ObjectManager $manager, $name)
    {
        $disciplinary = new Disciplinary();
        $disciplinary->setId(++$this->autoId);
        $disciplinary->setName($name);
        $manager->persist($disciplinary) ;
        return $disciplinary;
    }

    /**
     * @param ObjectManager $manager
     * @param $parent
     * @param $array
     */
    public function loadChildEntries(ObjectManager $manager, $parent, $array)
    {
        foreach($array as $name) {
            $disciplinary = new Disciplinary();
            $disciplinary->setId(++$this->autoId);
            $disciplinary->setName($name);
            $disciplinary->setParent($parent);
            $manager->persist($disciplinary) ;
        }
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->loadOneEntry($manager, "Pluridisciplinaire");

        $disciplinary = $this->loadOneEntry($manager, "Sciences humaines, lettres et langues");
        $this->loadChildEntries($manager, $disciplinary, array(
            "Sciences du langage",
            "Langues et littératures classiques",
            "Langue et littérature françaises",
            "Langues et cultures régionales",
            "Langues et littératures étrangères",
            "Histoire et civilisations modernes et contemporaines",
            "Histoire ancienne et médiévale, archéologie",
            "Sciences de l’art, arts du spectacle, musicologie",
            "Psychologie",
            "Philosophie",
            "Epistémologie, histoire des sciences et des techniques",
            "Théologie"
        ));

        $disciplinary = $this->loadOneEntry($manager, "Sciences sociales, droit, économie-gestion");
        $this->loadChildEntries($manager, $disciplinary, array("Droit public et privé", "Sciences politiques", "Sciences économiques et de gestion", "Géographie, aménagement, urbanisme", "Sociologie, démographie, ethnologie, anthropologie",
            "Sciences de l’éducation",
            "Sciences de l’information, de la documentation et de la communication"
        ));

        $disciplinary = $this->loadOneEntry($manager, "Sciences physiques, de la terre et de l’environnement");
        $this->loadChildEntries($manager, $disciplinary, array(
            "Chimie",
            "Biologie et physiologie",
            "Biochimie",
            "Astronomie, astrophysique",
            "Physique",
            "Météorologie, océanographie"
        ));

        $disciplinary = $this->loadOneEntry($manager, "Sciences de la vie et de la santé");
        $this->loadChildEntries($manager, $disciplinary, array(
            "Pharmacie",
            "Médecine",
            "STAPS",
            "Odontologie"
        ));

        $disciplinary = $this->loadOneEntry($manager, "Sciences de l’ingénieur");
        $this->loadChildEntries($manager, $disciplinary, array(
            "Mécanique",
            "Génie électrique"
        ));

        $disciplinary = $this->loadOneEntry($manager, "Mathématiques, informatique");
        $this->loadChildEntries($manager, $disciplinary, array(
            "Mathématiques",
            "Informatique"
        ));

        $this->loadOneEntry($manager, "Autre");

        $manager->flush() ;
    }
}
