<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 23/02/2015
 * Time: 14:56.
 */

namespace AppBundle\Form\Type\Session;

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
            'label' => 'Contact téléphonique',
            'required' => false,
        ));

        $builder->add('room', 'text', array(
            'label' => 'Salle',
            'required' => false,
        ));

        $builder->add('floor', 'text', array(
            'label' => 'Etage',
            'required' => false,
        ));

        $builder->add('staircase', 'text', array(
            'label' => 'Escalier',
            'required' => false,
        ));

        $builder->add('precision', 'textarea', array(
            'label' => 'Autres précisions',
            'required' => false,
        ));

        $builder->add('embed_map', 'textarea', array(
            'label' => 'Balise HTML intégrée',
            'required' => false,
            'attr' => array(
                'placeholder' => 'Exemple : <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d92956.81247576859!2d5.2941472!3d43.2744626!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12c9bf62615b8fa1%3A0x390b19c849bd126e!2sCNRS!5e0!3m2!1sfr!2sfr!4v1486475591657" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>',
            ),
        ));
    }

    public function getParent()
    {
        return VocabularyType::class;
    }
}
