<?php

namespace FrontBundle\Form\Type;

use AppBundle\Form\Type\Trainee\TraineeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

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

        // not a shibboleth account
        if (!$options['data']->getId() && !$options['data']->getShibbolethPersistentId()) {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => array(
                    new Length(array('min' => 8)),
                    new NotBlank(),
                ),
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options' => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmation'),
            ]);
        }
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}
