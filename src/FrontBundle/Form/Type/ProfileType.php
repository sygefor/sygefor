<?php

namespace FrontBundle\Form\Type;

use AppBundle\Entity\Organization;
use AppBundle\Entity\Trainee\Trainee;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use AppBundle\Form\Type\Trainee\TraineeType;
use AppBundle\Entity\Institution\Institution;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class ProfileType.
 */
class ProfileType extends TraineeType
{
    /** @var array */
    protected $people;

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $propertyAccessor = new PropertyAccessor(true);

        $builder->addEventListener(FormEvents::POST_SET_DATA, array($this, 'updateFieldOptions'));

        $trainee = $options['data'];
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $form) use ($trainee) {
            if (!$trainee->getInstitution()) {
                $form->getForm()->remove('institution');
            }
        });

        $builder->remove('status');
        $builder->remove('function');
        $builder->remove('isPaying');
        $builder->remove('isActive');

        $builder
            ->add('status', null, array(
                'required' => false,
                'label' => 'Statut',
            ))
            ->add('function', null, array(
                'required' => false,
                'label' => 'Fonction',
            ));

        $blockedFields = ['organization', 'title', 'firstName', 'lastName', 'email', 'institution'];
        if ($options['isCnrs']) {
            $this->people = $options['people'];
            if ($this->people !== false) {
                if (!empty($this->people)) {
                    $this->addPeopleValidatorFields($builder, $this->people, $options['data']);
                } else {
                    $builder->addEventListener(FormEvents::POST_SET_DATA, array($this, 'removeValidatorFieldsIfNoChoices'));
                }
            } else {
                $builder->remove('validator');
                $builder->remove('cofo');
            }
        }

        foreach ($blockedFields as $field) {
            if ($builder->has($field) && ($propertyAccessor->getValue($options['data'], $field) || $builder->get($field)->getData())) {
                $builder->get($field)->setDisabled(true);
            }
        }
    }

    /**
     * Add institution field depending organization.
     *
     * @param FormInterface $form
     * @param Organization  $organization
     */
    protected function addInstitutionField(FormInterface $form, $organization)
    {
        if ($organization) {
            $form->add('institution', EntityType::class, array(
                'class' => Institution::class,
                'label' => 'Unité',
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
//                        ->where('i.organization = :organization')
//                        ->setParameter('organization', $organization)
//                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                },
                'disabled' => true,
            ));
        }
    }

    /**
     * @param FormInterface $form
     * @param $institution
     * @param $trainee
     */
    protected function addValidatorFields(FormInterface $form, $institution, $trainee)
    {
        if ($this->people === false) {
            parent::addValidatorFields($form, $institution, $trainee);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $people
     * @param Trainee $trainee
     */
    protected function addPeopleValidatorFields(FormBuilderInterface $builder, $people, Trainee $trainee)
    {
        $defaultValue = null;
        foreach ($people as $email => $person) {
            if ($person['refrole'] === true) {
                $defaultValue = $email;
                break;
            }
        }

        foreach ($people as $key => $person) {
            $people[$key] = $person['name'];
        }

        foreach (['validator' => 'Responsable valideur', 'cofo' => 'Correspondant formation'] as $field => $label) {
            $value = null;
            if ($field === 'validator' && $trainee->getValidator()) {
                if ($trainee->getValidator()->getEmail() !== $defaultValue) {
                    $value = $trainee->getValidator()->getEmail();
                }
            } elseif ($field === 'cofo' && $trainee->getCofo()) {
                if ($trainee->getCofo()->getEmail() !== $defaultValue) {
                    $value = $trainee->getCofo()->getEmail();
                }
            }
            $preferred_choices = array();
            if ($value) {
                $preferred_choices[] = $value;
            }
            if ($defaultValue && empty($preferred_choices)) {
                $preferred_choices[] = $defaultValue;
            }

            $builder->add('people_'.$field, ChoiceType::class, array(
                'label' => $label,
                'preferred_choices' => $preferred_choices,
                'choices' => $people,
                'mapped' => false,
                'required' => true,
                'label_attr' => [
                    'class' => 'label-red'
                ]
            ));
        }
    }

    /**
     * @param FormEvent $event
     */
    public function updateFieldOptions(FormEvent $event)
    {
        $form = $event->getForm();
        $field = $form->get('employmentContractEnd');
        $config = $field->getConfig();
        $options = $config->getOptions();
        $form->add($field->getName(), $config->getType()->getName(), array(
            'label' => $options['label'],
            'years' => isset($options['years']) ? $options['years'] : null,
            'required' => $options['required'],
        ));

        foreach (['phoneNumber', 'workplace', 'employmentContractType'] as $field) {
            $form = $event->getForm();
            $field = $form->get($field);
            $config = $field->getConfig();
            $options = $config->getOptions();
            $options['required'] = true;
            $form->add($field->getName(), $config->getType()->getName(), $options);
        }

        if ($form->has('organization')) {
            $field = $form->get('organization');
            $config = $field->getConfig();
            $options = $config->getOptions();
            $form->add($field->getName(), $config->getType()->getName(), array(
                'label' => 'Délégation',
                'class' => $options['class'],
                'query_builder' => $options['query_builder'],
                'disabled' => $form->get('institution')->getData() !== null,
                'required' => $options['required'],
            ));
        }
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('isCnrs');
        $resolver->setDefined(array('people'));
    }
}
