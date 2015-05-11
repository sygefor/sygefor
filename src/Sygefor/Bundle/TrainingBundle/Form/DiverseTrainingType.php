<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Class DiverseTrainingType
 * @package Sygefor\Bundle\TrainingBundle\Form
 */
class DiverseTrainingType extends AbstractTrainingType
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
            ->add('variousAction', null, array(
                'required' => false,
                'label' => "Type d'action diverse"
            ))
            ->add('supervisor', null, array(
                'required' => false,
                'label' => 'Responsable pédagogique'
            ))
            ->add('interventionType', null, array(
                'required' => false,
                'label' => "Type d'intervention"
            ))
            ->add('externInitiative', null, array(
                'required' => false,
                'label' => 'Initiative externe'
            ))
            ->add('organism', null, array(
                'required' => false,
                'label' => 'Organisme'
            ))
            ->add('agreement', null, array(
                'required' => false,
                'label' => 'Etablissement en convention'
            ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'diversetrainingtype';
    }
}
