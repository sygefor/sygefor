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
 * DoctoralSchool
 *
 * @ORM\Table(name="doctoral_school")
 * @ORM\Entity
 * traduction: Ecole doctorale
 *
 */
class DoctoralSchool extends AbstractTerm implements VocabularyInterface
{
	/**
	 * @return mixed
	 */
	public function getVocabularyName(){
		return "Ecole doctorale";
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
	 */
	public static function getFormType()
	{
		return 'doctoralschool';
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