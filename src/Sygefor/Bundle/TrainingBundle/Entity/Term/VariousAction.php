<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractOrganizationTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface;

/**
 * OrganizationTrainingVocabulary
 *
 * @ORM\Table(name="various_action")
 * @ORM\Entity
 * traduction: actions diverses
 *
 */
class VariousAction extends AbstractOrganizationTerm implements LocalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Type d'action diverse";
    }

    function __toString()
    {
        return $this->getName();
    }


}
