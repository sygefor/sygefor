<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\SingleSessionTraining;
use Sygefor\Bundle\TrainingBundle\Form\TrainingType;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MeetingType extends AbstractSingleSessionTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('eventType', null, array(
                'label' => "Type d'événement"
            ))
            ->add('eventKind', 'entity', array(
                'label' => "Nature de l'événement",
                'multiple' => true,
                'required' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\EventKind'
            ))
            ->add('national', null, array(
                'required' => false,
                'label' => 'National'
            ))
            ->add('partners', null, array(
                'required' => false,
                'label' => 'Partenaires'
            ))
            ->add('receptionCost', null, array(
                'required' => false,
                'label' => "Coût de l'accueil"
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'meetingtype';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'Sygefor\Bundle\TrainingBundle\Entity\Meeting'
        ));
    }


}
