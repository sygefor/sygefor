<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 27/05/14
 * Time: 16:43
 */

namespace Sygefor\Bundle\CoreBundle\Entity\Term;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;

/**
 * Civilité
 *
 * @ORM\Table(name="title")
 * @ORM\Entity
 */
class Title extends AbstractTerm implements NationalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return "Civilités";
    }
}
