<?php
namespace Sygefor\Bundle\TrainerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ChangeOrganizationType
 * @package Sygefor\Bundle\TrainerBundle\Form
 */
class ChangeOrganizationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // we cant add event listener in listener, so we have to build the organization field now
        $trainee = $builder->getData();

        $builder
          ->add('organization', 'entity', array(
              'required' => true,
              'class' => 'Sygefor\Bundle\CoreBundle\Entity\Organization',
              'label' => 'Nouvelle URFIST',
              'query_builder' =>  function(EntityRepository $er) use ($trainee) {
                  return $er->createQueryBuilder('o')
                    ->where('o != :organization')
                    ->setParameter('organization', $trainee->getOrganization())
                    ->orderBy('o.name', 'ASC');
              }
            ));

        // add institution field on organization submit
        $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData());
        });
    }

    /**
     * Add institution field depending organization
     * @param FormInterface $form
     * @param Organization $organization
     */
    function addInstitutionField(FormInterface $form, $organization) {
        if($organization) {
            $form->add('institution', 'entity', array(
                'required' => true,
                'constraints' => new NotBlank(),
                'class' => 'Sygefor\Bundle\TrainingBundle\Entity\Term\Institution',
                'label' => 'Etablissement',
                'query_builder' => function(EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                      ->where('i.organization = :organization')
                      ->setParameter('organization', $organization)
                      ->orWhere('i.organization is null')
                      ->orderBy('i.name', 'ASC');
                }));
        }
    }

    /**
     * @return string
     */
    public function getName() {
        return 'changeOrganization';
    }
}
