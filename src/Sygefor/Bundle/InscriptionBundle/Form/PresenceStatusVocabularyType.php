<?php

namespace Sygefor\Bundle\InscriptionBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\PresenceStatus;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PresenceStatusVocabularyType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('status', ChoiceType::class, array(
            'label'    => 'Statut élémentaire',
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'choices'  => array(
                PresenceStatus::STATUS_PRESENT => 'Présent',
                PresenceStatus::STATUS_ABSENT  => 'Absent',
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
