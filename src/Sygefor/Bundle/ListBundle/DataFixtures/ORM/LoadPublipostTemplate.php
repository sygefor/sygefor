<?php
namespace Sygefor\Bundle\ListBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\ListBundle\Entity\Term\PublipostTemplate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class LoadPublipostTemplate extends AbstractDataFixture
{


    /**
     * @param ObjectManager $manager
     * @param $name
     * @param File $file
     * @param $entity
     * @internal param $subject
     * @internal param $body
     * @internal param null $inscriptionStatus
     * @internal param null $presenceStatus
     */
    public function loadOneEntry(ObjectManager $manager, $name, $file, $entity) {
        foreach($manager->getRepository('SygeforCoreBundle:Organization')->findAll() as $organization) {
            $template = new PublipostTemplate();
            $template->setOrganization($organization);
            $template->setName($name);
            $fs = new Filesystem();
            $fs->copy(__DIR__.'/../../../../../../app/Resources/fixtures/feuille_presence.odt', __DIR__.'/../../../../../../app/Resources/fixtures/feuille_presence_'.$organization->getId().'.odt');
            $template->setFile(new File(__DIR__.'/../../../../../../app/Resources/fixtures/feuille_presence_'.$organization->getId().'.odt'),"feuille_presence.odt");
            $template->setEntity($entity);
            $manager->persist($template);
        }
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    protected function doLoad(ObjectManager $manager) {

        $this->loadOneEntry($manager, "Feuille d'émargement", __DIR__.'/../../../../../../app/Resources/fixtures/feuille_presence.odt',"Sygefor\\Bundle\\TraineeBundle\\Entity\\Inscription");

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder() {
        return 6;
    }

} 