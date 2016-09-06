<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 29/01/2016
 * Time: 11:09
 */

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DoctoralPartner
 *
 * @ORM\Table(name="doctoral_partner")
 * @ORM\Entity
 * traduction: Partenaire (non pédagogique)
 *
 */
class DoctoralPartner extends AbstractTerm implements VocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName(){
        return "Partenaire (non pédagogique)";
    }

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return 'doctoralpartner';
    }

    /**
     * @return boolean
     */
    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }

    /**
     * @return mixed
     * This static method is used to set a specific order field
     * when fetch terms (api)
     */
    public static function orderBy()
    {
        return 'name';
    }
}