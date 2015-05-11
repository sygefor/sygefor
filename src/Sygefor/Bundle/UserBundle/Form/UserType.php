<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 18/03/14
 * Time: 10:18
 */

namespace Sygefor\Bundle\UserBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class UserType
 * @package Sygefor\Bundle\UserBundle\Form
 */
class UserType extends AbstractType
{
    /**
     * @var AccessRightRegistry
     */
    private $accessRightsRegistry;


    /**
     * @param AccessRightRegistry $registry
     */
    public function __construct(AccessRightRegistry $registry)
    {
        $this->accessRightsRegistry = $registry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array(
            'constraints' => new Length(array('min' => 4)),
            'invalid_message' => 'Le nom d\'utilisateur est trop court',
            'label' => 'Nom d\'utilisateur',
        ))
        ->add('email', 'email', array(
            'constraints' => new Email(array('message' => 'Invalid email address')),
            'label' => 'Email',
        ));

        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'constraints' => new Length(array('min' => 4)),
            'required' => !$builder->getForm()->getData()->getId(),
            'invalid_message' => 'Les mots de passe doivent correspondre',
            'first_options'  => array('label' => 'Mot de passe'),
            'second_options' => array('label' => 'Confirmation'),
        ));

        $builder->add('enabled', 'checkbox',array(
            'required' => false,
            'label' => 'Compte activé'
        ));

        $builder->add('organization', 'entity', array(
            'required' => true,
            'class' => 'Sygefor\Bundle\CoreBundle\Entity\Organization',
            'label' => 'URFIST',
            'query_builder' =>  function(EntityRepository $er) {
                return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
            }
          ));


        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_user.rights.user.all');
        if (!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user) {
                $trainer = $event->getData();
                $trainer->setOrganization($user->getOrganization());
                $event->getForm()->remove('organization');
            });
        }

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Sygefor\Bundle\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'user';
    }
}
