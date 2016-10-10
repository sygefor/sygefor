<?php

namespace Sygefor\Bundle\InscriptionBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class InscriptionStatusVocabularyType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('notify', CheckboxType::class, array('label' => "Notification lorsqu'une inscription prend ce status", 'required' => false));
        $builder->add('status', ChoiceType::class, array(
            'label'    => 'Statut élémentaire',
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'choices'  => array(
                InscriptionStatus::STATUS_ACCEPTED => 'Accepté',
                InscriptionStatus::STATUS_WAITING  => 'En attente',
                InscriptionStatus::STATUS_PENDING  => 'En attente de traitement',
                InscriptionStatus::STATUS_REJECTED => 'Rejeté',
            ),
        ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return VocabularyType::class;
    }
}
