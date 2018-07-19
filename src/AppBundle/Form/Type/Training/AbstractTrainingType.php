<?php

namespace AppBundle\Form\Type\Training;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\Term\Training\Tag;
use Symfony\Component\Form\FormInterface;
use AppBundle\Entity\Term\Training\Theme;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractTrainingType as CoreTrainingType;
use Sygefor\Bundle\CoreBundle\Security\Authorization\AccessRight\AccessRightRegistry;

/**
 * Class AbstractTrainingType.
 */
abstract class AbstractTrainingType extends AbstractType
{
    /**
     * @var AccessRightRegistry
     */
    protected $accessRightsRegistry;

    /**
     * @param AccessRightRegistry $registry
     */
    public function __construct(AccessRightRegistry $registry)
    {
        $this->accessRightsRegistry = $registry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var AbstractTraining $training */
        $training = isset($options['data']) ? $options['data'] : null;

        $builder
            ->add('theme', EntityType::class, array(
                'label' => 'Thème',
                'class' => Theme::class,
            ))
            ->add('program', null, array(
                'label' => 'Programme',
                'required' => false,
            ))
            ->add('description', null, array(
                'label' => 'Objectifs',
                'required' => true,
            ))
            ->add('teachingMethods', null, array(
                'label' => 'Méthodes pédagogiques',
                'required' => false,
            ));

        // add listeners to handle conditionals fields
        $this->addEventListeners($builder);

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_core.access_right.training.all.create');
        if (!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                $training = $event->getData();
                $training->setOrganization($user->getOrganization());
                $event->getForm()->remove('organization');
            });
        }
    }

    /**
     * Add all listeners to manage conditional fields.
     */
    protected function addEventListeners(FormBuilderInterface $builder)
    {
        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->addUserField($event->getForm(), $event->getData()->getOrganization());
            $this->addTagField($event->getForm(), $event->getData()->getOrganization());
        });

        // POST_SUBMIT for each field
        if ($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->addUserField($event->getForm()->getParent(), $event->getForm()->getData());
                $this->addTagField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add institution field depending organization.
     *
     * @param FormInterface $form
     * @param Organization  $organization
     */
    protected function addUserField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('user', EntityType::class, array(
                'class' => User::class,
                'label' => 'Assitant',
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('u')
                        ->where('u.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('u.organization is null')
                        ->orderBy('u.username', 'ASC');
                },
            ));
        }
    }

    /**
     * Add institution field depending organization.
     *
     * @param FormInterface $form
     * @param Organization  $organization
     */
    protected function addTagField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('tags', EntityTagsType::class, array(
                'class' => Tag::class,
                'label' => 'Tags',
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                },
                'required' => false,
                'prePersist' => function (Tag $tag) use ($form) {
                    // on prepersist new tag, set the training organization
                    $training = $form->getData();
                    $tag->setOrganization($training->getOrganization());
                },
            ));
        }
    }

    public function getParent()
    {
        return CoreTrainingType::class;
    }
}
