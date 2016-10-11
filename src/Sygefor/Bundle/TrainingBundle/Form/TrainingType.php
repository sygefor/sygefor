<?php

namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityTagsType;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Supervisor;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Tag;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Theme;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\TrainingCategory;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Sygefor\Bundle\CoreBundle\AccessRight\AccessRightRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class TrainingType.
 */
class TrainingType extends AbstractType
{
    /**
     * @var AccessRightRegistry
     */
    private $accessRightsRegistry;

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
        /** @var AbstractTraining $training */
        $training = isset($options['data']) ? $options['data'] : null;

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
            ->add('name', null, array(
                'label' => 'Titre',
            ))
            ->add('theme', EntityType::class, array(
                'label' => 'Thématique',
                'class' => Theme::class,
            ))
            ->add('program', null, array(
                'label'    => 'Programme',
                'required' => false,
            ))
            ->add('description', null, array(
                'label'    => 'Description',
                'required' => true,
            ))
            ->add('teachingMethods', null, array(
                'label'    => 'Méthodes pédagogiques',
                'required' => false,
            ))
            ->add('interventionType', null, array(
                'label'    => 'Type d\'intervention',
                'required' => false,
            ))
            ->add('externalInitiative', CheckboxType::class, array(
                'label'    => 'Initiative externe',
                'required' => false,
            ))
            ->add('category', EntityType::class, array(
                'label'         => 'Catégorie de formation',
                'class'         => TrainingCategory::class,
                'query_builder' => $training ? function (EntityRepository $er) use ($training) {
                    return $er->createQueryBuilder('c')
                        ->where('c.trainingType = :trainingType')
                        ->setParameter('trainingType', $training->getType());
                } : null,
                'required' => false,
            ))
            ->add('comments', null, array(
                'label'    => 'Commentaires',
                'required' => false,
            ))
            ->add('firstSessionPeriodSemester', ChoiceType::class, array(
                'label'    => '1ère session',
                'choices'  => array('1' => '1er semestre', '2' => '2nd semestre'),
                'required' => true,
            ))
            ->add('firstSessionPeriodYear', null, array(
                'label'    => 'Année',
                'required' => true,
            ));

        // add listeners to handle conditionals fields
        $this->addEventListeners($builder);

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_training.rights.training.all.create');
        if (!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user            = $securityContext->getToken()->getUser();
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
            $this->addInstitutionField($event->getForm(), $event->getData()->getOrganization());
            $this->addSupervisorField($event->getForm(), $event->getData()->getOrganization());
            $this->addTagField($event->getForm(), $event->getData()->getOrganization());
        });

        // POST_SUBMIT for each field
        if ($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
                $this->addSupervisorField($event->getForm()->getParent(), $event->getForm()->getData());
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
    protected function addInstitutionField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('institution', EntityType::class, array(
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
     * Add supervisor field depending organization.
     *
     * @param FormInterface $form
     * @param Organization  $organization
     */
    protected function addSupervisorField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('supervisor', EntityType::class, array(
                'class'         => Supervisor::class,
                'label'         => 'Responsable pédagogique',
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('s')
                        ->where('s.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('s.organization is null')
                        ->orderBy('s.name', 'ASC');
                },
                'required' => false,
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
                'class'         => Tag::class,
                'label'         => 'Tags',
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                },
                'required' => false,
                'prePersist' => function(Tag $tag) use ($form) {
                    // on prepersist new tag, set the training organization
                    $training = $form->getData();
                    $tag->setOrganization($training->getOrganization());
                }
            ));
        }
    }
}
