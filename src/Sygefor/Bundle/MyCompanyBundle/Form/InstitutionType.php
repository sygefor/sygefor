<?php

namespace Sygefor\Bundle\MyCompanyBundle\Form;

use Sygefor\Bundle\InstitutionBundle\Form\BaseInstitutionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class InstitutionType.
 */
class InstitutionType extends BaseInstitutionType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('manager', CorrespondentType::class, array(
                'label'              => "Directeur de l'établissement",
                'required'           => false,
                'allow_extra_fields' => $options['allow_extra_fields'],
            ));

        // remove unused fields
        $builder->remove('geographicOrigin');
        $builder->remove('institutionType');
    }
}
