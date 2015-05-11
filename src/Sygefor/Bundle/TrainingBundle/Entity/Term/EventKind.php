<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;

/**
 * EventKind
 *
 * @ORM\Table(name="event_kind")
 * @ORM\Entity
 * traduction: nature de l'evenement
 *
 */
class EventKind extends AbstractTerm implements NationalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Nature d'événement";
    }
}
