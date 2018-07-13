<?php

namespace AppBundle\Form\Type;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class MenuItemType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('link', UrlType::class, array(
                'label' => 'Lien externe',
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
