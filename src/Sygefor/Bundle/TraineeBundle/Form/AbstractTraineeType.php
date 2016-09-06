<?php

namespace Sygefor\Bundle\TraineeBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublicType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractTraineeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('organization', 'entity', array(
            'required' => true,
            'class' => 'Sygefor\Bundle\CoreBundle\Entity\Organization',
            'label' => 'URFIST',
            'query_builder' =>  function(EntityRepository $er) {
                return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
             }
          ))
          ->add('title', 'entity', array(
              'required' => true,
              'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\Title',
              'label' => 'Civilité'))

          ->add('lastName', null, array(
              'label' => 'Nom'))

          ->add('firstName', null, array(
              'label' => 'Prénom'))

          ->add('email', 'email', array(
              'label' => 'Email'
           ))

          ->add('phoneNumber', null, array(
              'label' => 'Numéro de téléphone'))

          ->add('addressType', 'choice', array(
              'required' => true,
              'choices' => array(
                '0' => 'Adresse personnelle',
                '1' => 'Adresse professionnelle'
              ),
              'label' => 'Type d\'adresse'))

          ->add('institutionName', null, array(
              'label' => 'Nom de l\'établissement'))

          ->add('address', null, array(
              'label' => 'Adresse'))

          ->add('zip', null, array(
              'label' => 'Code postal'))

          ->add('city', null, array(
              'label' => 'Ville'))

          ->add('bp', null, array(
              'label' => 'Boîte postale'))

          ->add('cedex', null, array(
               'label' => 'Cedex'))

          ->add('disciplinaryDomain', 'entity', array(
              'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary',
              'required' => false,
              'label' => "Domaine disciplinaire",
              'query_builder' => function(EntityRepository $er) {
                  return $er->createQueryBuilder('d')->where('d.parent IS NULL');
              }))

          ->add('otherInstitution', null, array(
              'label' => 'Autre institution'))

          ->add('service', null, array(
              'label' => 'Service'))

          ->add('status', null, array(
              'label' => 'Statut'))

          ->add('isPaying', null, array(
              'label' => 'Payant'))

          ->add('publicCategory', 'entity', array(
              'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\PublicType',
              'required' => true,
              'label' => "Catégorie de public",
              'query_builder' => function(EntityRepository $er) {
                  return $er->createQueryBuilder('d')->where('d.parent IS NULL');
              }))

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
              'group_by' => 'parent'));

        // add listeners to handle conditionals fields
        $this->addEventListeners($builder);
    }

    /**
     * Add all listeners to manage conditional fields
     */
    protected function addEventListeners(FormBuilderInterface $builder)
    {
        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $this->addInstitutionField($event->getForm(), $event->getData()->getOrganization());
            $this->addProfessionalSituationField($event->getForm(), $event->getData()->getPublicCategory());
            $this->addDisciplinaryField($event->getForm(), $event->getData()->getDisciplinaryDomain());
        });
        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
        $builder->get('publicCategory')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $this->addProfessionalSituationField($event->getForm()->getParent(), $event->getForm()->getData());
        });
        $builder->get('disciplinaryDomain')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $this->addDisciplinaryField($event->getForm()->getParent(), $event->getForm()->getData());
        });
    }

    /**
     * Add institution field depending organization
     * @param FormInterface $form
     * @param Organization $organization
     */
    protected function addInstitutionField(FormInterface $form, $organization) {
        if($organization) {
            $form->add('institution', 'entity', array(
                'required' => true,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Institution',
                'label' => 'Etablissement',
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
     * Add professional category field
     *
     * @param FormInterface $form
     * @param PublicType $publicCategory
     */
    protected function addProfessionalSituationField(FormInterface $form, $publicCategory) {
        if($publicCategory && $publicCategory->hasChildren()) {
            $form->add('professionalSituation', 'entity', array(
                'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\PublicType',
                'required' => false,
                'label' => "Catégorie professionnelle",
                'query_builder' => function(EntityRepository $er) use($publicCategory) {
                    return $er->createQueryBuilder('d')
                      ->where('d.parent = :parent')
                      ->setParameter('parent', $publicCategory);
                })
            );
        } else {
            $form->remove('professionalSituation');
        }
    }

    /**
     * Add disciplinary field
     * @param FormInterface $form
     * @param Disciplinary $disciplinaryDomain
     */
    protected function addDisciplinaryField(FormInterface $form, $disciplinaryDomain) {
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
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
          'data_class' => 'Sygefor\Bundle\TraineeBundle\Entity\Trainee',
        );
    }

    /**
     * @param $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('Default', 'trainee')
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'trainee';
    }
}
