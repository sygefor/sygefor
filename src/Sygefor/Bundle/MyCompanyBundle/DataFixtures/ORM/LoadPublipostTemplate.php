<?php

namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublipostTemplate;
use Sygefor\Bundle\MyCompanyBundle\Entity\Inscription;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class LoadPublipostTemplate extends AbstractTermLoad
{
    static $class = PublipostTemplate::class;

    function getTerms()
    {
        $fs                 = new Filesystem();
        $publipostTemplates = array();
        foreach ($this->organizations as $organization) {
            $fs->copy(__DIR__ . '/../../../../../../app/Resources/fixtures/feuille_presence.odt', __DIR__ . '/../../../../../../app/Resources/fixtures/feuille_presence_' . $organization->getId() . '.odt');
            $publipostTemplates[] = array(
                'name'         => 'Feuille d\'émargement',
                'organization' => $organization,
                'file'         => new File(__DIR__ . '/../../../../../../app/Resources/fixtures/feuille_presence_' . $organization->getId() . '.odt'),
                'filename'     => 'feuille_presence.odt',
                'entity'       => Inscription::class,
            );
        }

        return $publipostTemplates;
    }
}
