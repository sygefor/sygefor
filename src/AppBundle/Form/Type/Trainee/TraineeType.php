<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/9/16
 * Time: 4:35 PM.
 */

namespace AppBundle\Form\Type\Trainee;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\Term\PublicType;
use AppBundle\Entity\Trainee\Trainee;
use AppBundle\Entity\Term\Trainee\Disciplinary;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractTraineeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class TraineeType.
 */
class TraineeType extends AbstractTraineeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email', EmailType::class, array(
                'label' => 'Courriel',
            ))
            ->add('phoneNumber', null, array(
                'label' => 'Numéro de téléphone',
                'required' => false,
            ))
            ->add('disciplinaryDomain', EntityType::class, array(
                'label' => "Domaine disciplinaire",
                'class' => Disciplinary::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.parent IS NULL')
                        ->orderBy('d.' . Disciplinary::orderBy(), 'ASC');
                }))
            ->add('publicType', EntityType::class, array(
                'label' => 'Type de personnel',
                'class' => PublicType::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pt')->orderBy('pt.' . PublicType::orderBy(), 'ASC');
                },
                'required' => false,
            ))
            ->add('otherPublicType', TextType::class, array(
                'label' => 'Autre type de personnel',
                'required' => false,
            ))
            ->add('status', null, array(
                'label' => 'Statut',
                'required' => false,
            ))
            ->add('function', null, array(
                'label' => 'Fonction',
                'required' => false,
            ))
            ->add('isPaying', CheckboxType::class, array(
                'label' => 'Payant',
                'required' => false,
            ))
            ->add('isActive', CheckboxType::class, array(
                'label' => 'Validé',
                'required' => false,
            ));

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $this->addDisciplinaryField($event->getForm(), $event->getData()->getDisciplinaryDomain());
        });

        $builder->get('disciplinaryDomain')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $this->addDisciplinaryField($event->getForm()->getParent(), $event->getForm()->getData());
        });
    }

    /**
     * Add disciplinary field
     *
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
                'query_builder' => function (EntityRepository $er) use ($disciplinaryDomain) {
                    return $er->createQueryBuilder('d')
                        ->where('d.parent = :parent')
                        ->setParameter('parent', $disciplinaryDomain)
                        ->orderBy('d.' . Disciplinary::orderBy());
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
            'data_class' => Trainee::class,
            'validation_groups' => array('Default', 'trainee'),
            'enable_security_check' => true,
        ));
    }
}
