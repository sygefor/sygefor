<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/07/14
 * Time: 14:12
 */
namespace Sygefor\Bundle\ListBundle\Form\Type;

use Sygefor\Bundle\ListBundle\HumanReadablePropertyAccessor\HumanReadablePropertyAccessorFactory;
use Sygefor\Bundle\TaxonomyBundle\Form\Type\VocabularyType;
use Symfony\Component\Form\FormBuilderInterface;

class PublipostTemplateVocabularyType extends VocabularyType
{

    /**
     * @var HumanReadablePropertyAccessorFactory $HRPAFactory
     */
    protected $HRPAFactory;

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'publiposttemplatevocabulary';
    }

    public function setHRPAFactory(HumanReadablePropertyAccessorFactory $HRPAfactory){
        $this->HRPAFactory = $HRPAfactory ;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('entity', 'choice', array(
            'label' => 'Entité associée',
            'choices' => $this->HRPAFactory->getKnownEntities()
        ));
        $builder->add('file', 'file', array('label' => 'Fichier du modèle', 'required' => true, 'block_name' => 'updatable_file'));
    }


} 