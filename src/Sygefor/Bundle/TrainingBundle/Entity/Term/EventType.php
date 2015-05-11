<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 14:28
 */

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;

/**
 * Theme
 *
 * @ORM\Table(name="event_type")
 * @ORM\Entity
 */
class EventType extends AbstractTerm implements NationalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Type d'événement";
    }
}