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
            'empty_data'  => null
        ));
        $builder->add('attachmentTemplates', 'entity', array(
            'required' => false,
            'label' => "Templates de pièces jointes",
            'class' => 'SygeforListBundle:Term\PublipostTemplate',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('d')
                    ->where('d.organization = :orgId')->setParameters(array('orgId'=>$this->securityContext->getToken()->getUser()->getOrganization()->getId()));
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
            'empty_data'  => null
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
