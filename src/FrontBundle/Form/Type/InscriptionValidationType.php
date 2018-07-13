<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/9/17
 * Time: 3:53 PM.
 */

namespace FrontBundle\Form\Type;

use AppBundle\Entity\Inscription;
use AppBundle\Entity\Term\Priority;
use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InscriptionValidationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Inscription $inscription */
        $inscription = $options['data'];

        $builder
            ->add('inscriptionStatus', EntityType::class, array(
                'label' => 'Statut d\'inscription',
                'class' => InscriptionStatus::class,
                'expanded' => true,
                'read_only' => !$options['editable'],
                'query_builder' => function (EntityRepository $er) use ($inscription) {
                    return $er->createQueryBuilder('status')
                        ->where('status.machineName LIKE :rh')
//                        ->orWhere('status = :currentStatus')
                        ->setParameter('rh', 'rh_%');
//                        ->setParameter('currentStatus', $inscription->getInscriptionStatus());
                },
            ))
            ->add('priority', EntityType::class, array(
                'label' => 'Priorité dans l\'Unité',
                'class' => Priority::class,
                'expanded' => true,
                'choice_label' => function ($priority) {
                    return implode(' - ', array($priority->getName(), $priority->getDescription()));
                },
                'read_only' => !$options['editable'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('priority')
                        ->orderBy('priority.'.Priority::orderBy(), 'ASC');
                },
            ))
            ->add('hierarchicalManagerReview', TextareaType::class, array(
                'label' => 'Avis motivé',
                'read_only' => !$options['editable'],
                'attr' => array(
                    'rows' => 3,
                ),
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'editable',
        ));
        $resolver->setDefaults(array(
            'data_class' => Inscription::class,
        ));
    }
}
