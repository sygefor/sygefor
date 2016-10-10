<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/9/16
 * Time: 4:35 PM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Form;


use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\MyCompanyBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TraineeBundle\Form\BaseTraineeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeType extends BaseTraineeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('disciplinaryDomain', EntityType::class, array(
                'class' => Disciplinary::class,
                'required' => false,
                'label' => "Domaine disciplinaire",
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')->where('d.parent IS NULL');
                }))
        ;
    }

    /**
     * Add all listeners to manage conditional fields.
     */
    protected function addEventListeners(FormBuilderInterface $builder)
    {
        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->addInstitutionField($event->getForm(), $event->getData()->getOrganization());
            $this->addDisciplinaryField($event->getForm(), $event->getData()->getDisciplinaryDomain());
        });

        // POST_SUBMIT for each field
        if ($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }

        if ($builder->has('disciplinaryDomain')) {
            $builder->get('disciplinaryDomain')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->addDisciplinaryField($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add disciplinary field
     * @param FormInterface $form
     * @param Disciplinary $disciplinaryDomain
     */
    protected function addDisciplinaryField(FormInterface $form, $disciplinaryDomain)
    {
        if ($disciplinaryDomain && $disciplinaryDomain->hasChildren()) {
            $form->add('disciplinary', EntityType::class, array(
                    'class' => Disciplinary::class,
                    'required' => false,
                    'label' => "Discipline",
                    'query_builder' => function(EntityRepository $er) use($disciplinaryDomain) {
                        return $er->createQueryBuilder('d')
                            ->where('d.parent = :parent')
                            ->setParameter('parent', $disciplinaryDomain);
                    })
            );
        }
        else {
            $form->remove('disciplinary');
        }
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'            => Trainee::class,
            'validation_groups'     => array('Default', 'trainee'),
            'enable_security_check' => true,
        ));
    }
}