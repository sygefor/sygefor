<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/07/14
 * Time: 14:35.
 */

namespace AppBundle\Form\Type\Material;

use AppBundle\Entity\Material\LinkMaterial;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractMaterialType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class LinkMaterialType.
 */
class LinkMaterialType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', 'url', array(
            'label' => 'Lien',
        ));
    }

    public function getParent()
    {
        return AbstractMaterialType::class;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => LinkMaterial::class,
        ));
    }
}
