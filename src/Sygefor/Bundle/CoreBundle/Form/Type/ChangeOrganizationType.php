<?php

namespace Sygefor\Bundle\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ChangeOrganizationType.
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
        $entity = $builder->getData();

        $builder
            ->add('organization', EntityType::class, array(
                'label' => 'Nouveau centre',
                'class' => Organization::class,
                'query_builder' => function (EntityRepository $er) use ($entity) {
                    return $er->createQueryBuilder('o')
                        ->where('o != :organization')
                        ->setParameter('organization', $entity->getOrganization())
                        ->orderBy('o.name', 'ASC');
                },
                'required' => true,
            ));

        // add institution field on organization submit
        $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($entity) {
            $this->addInstitutionField($event->getForm()->getParent(), $event->getForm()->getData(), $entity);
        });
    }

    /**
     * Add institution field depending organization.
     *
     * @param FormInterface $form
     * @param Organization $organization
     * @param mixed $entity
     */
    function addInstitutionField(FormInterface $form, $organization, $entity)
    {
        if ($organization && method_exists($entity, 'getInstitution')) {
            $form->add('institution', EntityType::class, array(
                'label' => 'Etablissement',
                'class' => AbstractInstitution::class,
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('i')
                        ->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null')
                        ->orderBy('i.name', 'ASC');
                },
                'constraints' => new NotBlank(array('message' => 'Vous devez sélectionner un établissement')),
                'required' => true,
            ));
        }
    }
}
