<?php

namespace Sygefor\Bundle\TrainerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class TrainerType
 * @package Sygefor\Bundle\TrainerBundle\Form
 */
class TrainerType extends AbstractType
{
    /** @var  SecurityContext $securityContext */
    protected $accessRightsRegistry;

    /**
     * @param AccessRightRegistry $accessRightsRegistry
     */
    public function __construct(AccessRightRegistry $accessRightsRegistry)
    {
        $this->accessRightsRegistry = $accessRightsRegistry;
    }

     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // this field will be removed by a listener after a failed rights check
            ->add('organization', 'entity', array(
                'required' => true,
                'class' => 'Sygefor\Bundle\CoreBundle\Entity\Organization',
                'label' => 'URFIST',
                'query_builder' =>  function(EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                }
            ))
            ->add('isUrfist')
            ->add('isPublic')
            ->add('service')
            ->add('status')
            ->add('responsabilities')
            ->add('website', 'text', array(
                'label' => 'Site web'
            ))
            ->add('observations', 'textarea', array(
                'label' => 'Observations'
            ))
            ->add('firstName', 'text', array(
                'label' => 'Prénom'
            ))
            ->add('lastName', 'text', array(
                'label' => 'Nom'
            ))
            ->add('email', 'email', array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', null, array(
                'label' => 'Numéro de téléphone',
            ))
            ->add('addressType', 'choice', array(
                'required' => true,
                'choices' => array(
                    '0' => 'Adresse personnelle',
                    '1' => 'Adresse professionnelle'
                ),
                'label' => 'Type d\'adresse'))
            ->add('institutionName')
            ->add('address', null, array(
                'label' => 'Adresse',
            ))
            ->add('bp', null, array(
                'label' => 'Boîte postale',
            ))
            ->add('zip', null, array(
                'label' => 'Code postal',
            ))
            ->add('city', null, array(
                'label' => 'Ville',
            ))
            ->add('cedex')

            ->add('title', 'entity', array(
                'required' => true,
                'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\Title',
                'label' => 'Civilité',
            ));

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_trainer.rights.trainer.all.create');
        if (!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user) {
                $trainer = $event->getData();
                $trainer->setOrganization($user->getOrganization());
                $event->getForm()->remove('organization');
            });
        }

        // add listeners to handle conditionals fields
        $this->addEventListeners($builder);
    }

    /**
     * @param FormInterface $form
     * @param $data
     */
    protected function addCompetenceField(FormInterface $form, $data)
    {
        if ($data) {
            if ($data instanceof Trainer) {
                $organization = $data->getOrganization();
            }
            else if ($data instanceof Organization) {
                $organization = $data;
            }
            if ($organization) {
                $form->add('competenceFields', 'entity', array(
                    'class' => 'Sygefor\Bundle\TrainerBundle\Entity\Term\CompetenceField',
                    'multiple' => true,
                    'label' => 'Compétences',
                    'query_builder' => function (EntityRepository $er) use ($organization) {
                        return $er->createQueryBuilder('i')
                            ->where('i.organization = :organization')
                            ->setParameter('organization', $organization)
                            ->orderBy('i.name', 'ASC');
                    }
                ));
            }
        }
    }

    /**
     * Add all listeners to manage conditional fields
     */
    protected function addEventListeners(FormBuilderInterface $builder)
    {
        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $this->addInstitutionField($event->getForm(), $event->getData()->getOrganization());
            $this->addCompetenceField($event->getForm(), $event->getData());
        });
        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
                $this->addCompetenceField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add institution field depending organization
     * @param FormInterface $form
     * @param Organization $organization
     */
    function addInstitutionField(FormInterface $form, $organization) {
        if($organization) {
            $form->add('institution', 'entity', array(
                'required' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Institution',
                'label' => 'Etablissement',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orderBy('i.name', 'ASC');
                }));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sygefor\Bundle\TrainerBundle\Entity\Trainer'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'trainer';
    }
}
