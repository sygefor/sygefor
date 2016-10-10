<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Controller\AbstractTrainingController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;

/**
 * @Route("/training")
 */
class TrainingController extends AbstractTrainingController
{
    protected $sessionClass = Session::class;

    /**
     * @param AbstractTraining $dest
     * @param AbstractTraining $source
     */
    protected function mergeArrayCollectionsAndFlush($dest, $source)
    {
        $em = $this->getDoctrine()->getManager();

        // clone common arrayCollections
        if (method_exists($source, 'getTags')) {
            $dest->duplicateArrayCollection('addTag', $source->getTags());
        }

        // clone duplicate materials
        $tmpMaterials = $source->getMaterials();
        if (!empty($tmpMaterials)) {
            foreach ($tmpMaterials as $material) {
                $newMat = clone $material;
                $dest->addMaterial($newMat);
            }
        }

        $em->persist($dest);
        $em->flush();
    }
}
