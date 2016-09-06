<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 25/02/2015
 * Time: 11:36
 */

namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class ApplicantOrganismType extends VocabularyType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
	}

	public function getName()
	{
		return 'applicantorganism';
	}
}