<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/28/16
 * Time: 5:33 PM.
 */

namespace FrontBundle\Form\Type;

use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\Inscription;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Organization;
use AppBundle\Entity\Session\Session;
use AppBundle\Entity\Trainee\Trainee;
use Symfony\Component\Form\AbstractType;
use Sygefor\Bundle\CoreBundle\Entity\User;
use AppBundle\Entity\Institution\Institution;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class FilterRegistrationType.
 */
class FilterRegistrationType extends AbstractType
{
    /** @var Trainee */
    protected $user;

    /**
     * FilterRegistrationType constructor.
     *
     * @param Trainee $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;
        $builder
            ->add('trainee', EntityType::class, array(
                'label' => 'Stagiaire',
                'class' => Trainee::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('trainee')
                        ->leftJoin(Inscription::class, 'inscription', 'WITH', 'inscription.trainee = trainee.id')
                        ->where('inscription.trainee is not null')
                        ->orderBy('trainee.lastName')
                        ->addOrderBy('trainee.firstName');

                    return $this->filterValidator($qb, $options);
                },
                'choice_label' => function(Trainee $trainee) {
                  return $trainee->getReverseFullName();
                },
                'required' => false,
            ))
            ->add('training', EntityType::class, array(
                'label' => 'Stage',
                'class' => AbstractTraining::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('training')
                        ->leftJoin(Session::class, 'session', 'WITH', 'session.training = training.id')
                        ->leftJoin(Inscription::class, 'inscription', 'WITH', 'inscription.session = session.id')
                        ->leftJoin(Trainee::class, 'trainee', 'WITH', 'inscription.trainee = trainee.id')
                        ->where('inscription.trainee is not null')
                        ->orderBy('training.name');

                    return $this->filterValidator($qb, $options);
                },
                'required' => false,
            ))
            ->add('inscriptionStatus', EntityType::class, array(
                'label' => 'Statut',
                'class' => InscriptionStatus::class,
                'query_builder' => function (EntityRepository $er) use ($user, $options) {
                    $qb = $er->createQueryBuilder('inscriptionStatus')
                        ->leftJoin(User::class, 'user', 'WITH', 'user = :user')
                        ->leftJoin(Organization::class, 'organization', 'WITH', 'organization.id = user.organization')
                        ->where('inscriptionStatus.organization IS NULL')
                        ->orWhere('inscriptionStatus.organization = user.organization')
                        ->setParameter('user', $user)
                        ->orderBy('inscriptionStatus.name');

                    return $qb;
                },
                'required' => false,
            ))
            ->add('createdFrom', DateType::class, array(
                'label' => 'De',
                'required' => false,
            ))
            ->add('createdTo', DateType::class, array(
                'label' => 'A',
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Appliquer les filtres',
                'attr' => array(
                    'class' => 'btn btn-primary btn-block',
                ),
            ))
            ->add('csv', SubmitType::class, array(
                'label' => 'Télécharger au format CSV',
                'attr' => array(
                    'class' => 'btn btn-primary btn-block',
                ),
            ));
    }

    /**
     * @param $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
        $resolver->setDefaults(['isCofo' => false, 'isValidator' => false, 'isManager' => false]);
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $options
     *
     * @return QueryBuilder
     */
    private function filterValidator($qb, $options)
    {
        if ($options['isManager']) {
            return $qb
                ->leftJoin(Institution::class, 'institution', 'WITH', 'trainee.institution = institution.id')
                ->andWhere('institution = :institution')
                ->setParameter('institution', $this->user->getInstitution());
        }
        else {
            $field = $options['isValidator'] ? 'validator' : ($options['isCofo'] ? 'cofo' : null);
            if ($field) {
                return $qb->leftJoin(Trainee::class, $field, 'WITH', "trainee.$field = $field.id")
                    ->andWhere("$field.email = :email")
                    ->setParameter('email', $this->user->getEmail());
            }
            else {
                throw new AccessDeniedException();
            }
        }
    }
}
