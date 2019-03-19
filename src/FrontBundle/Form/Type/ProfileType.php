<?php

namespace FrontBundle\Form\Type;

use AppBundle\Form\Type\Trainee\TraineeType;
use Sygefor\Bundle\ApiBundle\Form\Type\CguType;
use Symfony\Component\Form\FormBuilderInterface;
use Sygefor\Bundle\ApiBundle\Form\Type\ConsentType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sygefor\Bundle\ApiBundle\Form\Type\NewsletterType;
use Sygefor\Bundle\CoreBundle\Form\Type\StrongPasswordType;

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

        $builder->remove('isPaying');
        $builder->remove('isActive');

        $builder->add('newsletter', NewsletterType::class, [
	        'label' => 'Lettres d\'informations',
	        'widget_checkbox_label' => 'label',
	        'help_block' => 'En me désabonnant des lettres d\'informations, je recevrai toujours les notifications relatives à mes demandes d\'inscription.',
	        'required' => false,
        ]);

        $trainee = $options['data'];
        if (!$trainee->getId() || !$trainee->getCgu()) {
        	$builder->add('cgu', CguType::class, [
		        'label' => 'Conditions générales d\'utilisation',
		        'widget_checkbox_label' => 'label',
		        'help_block' => 'J\'accepte les conditions générales d\'utilisation de la plateforme.'
	        ]);
        }
        if (!$trainee->getId() || !$trainee->getConsent()) {
        	$builder->add('consent', ConsentType::class, [
	            'label' => 'Consentement de l\'utilisation de mes données',
		        'widget_checkbox_label' => 'label',
		        'help_block' => 'Je consent à l\'utilisation de mes données à des fins statistiques (anonymisées) ou de gestion de mes inscriptions.'
	        ]);
        }

        // not a shibboleth account
        if (!$trainee->getId() && !$trainee->getShibbolethPersistentId()) {
            $builder->add('plainPassword', StrongPasswordType::class);
        }

        if ($trainee->getShibbolethPersistentId()) {
        	$builder->get('email')->setDisabled(true);
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
