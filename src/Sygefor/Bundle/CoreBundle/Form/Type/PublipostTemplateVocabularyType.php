<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/07/14
 * Time: 14:12.
 */
namespace Sygefor\Bundle\CoreBundle\Form\Type;

use Sygefor\Bundle\CoreBundle\HumanReadablePropertyAccessor\HumanReadablePropertyAccessorFactory;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class PublipostTemplateVocabularyType extends VocabularyType
{
    /**
     * @var HumanReadablePropertyAccessorFactory
     */
    protected $HRPAFactory;

    public function setHRPAFactory(HumanReadablePropertyAccessorFactory $HRPAfactory)
    {
        $this->HRPAFactory = $HRPAfactory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('entity', ChoiceType::class, array(
            'label'   => 'Entité associée',
            'choices' => $this->HRPAFactory->getKnownEntities(false),
        ));

        $builder->add('file', FileType::class, array(
            'label'      => 'Fichier du modèle',
            'required'   => true,
            'block_name' => 'updatable_file',
        ));
    }
}
