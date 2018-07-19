<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 6/7/16
 * Time: 10:26 AM.
 */

namespace AppBundle\Form\Type\Training;

use AppBundle\Entity\Term\PublicType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class LongTrainingType extends AbstractTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publicType', EntityType::class, array(
                'label' => 'Type de public',
                'class' => PublicType::class,
                'required' => false,
            ))
            ->add('prerequisites', null, array(
                'label'    => 'Pré-requis',
                'required' => false,
            ))
            ->add('modules', CollectionType::class, array(
                'label'        => 'Modules',
                'type'         => ModuleType::class,
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
            ));

        parent::buildForm($builder, $options);
    }
}
