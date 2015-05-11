<?php

namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Sygefor\Bundle\TaxonomyBundle\Form\DataTransformer\CollectionToTagsTransformer;

/**
 * Class EntityTagsType
 * @package Sygefor\Bundle\TaxonomyBundle\Form\Type
 */
class EntityTagsType extends EntityType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //parent::buildForm($builder,$options);
        /** @var EntityChoiceList $list */
        $em = $options['em'];
        $list = $options['choice_list'];
        $property = $options['property'];
        $class = $options['class'];
        $prePersist = $options['prePersist'];
        $builder->addViewTransformer(new CollectionToTagsTransformer($em, $list, $class, $property, $prePersist), true);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $list = $options['choice_list'];
        $tags = array();
        foreach($list->getChoices() as $tag) {
            $tags[] = (string)$tag;
        }
        $view->vars['attr']['data-select2-tags'] = json_encode($tags);
        $view->vars['choices'] = $options['choice_list']->getRemainingViews();
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
            'prePersist' => null
        ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'entity_tags';
    }
}
