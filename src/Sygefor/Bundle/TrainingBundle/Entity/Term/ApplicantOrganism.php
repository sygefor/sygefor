<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 25/02/2015
 * Time: 11:32
 */

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;


use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ApplicantOrganism
 *
 * @ORM\Table(name="applicant_organism")
 * @ORM\Entity
 * traduction: Organisme demandeur
 *
 */
class ApplicantOrganism extends AbstractTerm implements VocabularyInterface
{
	/**
	 * @return mixed
	 */
	public function getVocabularyName(){
		return "Organisme demandeur";
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
		return 'applicantorganism';
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