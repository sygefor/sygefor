<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/16/16
 * Time: 5:12 PM.
 */
namespace Sygefor\Bundle\TrainingBundle\Form;

use Sygefor\Bundle\CoreBundle\Form\Type\VocabularyType;
use Sygefor\Bundle\TrainingBundle\Registry\TrainingTypeRegistry;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContext;

class TrainingCategoryType extends VocabularyType
{
    /**
     * @var TrainingTypeRegistry
     */
    protected $trainingTypeRegistry;

    /**
     * @param SecurityContext      $securityContext
     * @param TrainingTypeRegistry $trainingTypeRegistry
     */
    public function __construct(SecurityContext $securityContext, TrainingTypeRegistry $trainingTypeRegistry)
    {
        $this->trainingTypeRegistry = $trainingTypeRegistry;
        parent::__construct($securityContext);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trainingTypes = $this->trainingTypeRegistry->getTypes();
        $choices       = array();
        foreach ($trainingTypes as $key => $trainingType) {
            $training = new $trainingType['class']();
            if (method_exists($training, 'getCategory')) {
                $choices[$key] = $trainingType['label'];
            }
        }
        $builder->add('trainingType', ChoiceType::class, array(
            'label'    => 'Type de formation',
            'choices'  => $choices,
            'required' => true,
        ));

        parent::buildForm($builder, $options);
    }

    public function getParent()
    {
        return VocabularyType::class;
    }
}
