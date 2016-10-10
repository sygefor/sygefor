<?php

namespace Sygefor\Bundle\InstitutionBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\InstitutionType as InstitutionTypeTerm;
use Sygefor\Bundle\CoreBundle\AccessRight\AccessRightRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class BaseInstitutionType.
 */
class BaseInstitutionType extends AbstractType
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
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organization', EntityType::class, array(
                'label'         => 'Centre',
                'class'         => Organization::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                },
                'required' => true,
            ))
            ->add('name', TextType::class, array(
                'label' => 'Nom',
            ))
            ->add('address', TextareaType::class, array(
                'label' => 'Adresse',
                'required' => false,
            ))
            ->add('zip', TextType::class, array(
                'label' => 'Code postal',
                'required' => false,
            ))
            ->add('city', TextType::class, array(
                'label' => 'Ville',
                'required' => false,
            ))
            ->add('institutionType', EntityType::class, array(
                'label' => 'Type',
                'required' => false,
                'class' => InstitutionTypeTerm::class,
            ))
            ->add('geographicOrigin', EntityType::class, array(
                'label'         => 'Origine géographique',
                'class'         => GeographicOrigin::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                },
                'required' => false,
            ))
            ->add('manager', BaseCorrespondentType::class, array(
                'label'              => "Directeur de l'établissement",
                'required'           => false,
                'allow_extra_fields' => $options['allow_extra_fields'],
            ));

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_training.rights.institution.all.create');
        if (!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user            = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                $institution = $event->getData();
                if ($institution) {
                    $institution->setOrganization($user->getOrganization());
                    $event->getForm()->remove('organization');
                }
            });
        }
    }
}
