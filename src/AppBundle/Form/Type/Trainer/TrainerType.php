<?php

namespace AppBundle\Form\Type\Trainer;

use AppBundle\Entity\Trainer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractTrainerType;

/**
 * Class TrainerType.
 */
class TrainerType extends AbstractTrainerType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('email', EmailType::class, array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', null, array(
                'label' => 'Numéro de téléphone',
            ))
            ->add('website', UrlType::class, array(
                'label' => 'Site internet',
            ))
            ->add('addressType', ChoiceType::class, array(
                'label' => 'Type d\'adresse',
                'choices' => array(
                    '0' => 'Adresse personnelle',
                    '1' => 'Adresse professionnelle',
                ),
                'required' => false,
            ))
            ->add('address', null, array(
                'label' => 'Adresse',
            ))
            ->add('zip', null, array(
                'label' => 'Code postal',
            ))
            ->add('city', null, array(
                'label' => 'Ville',
            ))
            ->add('status', null, array(
                'label' => 'Statut',
            ))
            ->add('isArchived', null, array(
                'label' => 'Archivé',
            ))
            ->add('isAllowSendMail', null, array(
                'label' => 'Autoriser les courriels',
            ))
            ->add('isOrganization', null, array(
                'label' => 'Intervenant interne',
            ))
            ->add('isPublic', null, array(
                'label' => 'Publié sur le web',
            ))
            ->add('responsabilities', null, array(
                'label' => 'Responsabilités',
                'required' => false,
            ));
    }

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		parent::configureOptions($resolver);

		$resolver->setDefaults([
			'data_class', Trainer::class,
		]);
	}
}
