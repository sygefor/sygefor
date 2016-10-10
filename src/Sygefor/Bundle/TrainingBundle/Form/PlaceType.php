<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 23/02/2015
 * Time: 14:56.
 */
namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\FormBuilderInterface;

class PlaceType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('address', 'text', array('label' => 'Adresse'));
        $builder->add('postal', 'text', array('label' => 'Code postal'));
        $builder->add('city', 'text', array('label' => 'Ville'));

        $builder->add('phone', 'text', array(
            'label'    => 'Contact téléphonique',
            'required' => false,
        ));

        $builder->add('room', 'text', array(
            'label'    => 'Salle',
            'required' => false,
        ));

        $builder->add('floor', 'text', array(
            'label'    => 'Etage',
            'required' => false,
        ));

        $builder->add('staircase', 'text', array(
            'label'    => 'Escalier',
            'required' => false,
        ));

        $builder->add('precision', 'textarea', array(
            'label'    => 'Autres précisions',
            'required' => false,
        ));

        $builder->add('embed_map', 'textarea', array(
            'label'    => 'Balise HTML intégrée',
            'required' => false,
            'attr'     => array(
                'placeholder' => 'Exemple : <iframe src="https://www.google.com/maps/embed?pb=CRFCB+Paris" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>',
            ),
        ));
    }

    public function getParent()
    {
        return VocabularyType::class;
    }
}
