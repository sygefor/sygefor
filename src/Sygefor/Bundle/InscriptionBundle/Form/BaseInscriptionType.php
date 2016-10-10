<?php

namespace Sygefor\Bundle\InscriptionBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Form\Type\EntityHiddenType;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class BaseInscriptionType.
 */
class BaseInscriptionType extends AbstractType
{
    protected $organization;

    function __construct($organization)
    {
         $this->organization = $organization;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $organization = $this->organization;
        /** @var AbstractSession $session */
        $session = $options['data']->getSession();

        $builder
            ->add('trainee', EntityHiddenType::class, array(
                'label'           => 'Stagiaire',
                'class'           => 'SygeforTraineeBundle:AbstractTrainee',
                'invalid_message' => '',
            ))
            ->add('session', EntityHiddenType::class, array(
                'label'           => 'Session',
                'class'           => 'SygeforTrainingBundle:Session\AbstractSession',
                'invalid_message' => 'Session non reconnue',
            ))
            ->add('inscriptionStatus', EntityType::class, array(
                'label'         => 'Status d\'inscription',
                'class'         => 'SygeforInscriptionBundle:Term\InscriptionStatus',
                'query_builder' => function (EntityRepository $repository) use ($organization) {
                    $qb = $repository->createQueryBuilder('i');
                    $qb->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null');

                    return $qb;
                },
            ))
            ->add('presenceStatus', EntityType::class, array(
                'label'         => 'Status de présence',
                'class'         => 'SygeforInscriptionBundle:Term\PresenceStatus',
                'query_builder' => function (EntityRepository $repository) use ($organization) {
                    $qb = $repository->createQueryBuilder('i');
                    $qb->where('i.organization = :organization')
                        ->setParameter('organization', $organization)
                        ->orWhere('i.organization is null');

                    return $qb;
                },
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AbstractInscription::class,
        ));
    }
}
