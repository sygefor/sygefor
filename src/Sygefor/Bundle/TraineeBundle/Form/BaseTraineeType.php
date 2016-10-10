<?php

namespace Sygefor\Bundle\TraineeBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractAccountType;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\CoreBundle\AccessRight\AccessRightRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TraineeType.
 */
class BaseTraineeType extends AbstractAccountType
{
    /** @var  AccessRightRegistry $accessRightsRegistry */
    protected $accessRightsRegistry;

    /**InscriptionListener
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
        parent::buildForm($builder, $options);

        $builder
            ->add('organization', EntityType::class, array(
                'label'         => 'Centre',
                'class'         => Organization::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                },
            ))
            ->add('institution', EntityType::class, array(
                'label'         => 'Etablissement',
                'class'         => Organization::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                },
            ))
            ->add('service', null, array(
                'required' => false,
                'label'    => 'Service',
            ))
            ->add('isPaying', CheckboxType::class, array(
                'required' => false,
                'label'    => 'Payant'
            ))
            ->add('status', null, array(
                'required' => false,
                'label'    => 'Statut',
            ))
            ->add('publicType', 'entity', array(
                'label'    => 'Type de personnel',
                'class'    => PublicType::class,
                'required' => false,
            ))
            ->add('isActive', CheckboxType::class, array(
                'label' => 'Validé',
                'required' => false
            ));

        // add listeners to handle conditionals fields
        $this->addEventListeners($builder);

        if($options['enable_security_check']) {
            // If the user does not have the rights, remove the organization field and force the value
            $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_trainee.rights.trainee.all.create');
            if (!$hasAccessRightForAll) {
                $securityContext = $this->accessRightsRegistry->getSecurityContext();
                $user            = $securityContext->getToken()->getUser();
                if (is_object($user)) {
                    $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                        $trainee = $event->getData();
                        $trainee->setOrganization($user->getOrganization());
                        $event->getForm()->remove('organization');
                    });
                }
            }
        }
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
        if ($builder->has('organization')) {
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
    protected function addInstitutionField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('institution', 'entity', array(
                'class'         => AbstractInstitution::class,
                'label'         => 'Etablissement',
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

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'            => AbstractTrainee::class,
            'validation_groups'     => array('Default', 'trainee'),
            'enable_security_check' => true,
        ));
    }
}
