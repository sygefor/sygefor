<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Sygefor\Bundle\CoreBundle\Form\Type\ChangeOrganizationType as CoreChangeOrganizationType;

/**
 * Class ChangeOrganizationType.
 */
class ChangeOrganizationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public function getParent()
    {
        return CoreChangeOrganizationType::class;
    }
}
