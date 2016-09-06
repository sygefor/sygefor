<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 25/02/2015
 * Time: 11:31
 */

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;


use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DoctoralYear
 *
 * @ORM\Table(name="doctoral_year")
 * @ORM\Entity
 * traduction: Année de doctoral
 *
 */
class DoctoralYear extends AbstractTerm implements VocabularyInterface
{
	/**
	 * @return mixed
	 */
	public function getVocabularyName()
	{
		return "Année de doctorat";
	}

    /**
     * @return boolean
     */
    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }
}