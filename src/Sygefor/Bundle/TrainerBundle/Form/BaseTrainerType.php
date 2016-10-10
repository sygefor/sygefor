<?php

namespace Sygefor\Bundle\TrainerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term\Title;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer;
use Sygefor\Bundle\CoreBundle\AccessRight\AccessRightRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class TrainerType.
 */
class BaseTrainerType extends AbstractType
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
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // this field will be removed by a listener after a failed rights check
            ->add('organization', EntityType::class, array(
                'required'      => true,
                'class'         => Organization::class,
                'label'         => 'Centre',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                },
            ))
            ->add('title', EntityType::class, array(
                'label'    => 'Civilité',
                'class'    => Title::class,
                'required' => true,
            ))
            ->add('firstName', null, array(
                'label' => 'Prénom',
            ))
            ->add('lastName', null, array(
                'label' => 'Nom',
            ))
            ->add('email', EmailType::class, array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', null, array(
                'label' => 'Numéro de téléphone',
            ))
            ->add('website', UrlType::class, array(
                'label' => 'Site internet',
            ))
            ->add('addressType', ChoiceType::class, array(
                'label' => 'Type d\'adresse',
                'choices' => array(
                    '0' => 'Adresse personnelle',
                    '1' => 'Adresse professionnelle'
                ),
                'required' => false
            ))
            ->add('address', null, array(
                'label' => 'Adresse',
            ))
            ->add('zip', null, array(
                'label' => 'Code postal',
            ))
            ->add('city', null, array(
                'label' => 'Ville',
            ))
            ->add('trainerType', EntityType::class, array(
                'label'    => "Type d'intervenant",
                'class'    => \Sygefor\Bundle\TrainerBundle\Entity\Term\TrainerType::class,
                'required' => false,
            ))
            ->add('service', null, array(
                'label' => 'Service',
            ))
            ->add('status', null, array(
                'label' => 'Statut',
            ))
            ->add('isArchived', null, array(
                'label' => 'Archivé',
            ))
            ->add('isAllowSendMail', null, array(
                'label' => 'Autoriser les courriels',
            ))
            ->add('isOrganization', null, array(
                'label' => 'Formateur interne',
            ))
            ->add('isPublic', null, array(
                'label' => 'Publié sur le web',
            ))
            ->add('observations', TextareaType::class, array(
                'label' => 'Observations',
            ));

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_trainer.rights.trainer.all.create');
        if ( ! $hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user            = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                $trainer = $event->getData();
                $trainer->setOrganization($user->getOrganization());
                $event->getForm()->remove('organization');
            });
        }

        // add listeners to handle conditionals fields
        $this->addEventListeners($builder);
    }

    /**
     * Add all listeners to manage conditional fields.
     */
    protected function addEventListeners(FormBuilderInterface $builder)
    {
        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->addInstitutionField($event->getForm(), $event->getData()->getOrganization());
        });
        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add institution field depending organization.
     *
     * @param FormInterface $form
     * @param Organization  $organization
     */
    function addInstitutionField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('institution', EntityType::class, array(
                'label'         => 'Etablissement',
                'class'         => AbstractInstitution::class,
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                },
                'required' => true,
            ));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AbstractTrainer::class,
        ));
    }
}
