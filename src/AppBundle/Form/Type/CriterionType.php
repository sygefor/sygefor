<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 12/5/17
 * Time: 4:55 PM.
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Term\Evaluation\Theme;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;

class CriterionType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('theme', EntityType::class, array(
            'label' => 'Thème',
            'class' => Theme::class,
        ));
    }

    public function getParent()
    {
        return VocabularyType::class;
    }
}
