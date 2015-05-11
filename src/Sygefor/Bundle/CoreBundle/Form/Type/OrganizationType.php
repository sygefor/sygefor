<?php

namespace Sygefor\Bundle\CoreBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrganizationType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('name','text', array ('label' => 'Nom'));
        $builder->add('phoneNumber','text', array ('label' => 'Téléphone'));
        $builder->add('faxNumber','text', array ('label' => 'Fax'));
        $builder->add('website','text', array ('label' => 'Site internet'));
        $builder->add('email','email', array ('label' => 'Email'));
        $builder->add('institutionName','text', array ('label' => 'Etablissement'));
        $builder->add('bp','text', array ('label' => 'Boîte postale', 'required' => false));
        $builder->add('address','textarea', array ('label' => 'Adresse'));
        $builder->add('zip','text', array ('label' => 'Code postal'));
        $builder->add('city','text', array ('label' => 'Ville'));

        // PRE_SET_DATA for the parent form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
              $builder = $event->getForm();
              $organization = $event->getData();
              $builder->add('institution','entity', array(
                  'label' => 'Etablissement de rattachement',
                  'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Institution',
                  'required' => false,
                  'query_builder' => function(EntityRepository $er) use ($organization) {
                      return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orderBy('i.name');
                  }
                ));

          });
    }


    public function getName()
    {
        return 'organization';
    }
}
