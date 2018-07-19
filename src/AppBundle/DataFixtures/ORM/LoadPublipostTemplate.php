<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Inscription;
use AppBundle\Entity\Session\Session;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublipostTemplate;

class LoadPublipostTemplate extends AbstractTermLoad
{
    public static $class = PublipostTemplate::class;

    public function getTerms()
    {
        $fs = new Filesystem();
        $publipostTemplates = array();
        foreach ($this->organizations as $organization) {
            $fs->copy($this->container->getParameter('kernel.root_dir').'/Resources/fixtures/feuille_presence.odt', sys_get_temp_dir().'/sygefor/feuille_presence_'.$organization->getId().'.odt');
            $publipostTemplates[] = array(
                'name' => 'Feuille d\'émargement',
                'organization' => $organization,
                'file' => new File(sys_get_temp_dir().'/sygefor/feuille_presence_'.$organization->getId().'.odt'),
                'filename' => 'feuille_presence.odt',
                'entity' => Inscription::class,
            );

            $fs->copy($this->container->getParameter('kernel.root_dir').'/Resources/fixtures/formulaire_d_autorisation.odt', sys_get_temp_dir().'/sygefor/formulaire_d_autorisation_'.$organization->getId().'.odt');
            $publipostTemplates[] = array(
                'name' => 'Formulaire d\'autorisation',
                'organization' => $organization,
                'file' => new File(sys_get_temp_dir().'/sygefor/formulaire_d_autorisation_'.$organization->getId().'.odt'),
                'filename' => 'formulaire_d_autorisation.odt',
                'entity' => Inscription::class,
                'machineName' => 'authorization',
            );

            if ($organization->getId() === 1) {
                $fs->copy($this->container->getParameter('kernel.root_dir').'/Resources/fixtures/programme.odt', sys_get_temp_dir().'/sygefor/programme.odt');
                $publipostTemplates[] = array(
                    'name' => 'Programme',
                    'organization' => $organization,
                    'file' => new File(sys_get_temp_dir().'/sygefor/programme.odt'),
                    'filename' => 'Programme.odt',
                    'entity' => Session::class,
                    'machineName' => 'export_pdf',
                );
            }
        }

        return $publipostTemplates;
    }
}
