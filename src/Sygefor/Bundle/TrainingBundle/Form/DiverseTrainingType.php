<?php
namespace Sygefor\Bundle\TrainingBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainingBundle\Entity\DiverseTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

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
            /*->add('variousAction', null, array(
                'required' => false,
                'label' => "Type d'action diverse"
            ))*/
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

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $this->addType($event->getForm(), $event->getData());
        });

        // POST_SUBMIT for each field
        if($builder->has('organization')) {
            $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $this->addType($event->getForm()->getParent(), $event->getForm()->getData());
            });
        }
    }

    /**
     * Add institution field depending organization
     * @param FormInterface $form
     * @param $data
     */
    function addType(FormInterface $form, $data) {

        if ($data instanceof DiverseTraining) {
            $organization = $data->getOrganization();
        } else if ($data instanceof Organization) {
            $organization = $data;
        }
        if ($organization) {
            $form->add('variousAction', 'entity', array(
                'required' => false,
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction',
                'label' => 'Type d\'action diverse',
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                }
            ));
        }
    }

    /**
     * @return string
     */
    public function getName() {
        return 'diversetrainingtype';
    }
}
