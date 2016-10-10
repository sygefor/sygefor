<?php

namespace Sygefor\Bundle\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrganizationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', 'text', array(
                'label' => 'Nom',
            ))
            ->add('code', 'text', array(
                'label' => 'Code',
            ))
            ->add('email', 'email', array(
                'label' => 'Email',
            ))
            ->add('phoneNumber', 'text', array(
                'label'    => 'Téléphone',
                'required' => false,
            ))
            ->add('faxNumber', 'text', array(
                'label'    => 'Numéro de fax',
                'required' => false,
            ))
            ->add('address', 'textarea', array(
                'label'    => 'Adresse',
                'required' => false,
            ))
            ->add('zip', 'text', array(
                'label'    => 'Code postal',
                'required' => false,
            ))
            ->add('city', 'text', array(
                'label'    => 'Ville',
                'required' => false,
            ))
            ->add('website', 'url', array(
                'label'    => 'Site internet',
                'required' => false,
            ))
            ->add('traineeRegistrable', 'checkbox', array(
                'label'    => 'Les stagiaires peuvent choisir cette organisation',
                'required' => false,
            ));

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
              $builder = $event->getForm();
              $organization = $event->getData();

              $builder->add('institution', 'entity', array(
                  'label'         => 'Etablissement de rattachement',
                  'class'         => AbstractInstitution::class,
                  'required'      => false,
                  'query_builder' => $organization->getId() ? function (EntityRepository $er) use ($organization) {
                      return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name');
                  } : null,
                ));
          });
    }
}
