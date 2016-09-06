<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 14:30
 */

namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainingBundle\Entity\ParticipantsSummary;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\SessionParticipantsSummary;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SessionType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class SessionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('training', 'entity_hidden', array(
                'required' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Training',
                'label' => 'Formation'
            ))
            ->add('dateBegin', 'date', array(
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de début',
                'attr' => array(
                    'placeholder' => 'Date de début'
                )
            ))
            ->add('dateEnd', 'date', array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de fin',
                'attr' => array(
                    'placeholder' => 'Date de fin'
                )
            ))
            ->add('hourDuration', null, array(
                'required' => false,
                'label' => "Durée"
            ))
            ->add('schedule', null, array(
                'required' => false,
                'label' => "Horaires"
            ))
            ->add('price', 'money', array(
                'required' => false,
                'label' => "Tarif"
            ))
            ->add('maximumNumberOfRegistrations', null, array(
                'required' => true,
                'label' => "Participants max."
            ))
            ->add('limitRegistrationDate', 'date', array(
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => "Date limite d'inscription"
            ))
            ->add('numberOfRegistrations', null, array(
                'required' => false,
                'label' => "Nombre d'inscrits"
            ))
//            ->add('numberOfParticipants', null, array(
//                'required' => false,
//                'label' => "Nombre de présents"
//            ))
            ->add('registration', 'choice', array(
                'required' => true,
                'choices' => array(
                    Session::REGISTRATION_DEACTIVATED => 'Désactivées',
                    Session::REGISTRATION_CLOSED => 'Fermées',
                    Session::REGISTRATION_PRIVATE => 'Privées',
                    Session::REGISTRATION_PUBLIC => 'Publiques'
                ),
                'label' => 'Inscriptions'

            ))
            ->add('displayOnline', 'choice', array(
                'required' => false,
                'choices' => array(
                    0 => 'Non',
                    1 => 'Oui'
                ),
                'label' => "Afficher en ligne"
            ))
            ->add('networkTrainerCost', 'money', array(
                'required' => false,
                'label' => "Frais de mission des formateurs réseau"
            ))
            ->add('externTrainerCost', 'money', array(
                'required' => false,
                'label' => "Frais de mission des intervenants extérieurs"
            ))
            ->add('externTrainerConsideration', 'money', array(
                'required' => false,
                'label' => "Rémunération intervenants extérieurs"
            ))
            ->add('reprographyCost', 'money', array(
                'required' => false,
                'label' => "Frais de repographie"
            ))
            ->add('otherCost', 'money', array(
                'required' => false,
                'label' => "Autres"
            ))
            ->add('subscriptionRightTaking', 'money', array(
                'required' => false,
                'label' => "Droit d'inscription"
            ))
            ->add('otherTaking', 'money', array(
                'required' => false,
                'label' => "Autres"
            ))
            ->add('observations', null, array(
                'required' => false,
                'label' => "Observations"
            ))
            ->add('comments', 'textarea', array(
                  'required' => false,
                  'label' => 'Commentaires'
            ))
            ->add('promote', 'checkbox', array(
                'label' => 'Promouvoir'
            ))
            ->add('participantsSummaries', 'collection', array(
                'type' => new ParticipantsSummaryType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ));


        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $this->addPlaceField($event->getForm(), $event->getData());
        });

        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $this->addPlaceField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add institution field depending organization
     * @param FormInterface $form
     * @param $data
     */
    function addPlaceField(FormInterface $form, $data) {
        if ($data) {
            if ($data instanceof Session) {
                $organization = $data->getTraining()->getOrganization();
            }
            else if ($data instanceof Organization) {
                $organization = $data;
            }

            if ($organization) {
                $form->add('place', 'entity', array(
                    'required' => false,
                    'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Place',
                    'label' => 'Lieu',
                    'query_builder' => function (EntityRepository $er) use ($organization) {
                        return $er->createQueryBuilder('i')
                            ->where('i.organization = :organization')
                            ->setParameter('organization', $organization)
                            ->orWhere('i.organization is null')
                            ->orderBy('i.name', 'ASC');
                    }
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
            'data_class' => 'Sygefor\Bundle\TrainingBundle\Entity\Session',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sessiontype';
    }
}
