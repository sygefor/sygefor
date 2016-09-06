<?php

namespace Sygefor\Bundle\TraineeBundle\Form;


use Sygefor\Bundle\TaxonomyBundle\Form\Type\VocabularyType;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Symfony\Component\Form\FormBuilderInterface;

class InscriptionStatusVocabularyType extends VocabularyType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('notify', 'checkbox', array('label' => "Notification lorsqu'une inscription prend ce status", "required" => false));
        $builder->add('status', 'choice', array(
            'label' => "Statut élémentaire",
            'expanded' => true,
            'multiple' => false,
            "required" => true,
            "choices" => array(
                InscriptionStatus::STATUS_ACCEPTED => "Accepté",
                InscriptionStatus::STATUS_WAITING => "En attente",
                InscriptionStatus::STATUS_PENDING => "En attente de traitement",
                InscriptionStatus::STATUS_REJECTED => "Rejeté",
            )
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'inscriptionstatusvocabulary';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'vocabulary' ;
    }

}