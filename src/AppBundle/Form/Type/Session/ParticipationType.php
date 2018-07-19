<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 7/27/17
 * Time: 9:43 AM.
 */

namespace AppBundle\Form\Type\Session;

use AppBundle\Entity\Session\Participation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractParticipationType;

class ParticipationType extends AbstractType
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
        return AbstractParticipationType::class;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Participation::class,
        ));
    }
}
