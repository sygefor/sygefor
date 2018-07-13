<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 14:30.
 */

namespace AppBundle\Form\Type\Session;

use AppBundle\Entity\Organization;
use AppBundle\Entity\Session\Session;
use AppBundle\Entity\Term\Session\Place;
use AppBundle\Entity\Training\Module;
use AppBundle\Form\Type\Training\ModuleType;
use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractSessionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SessionType.
 */
class SessionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var Session $session */
        $session = isset($options['data']) ? $options['data'] : null;

        $builder
            ->add('place', EntityType::class, array(
                'label' => 'Lieu',
                'class' => Place::class,
                'query_builder' => $session ? function (EntityRepository $er) use ($session) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $session->getTraining()->getOrganization())
                        ->orWhere('i.organization is null')
                        ->orderBy('i.'.Place::orderBy(), 'ASC');
                } : null,
                'required' => false,
            ))
            ->add('promote', CheckboxType::class, array(
                'label' => 'Promouvoir',
            ))
            ->add('schedule', null, array(
                'label' => 'Horaires',
                'required' => false,
            ))
            ->add('hourNumber', null, array(
                'label' => "Nombre d'heures",
                'required' => false,
                'attr' => array(
                    'min' => 0,
                ),
                'disabled' => $session && $session->getDayNumber() > 0,
            ))
            ->add('dayNumber', null, array(
                'label' => 'Nombre de jours',
                'required' => false,
                'attr' => array(
                    'min' => 0,
                    'step' => 0.5,
                ),
            ))
            ->add('participantsSummaries', 'collection', array(
                'type' => new ParticipantsSummaryType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('name', TextType::class, array(
                'label' => 'Intitulé',
                'required' => false,
            ))
            ->add('datePrecisions', TextType::class, array(
                'label' => 'Dates précises',
                'required' => false,
            ))
            ->add('price', null, array(
                'label' => 'Prix',
                'required' => false,
            ))
            ->add('reprographyCost', null, array(
                'required' => false,
                'label' => 'Frais de repographie',
            ))
            ->add('otherCost', null, array(
                'required' => false,
                'label' => 'Autres',
            ))
            ->add('subscriptionRightTaking', null, array(
                'required' => false,
                'label' => "Droit d'inscription",
            ))
            ->add('otherTaking', null, array(
                'required' => false,
                'label' => 'Autres',
            ))
            ->add('room', 'text', array(
                'required' => false,
                'label' => 'Salle',
            ));

        if ($session && method_exists($session->getTraining(), 'getModules')) {
            $builder
                ->add('sessionType', EntityType::class, array(
                    'label'         => 'Type',
                    'class'         => \AppBundle\Entity\Term\Session\SessionType::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->orderBy('t.' . \AppBundle\Entity\Term\Session\SessionType::orderBy(), 'ASC');
                    },
                    'required' => false,
                ))
                ->add('module', EntityType::class, array(
                    'label'         => 'Module',
                    'class'         => Module::class,
                    'query_builder' => function (EntityRepository $er) use ($session) {
                        return $er->createQueryBuilder('m')
                            ->where('m.training = :training')
                            ->setParameter('training', $session->getTraining())
                            ->orderBy('m.name');
                    },
                    'required' => false,
                ))
                ->add('newModule', ModuleType::class, array(
                    'label'    => 'Nouveau module',
                    'required' => false,
                ));
        }

        $builder->get('training')->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm()->getParent();
            $training = $event->getForm()->getData();
            if ($training->getType() === 'et') {
                $form->remove('registration');
            }
        });
    }

    public function getParent()
    {
        return AbstractSessionType::class;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Session::class,
        ));
    }
}
