<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Tag;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TrainingType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class AbstractTrainingType extends AbstractType
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
            ->add('theme', null, array(
                'label' => 'Thématique'
            ))
            ->add('tags', 'entity_tags', array(
                'required' => false,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Tag',
                'label' => 'Tags',
                'query_builder' => function(EntityRepository $er) use ($builder) {
                    return $er->createQueryBuilder('t')
                      ->where('t.organization = :organization')
                      ->setParameter('organization', $builder->getData()->getOrganization())
                      ->orWhere('t.organization is null')
                      ->orderBy('t.name', 'ASC');
                },
                'prePersist' => function(Tag $tag) use ($builder) {
                    // on prepersist new tag, set the training organization
                    $training = $builder->getData();
                    $tag->setOrganization($training->getOrganization());
                }
            ))
            ->add('name', null, array(
                'label' => 'Intitulé'
            ))
            ->add('objectives', null, array(
                'label' => 'Objectifs',
            ))
            ->add('firstSessionPeriodSemester', 'choice', array(
                'required' => true,
                'choices' => array('1' => '1er semestre', '2' => '2nd semestre'),
                'label' => '1ère session'
            ))
            ->add('firstSessionPeriodYear', null, array(
                'required' => true,
                'label' => 'Année',
            ))
            ->add('program', null, array(
                'label' => 'Contenu/Programme',
            ))
            ->add('supervisors', 'entity', array(
                'required' => false,
                'multiple' => true,
                'class' => 'Sygefor\Bundle\TrainerBundle\Entity\Trainer',
                'label' => 'Responsables pédagogiques',
                'query_builder' => function(EntityRepository $er) use ($builder) {
                    return $er->createQueryBuilder('t')
                        ->where('t.isArchived is null')
                        ->orWhere('t.isArchived = false')
                        ->orderBy('t.lastName', 'ASC');
                },
            ))
            ->add('resources', null, array(
                'required' => false,
                'label' => 'Ressources utilisées'
            ))
            ->add('comments', null, array(
                'required' => false,
                'label' => 'Commentaires'
            ));

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_training.rights.training.all.create');
        if(!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user) {
                $training = $event->getData();
                $training->setOrganization($user->getOrganization());
                $event->getForm()->remove('organization');
            });
        }
    }

    /**
     * @return string
     */
    public function getName() {
        return 'trainingtype';
    }
}
