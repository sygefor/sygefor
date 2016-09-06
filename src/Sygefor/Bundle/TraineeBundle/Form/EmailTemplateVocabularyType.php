<?php
namespace Sygefor\Bundle\TraineeBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\TaxonomyBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailTemplateVocabularyType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('subject', 'text', array('label' => 'Sujet'));
        $builder->add('body', 'textarea', array('label' => 'Corps', 'attr' => array('rows' => 10)));
        $builder->add('inscriptionStatus', 'entity', array(
            'required' => false,
            'label' => "Status d'inscription",
            'class' => 'SygeforTraineeBundle:Term\InscriptionStatus',
            'empty_value' => '',
            'empty_data'  => null,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('i')
                    ->where('i.organization = :orgId')->setParameters(array('orgId'=>$this->securityContext->getToken()->getUser()->getOrganization()->getId()))
                    ->orWhere('i.organization is null')
                    ->orderBy('i.name');
            }
        ));
        $builder->add('attachmentTemplates', 'entity', array(
            'required' => false,
            'label' => "Templates de pièces jointes",
            'class' => 'SygeforListBundle:Term\PublipostTemplate',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('d')
                    ->where('d.organization = :orgId')->setParameters(array('orgId'=>$this->securityContext->getToken()->getUser()->getOrganization()->getId()))
                    ->orWhere('d.organization is null')
                    ->orderBy('d.name');
            },
            'multiple' => 'true',
            'empty_value' => '',
            'empty_data'  => null
        ));
        $builder->add('presenceStatus', 'entity', array(
            'required' => false,
            'label' => 'Status de présence',
            'class' => 'SygeforTraineeBundle:Term\PresenceStatus',
            'empty_value' => '',
            'empty_data'  => null,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('p')
                    ->where('p.organization = :orgId')->setParameters(array('orgId'=>$this->securityContext->getToken()->getUser()->getOrganization()->getId()))
                    ->orWhere('p.organization is null');
            }
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'emailtemplatevocabulary';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'vocabulary' ;
    }

}
