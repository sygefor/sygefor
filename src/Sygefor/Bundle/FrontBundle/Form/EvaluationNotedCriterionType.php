<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/26/16
 * Time: 5:45 PM
 */

namespace Sygefor\Bundle\FrontBundle\Form;


use Sygefor\Bundle\MyCompanybundle\Entity\Inscription;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Sygefor\Bundle\MyCompanybundle\Entity\EvaluationNotedCriterion;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationNotedCriterionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('inscription', EntityHiddenType::class, array(
                'label' => 'Inscription',
                'class' => Inscription::class
            ))
            ->add('criterion', EntityHiddenType::class, array(
                'label' => 'Critère',
                'class' => EvaluationCriterion::class
            ))
            ->add('note', ChoiceType::class, array(
                'label' => 'Note',
                'choices' => array(
                    5 => "Très satisfaisant",
                    4 => "Satisfaisant",
                    3 => "Passable",
                    2 => "Insuffisant",
                    1 => "Très insuffisant"
                )
            ));

        $builder->addEventListener(FormEvents::POST_SET_DATA, array($this, 'replaceNoteLabel'));
    }

    /**
     * @param FormEvent $event
     */
    public function replaceNoteLabel(FormEvent $event)
    {
        $form             = $event->getForm();
        $criterion        = $form->get('criterion')->getData();
        $note             = $form->get('note');
        $config           = $note->getConfig();
        $options          = $config->getOptions();

        $options['label'] = $criterion->getName();
        $form->add($note->getName(), $config->getType() ? $config->getType()->getName() : null, $options);
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => EvaluationNotedCriterion::class,
            'validation_groups' => array('Correspondent'),
        ));
    }
}