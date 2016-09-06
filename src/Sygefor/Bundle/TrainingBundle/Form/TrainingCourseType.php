<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class TrainingCourseType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class TrainingCourseType extends AbstractTrainingType
{
    /**
     * @var AccessRightRegistry
     */
    private $accessRightsRegistry;

    /**
     * @var
     */
    private $securityContext;

    /**
     * @param AccessRightRegistry $registry
     */
    public function __construct(AccessRightRegistry $registry, SecurityContext $securityContext)
    {
        parent::__construct($registry);
        $this->accessRightsRegistry = $registry;
        $this->securityContext = $securityContext;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder
            ->add('context', null, array(
                'required' => false,
                'label' => "Contexte"
            ))
            ->add('teachingCursus', 'entity', array(
                  'required' => false,
                  'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus',
                  'label' => 'Cursus d\'enseignement',
                  'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('tc')
                          ->leftJoin('tc.children', 'children')
                          ->addGroupBy('tc.id')
                          ->having('COUNT(children) = 0');
                    },
                  'group_by' => 'parent'
            ))
            ->add('otherCursus', null, array(
                'required' => false,
                'label' => "Autre cursus"
            ))
            ->add('disciplinaryDomain', 'entity', array(
                'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary',
                'required' => false,
                'label' => "Domaine disciplinaire",
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')->where('d.parent IS NULL');
            }))
            ->add('interventionType', null, array(
                'required' => false,
                'label' => "Type d'intervention"
            ))
            ->add('evaluation', null, array(
                'required' => false,
                'label' => "Evaluation"
            ))
            ->add('externInitiative', null, array(
                'required' => false,
                'label' => 'Initiative externe'
            ));

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $this->addInstitutionField($event->getForm(), $event->getData()->getOrganization());
            $this->addDisciplinaryField($event->getForm(), $event->getData()->getDisciplinaryDomain());
        });
        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                  $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
        $builder->get('disciplinaryDomain')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $this->addDisciplinaryField($event->getForm()->getParent(), $event->getForm()->getData());
        });
    }

    /**
     * Add institution field
     * @param FormInterface $form
     * @param Organization $organization
     */
    function addInstitutionField(FormInterface $form, $organization) {
        // add institution
        if($organization) {
            $form->add('institution', 'entity', array(
                'required' => false,
                'label' => 'Etablissement',
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Institution',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                      ->where('i.organization = :organization')
                      ->setParameter('organization', $organization)
                      ->orWhere('i.organization is null')
                      ->orderBy('i.name', 'ASC');
                }
              ));
        }
    }

    /**
     * Add disciplinary field
     * @param FormInterface $form
     * @param Disciplinary $disciplinaryDomain
     */
    function addDisciplinaryField(FormInterface $form, $disciplinaryDomain) {
        if($disciplinaryDomain && $disciplinaryDomain->hasChildren()) {
            $form->add('disciplinary', 'entity', array(
                'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary',
                'required' => false,
                'label' => "Discipline",
                'query_builder' => function(EntityRepository $er) use($disciplinaryDomain) {
                    return $er->createQueryBuilder('d')
                        ->where('d.parent = :parent')
                        ->setParameter('parent', $disciplinaryDomain);
                })
            );
        } else {
            $form->remove('disciplinary');
        }
    }
    /**
     * @return string
     */
    public function getName() {
        return 'trainingcoursetype';
    }
}
