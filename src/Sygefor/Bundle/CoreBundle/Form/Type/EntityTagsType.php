<?php

namespace Sygefor\Bundle\CoreBundle\Form\Type;

use Sygefor\Bundle\CoreBundle\Form\DataTransformer\CollectionToTagsTransformer;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class EntityTagsType.
 */
class EntityTagsType extends EntityType
{
    /** @var EntityChoiceList $_choiceList */
    private $_choiceList;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // A choice list presenting a list of Doctrine entities as choices
        $choiceList = new EntityChoiceList(
            $options['em'],
            $options['class'],
            $options['choice_label'],
            $options['query_builder'] ? $this->getLoader($options['em'], $options['query_builder'], $options['class']) : $options['query_builder'],
            $options['choices'],
            $options['preferred_choices']
        );
        $this->_choiceList = $choiceList;

        // Transforms tags to a concatened string
        $builder->addViewTransformer(new CollectionToTagsTransformer($options['em'], $choiceList, $options['class'], $options['choice_label'], $options['prePersist']), true);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $list = $this->_choiceList;
        $tags = array();
        foreach ($list->getChoices() as $tag) {
            $tags[] = (string)$tag;
        }
        $view->vars['attr']['data-select2-tags'] = json_encode($tags);
        $view->vars['choices'] = $this->_choiceList->getRemainingViews();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'preferred_choices' => array(),
            'multiple' => true,
            'property' => 'name',
            'prePersist' => null,
        ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }
}
