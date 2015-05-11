<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\TaxonomyBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PublicTypeType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class PublicTypeType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('priority', 'checkbox', array('label' => 'Public visé', 'required' => false));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'publictype';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'vocabulary' ;
    }

}
