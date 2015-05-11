<?php

namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;


use Symfony\Component\Form\FormBuilderInterface;

class InstitutionType extends VocabularyType {


    public function getName()
    {
        return 'institution';
    }

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
}
