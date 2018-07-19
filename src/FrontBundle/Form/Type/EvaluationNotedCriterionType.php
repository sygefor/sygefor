<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/26/16
 * Time: 5:45 PM.
 */

namespace FrontBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use AppBundle\Entity\Evaluation\NotedCriterion;
use AppBundle\Entity\Term\Evaluation\Criterion;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class EvaluationNotedCriterionType.
 */
class EvaluationNotedCriterionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('criterion', EntityHiddenType::class, array(
                'label' => 'Critère',
                'class' => Criterion::class,
            ))
            ->add('note', ChoiceType::class, array(
                'label' => 'Note',
                'choices' => array(
                    3 => 'Très Satisfaisant',
                    2 => 'Satisfaisant',
                    1 => 'Passable',
                    0 => 'Insuffisant',
                ),
            ));

        $builder->addEventListener(FormEvents::POST_SET_DATA, array($this, 'replaceNoteLabel'));
    }

    /**
     * @param FormEvent $event
     */
    public function replaceNoteLabel(FormEvent $event)
    {
        $form = $event->getForm();
        $criterion = $form->get('criterion')->getData();
        $note = $form->get('note');
        $config = $note->getConfig();
        $options = $config->getOptions();

        $options['label'] = $criterion->getName();
        $form->add($note->getName(), $config->getType() ? $config->getType()->getName() : null, $options);
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NotedCriterion::class,
        ));
    }
}
