<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 16:03
 */

namespace Sygefor\Bundle\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPublicType
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadPublicType extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $name
     * @return PublicType
     */
    public function loadOneEntry(ObjectManager $manager, $name, $priority = false, $private = false) {
        $publicType = new PublicType();
        $publicType->setId(++$this->autoId);
        $publicType->setName($name);
        $publicType->setPriority($priority);
        $publicType->setPrivate($private);
        $publicType->setPayingTrainee(false);
        $manager->persist($publicType) ;
        $manager->flush();
        return $publicType;
    }

    /**
     * @param ObjectManager $manager
     * @param $parent
     * @param $array
     */
    public function loadChildEntries(ObjectManager $manager, $parent, $array) {
        foreach($array as $name) {
            $publicType = new PublicType();
            $publicType->setId(++$this->autoId);
            $publicType->setName($name);
            $publicType->setParent($parent);
            $publicType->setPriority(false);
            $publicType->setPayingTrainee(false);
            $manager->persist($publicType) ;
        }
        $manager->flush();
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\CoreBundle\Entity\Term\PublicType');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $publicType = $this->loadOneEntry($manager, "Enseignant du supérieur, chercheur", true);
        $this->loadChildEntries($manager, $publicType, array(
            "Professeur des universités",
            "Maître de conférences",
            "Chercheur",
            "PRAG/PRCE",
            "ATER, assistant",
            "Autre"
        ));

        $this->loadOneEntry($manager, "Doctorant", true);

        $this->loadOneEntry($manager, "Personnel des bibliothèques de l’enseignement supérieur");

        $publicType = $this->loadOneEntry($manager, "Autre personnel IATOS du MESR");
        $this->loadChildEntries($manager, $publicType, array(
            "Personnel de la filière ingénieur",
            "Personnel de la filière technique",
            "Personnel de la filière administrative"
        ));

        $publicType = $this->loadOneEntry($manager, "Personnel de la fonction publique hors MESR");
        $this->loadChildEntries($manager, $publicType, array(
            "Personnel conservateur / bibliothécaire / documentaliste d'autres ministères",
            "Personnel conservateur / bibliothécaire / documentaliste de la fonction publique territoriale",
            "Professeur documentaliste des CDI",
            "Enseignant du 2e degré et professeur des écoles",
            "Autre"
        ));

        $publicType = $this->loadOneEntry($manager, "Etudiant, stagiaire");
        $this->loadChildEntries($manager, $publicType, array(
            "Etudiant de Master",
            "Etudiant de Licence",
            "Stagiaire du CRFCB"
        ));

        $publicType = $this->loadOneEntry($manager, "Personnel hors fonction publique");
        $this->loadChildEntries($manager, $publicType, array(
            "Professionnel de l'information du secteur privé",
            "Autre"
        ));

        $this->loadOneEntry($manager, "Professionnels de l’information", true, true);

        $this->loadOneEntry($manager, "Autre");
    }
}
