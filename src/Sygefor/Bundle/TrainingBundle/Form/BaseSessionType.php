<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 14:30.
 */
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\Place;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\SessionType as Type;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class BaseSessionType.
 */
class BaseSessionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AbstractSession $session */
        $session = isset($options['data']) ? $options['data'] : null;

        $builder
            ->add('training', EntityHiddenType::class, array(
                'label'    => 'Formation',
                'class'    => AbstractTraining::class,
                'required' => true,
            ))
            ->add('promote', CheckboxType::class, array(
                'label' => 'Promouvoir',
            ))
            ->add('registration', ChoiceType::class, array(
                'label'   => 'Inscriptions',
                'choices' => array(
                    AbstractSession::REGISTRATION_DEACTIVATED => 'Désactivées',
                    AbstractSession::REGISTRATION_CLOSED      => 'Fermées',
                    AbstractSession::REGISTRATION_PRIVATE     => 'Privées',
                    AbstractSession::REGISTRATION_PUBLIC      => 'Publiques',
                ),
                'required' => true,
            ))
            ->add('displayOnline', ChoiceType::class, array(
                'label'   => 'Afficher en ligne',
                'choices' => array(
                    0 => 'Non',
                    1 => 'Oui',
                ),
                'required' => false,
            ))
            ->add('dateBegin', DateType::class, array(
                'label'    => 'Date de début',
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy',
                'required' => true,
            ))
            ->add('dateEnd', DateType::class, array(
                'label'    => 'Date de fin',
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy',
                'required' => false,
            ))
            ->add('schedule', null, array(
                'label'    => "Horaires",
                'required' => false
            ))
            ->add('hourNumber', NumberType::class, array(
                'label'    => "Nombre d'heures",
                'required' => true,
                'attr'     => array(
                    'min' => 1,
                    'max' => 999,
                ),
            ))
            ->add('dayNumber', NumberType::class, array(
                'label'    => 'Nombre de jours',
                'required' => true,
                'attr'     => array(
                    'min' => 1,
                    'max' => 999,
                ),
            ))
            ->add('status', ChoiceType::class, array(
                'label'   => 'Statut',
                'choices' => array(
                    AbstractSession::STATUS_OPEN     => 'Ouverte',
                    AbstractSession::STATUS_REPORTED => 'Reportée',
                    AbstractSession::STATUS_CANCELED => 'Annulée',
                ),
                'required' => false,
            ))
            ->add('sessionType', EntityType::class, array(
                'label'    => 'Type',
                'class'    => Type::class,
                'required' => false,
            ))
            ->add('numberOfRegistrations', null, array(
                'label'    => "Nombre d'inscrits",
                'required' => false,
            ))
            ->add('maximumNumberOfRegistrations', null, array(
                'label'    => 'Participants max.',
                'required' => true,
            ))
            ->add('limitRegistrationDate', 'date', array(
                'label'    => "Date limite d'inscription",
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy',
                'required' => true,
            ))
            ->add('comments', 'textarea', array(
                'required' => false,
                'label' => 'Commentaires'
            ))
            ->add('participantsSummaries', 'collection', array(
                'type'         => new ParticipantsSummaryType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ));

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->addPlaceField($event->getForm(), $event->getData());
        });

        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->addPlaceField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add institution field depending organization.
     *
     * @param FormInterface $form
     * @param $data
     */
    function addPlaceField(FormInterface $form, $data)
    {
        if ($data) {
            $organization = null;
            if (get_parent_class($data) === AbstractSession::class) {
                $organization = $data->getTraining()->getOrganization();
            }
            else if ($data instanceof Organization) {
                $organization = $data;
            }

            if ($organization) {
                $form->add('place', EntityType::class, array(
                    'required'      => false,
                    'class'         => Place::class,
                    'label'         => 'Lieu',
                    'query_builder' => function (EntityRepository $er) use ($organization) {
                        return $er->createQueryBuilder('i')
                            ->where('i.organization = :organization')
                            ->setParameter('organization', $organization)
                            ->orWhere('i.organization is null')
                            ->orderBy('i.name', 'ASC');
                    },
                ));
            }
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AbstractSession::class,
        ));
    }
}
