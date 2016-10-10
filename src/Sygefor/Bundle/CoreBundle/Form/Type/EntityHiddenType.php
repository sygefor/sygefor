<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 25/06/14
 * Time: 16:48.
 */
namespace Sygefor\Bundle\CoreBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityHiddenType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['class'] === null) {
            throw new MissingOptionsException('Missing required class option ');
        }
        else {
            $transformer = new EntityToIdTransformer($this->om);
            $transformer->setEntityClass($options['class']);
            $builder->addViewTransformer($transformer);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => null,
                'error_bubbling' => false,
            )
        );
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
