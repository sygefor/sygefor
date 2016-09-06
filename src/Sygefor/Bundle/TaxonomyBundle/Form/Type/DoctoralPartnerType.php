<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 29/01/2016
 * Time: 11:56
 */

namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class DoctoralPartnerType extends VocabularyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'doctoralpartner';
    }
}