<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/6/17
 * Time: 12:37 PM.
 */

namespace FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use AppBundle\Entity\Evaluation\EvaluatedTheme;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Class EvaluatedThemeType.
 */
class EvaluatedThemeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('criteria', CollectionType::class, array(
                'label' => 'Critères d\'évaluation',
                'type' => EvaluationNotedCriterionType::class,
            ))
            ->add('comments', null, array(
                'label' => 'Commentaires',
                'required' => false,
            ))
        ;
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EvaluatedTheme::class,
        ));
    }
}
