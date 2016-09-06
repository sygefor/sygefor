<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 16/02/2015
 * Time: 16:40
 */

namespace Sygefor\Bundle\TrainingBundle\Form;


use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class DoctoralTrainingType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class DoctoralTrainingType extends AbstractTrainingType
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
        parent::__construct($registry);
        $this->accessRightsRegistry = $registry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('doctoralYears', 'entity', array(
                'required' => false,
                'multiple' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralYear',
                'label' => 'Année de doctorat'
            ))
            ->add('firstSessionPeriodSemester', 'choice', array(
                'required' => true,
                'choices' => array('1' => '1er semestre civil', '2' => '2nd semestre civil'),
                'label' => '1ère session'
            ))
            ->add('ects', null, array(
                'required' => false,
                'label' => 'Crédits ECTS'
            ))
            ->add('evaluation', null, array(
                'required' => false,
                'label' => "Evaluation"
            ))
            ->add('otherPartner', 'entity', array(
                'required' => false,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralPartner',
                'label' => 'Autre partenaire'
            ))
            ->add('externInitiative', null, array(
                'required' => false,
                'label' => 'Initiative externe'
            ))
            ->add('interventionType', null, array(
                'required' => false,
                'label' => "Type d'intervention"
            ))
            ->add('disciplinaryDomain', 'entity', array(
                'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary',
                'required' => false,
                'label' => "Domaine disciplinaire",
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')->where('d.parent IS NULL');
                }
            ));

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $this->addInstitutionsField($event->getForm(), $event->getData()->getOrganization());
            $this->addApplicantOrganismField($event->getForm(), $event->getData()->getOrganization());
            $this->addDoctoralSchoolsField($event->getForm(), $event->getData()->getOrganization());
            $this->addPedagogicPartnerField($event->getForm(), $event->getData()->getOrganization());
            $this->addDisciplinaryField($event->getForm(), $event->getData()->getDisciplinaryDomain());
        });
    }

    /**
     * Add institution field
     * @param FormInterface $form
     */
    function addInstitutionsField(FormInterface $form, Organization $organization) {
        if($organization) {
            $form->add('institutions', 'entity', array(
                'required' => false,
                'multiple' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Institution',
                'label' => 'Etablissement(s)',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                }));
        }
    }

    /**
     * Add doctoral school field
     * @param FormInterface $form
     */
    function addDoctoralSchoolsField(FormInterface $form, Organization $organization) {
        if($organization) {
            $form->add('doctoralSchools', 'entity', array(
                'required' => false,
                'multiple' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralSchool',
                'label' => 'Ecole(s) doctorale(s)',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                }));
        }
    }

    /**
     * Add pedagogic partner field
     * @param FormInterface $form
     */
    function addPedagogicPartnerField(FormInterface $form, Organization $organization) {
        if($organization) {
            $form->add('pedagogicPartner', 'entity', array(
                'required' => false,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\PedagogicPartner',
                'label' => 'Partenaire pédagogique',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                }));
        }
    }

    /**
     * Add applicant organism field
     * @param FormInterface $form
     */
    function addApplicantOrganismField(FormInterface $form, Organization $organization) {
        if($organization) {
            $form->add('applicantOrganism', 'entity', array(
                'required' => false,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\ApplicantOrganism',
                'label' => 'Organisme demandeur',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                }));
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
        return 'doctoraltrainingtype';
    }
}
