<?php

namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PublicTypeType.
 */
class PublicTypeType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('priority', 'checkbox', array('label' => 'Public visé', 'required' => false));
        $builder->add('recursiveIsPaying', 'checkbox', array('label' => 'Payant', 'required' => false));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return VocabularyType::class;
    }
}
