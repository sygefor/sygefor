<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Class InternshipType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class InternshipType extends AbstractTrainingType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder
          ->add('publicTypes', 'entity', array(
              'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\PublicType',
              'multiple' => true,
              'required' => false,
              'label' => 'Publics prioritaires',
              'query_builder' => function(EntityRepository $er) {
                  return $er->createQueryBuilder('pt')
                    ->where('pt.parent IS NULL')
                    ->where('pt.priority = 1');
              }
            ))
            ->add('prerequisite', null, array(
                'required' => false,
                'label' => 'Pré-requis'
            ))
            ->add('interventionType', null, array(
                'required' => false,
                'label' => "Type d'intervention"
            ))
            ->add('externInitiative', 'checkbox', array(
                'required' => false,
                'label' => 'Initiative externe'
            ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'internshiptype';
    }
}
