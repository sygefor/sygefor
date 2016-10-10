<?php

namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Sygefor\Bundle\TraineeBundle\Form\BaseTraineeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Sygefor\Bundle\InstitutionBundle\Form\BaseInstitutionType;

/**
 * Class ProfileType.
 */
class ProfileType extends BaseTraineeType
{
    /**
     * Add all listeners to manage conditional fields.
     */
    protected function addEventListeners(FormBuilderInterface $builder)
    {
        // Add institution
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($builder) {
            $data = $event->getData();
            $form = $event->getForm();

            // institution
            if (!empty($data['institution'])) {
                // todo
                $this->addInstitutionField($form, $data['organization']);
            }
            else {
                $this->addCustomInstitutionField($form);
                $data['institution'] = array_merge($data['_institution'], array('organization' => $data['organization']));
                unset($data['_institution']);
            }
            $event->setData($data);
        });
    }

    /**
     * Add a custom institution field depending organization.
     *
     * @param FormInterface $form
     */
    protected function addCustomInstitutionField(FormInterface $form)
    {
        $form->add('institution', BaseInstitutionType::class, array(
            'required'           => true,
            'allow_extra_fields' => true,
            'constraints'        => array(
                new Valid(),
            ),
        ));
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'       => false,
            'validation_groups'     => array('Default', 'trainee', 'api.profile'),
            'enable_security_check' => false,
            'allow_extra_fields'    => true,
        ));
    }

    /**
     * Helper : request data extractor.
     *
     * @param Request       $request
     * @param FormInterface $form
     *
     * @return array
     */
    static public function extractRequestData(Request $request, FormInterface $form) {
        // remove extra fields
        $data = $request->request->all();
        $keys = array_keys($form->all());
//        $keys = array_merge($keys, array('institution', 'disciplinary', 'professionalSituation'));
//        $data = array_intersect_key($data, array_flip($keys));
//        $data = array_merge(array("addressType" => 0), $data);
        return $data;
    }
}
