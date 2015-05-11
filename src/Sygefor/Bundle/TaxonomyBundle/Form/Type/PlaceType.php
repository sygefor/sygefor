<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 23/02/2015
 * Time: 14:56
 */

namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;


use Symfony\Component\Form\FormBuilderInterface;

class PlaceType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('address','text', array ('label' => 'Adresse'));
        $builder->add('postal','text', array ('label' => 'Code postal'));
        $builder->add('city','text', array ('label' => 'Ville'));
    }

    public function getName()
    {
        return 'place';
    }
}
