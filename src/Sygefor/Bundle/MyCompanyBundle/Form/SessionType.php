<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 14:30.
 */
namespace Sygefor\Bundle\MyCompanyBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\MyCompanyBundle\Entity\Module;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Sygefor\Bundle\MyCompanyBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Form\BaseSessionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SessionType.
 */
class SessionType extends BaseSessionType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AbstractSession $session */
        $session = isset($options['data']) ? $options['data'] : null;

        $builder
            ->add('name', TextType::class, array(
                'label'    => "Intitulé",
                'required' => false
            ))
            ->add('price', MoneyType::class, array(
                'label'    => "Prix",
                'required' => false
            ));

        if ($session && method_exists($session->getTraining(), 'getModules')) {
            $builder
                ->add('module', EntityType::class, array(
                    'label'         => 'Module',
                    'class'         => Module::class,
                    'query_builder' => function (EntityRepository $er) use ($session) {
                        return $er->createQueryBuilder('m')
                            ->where('m.training = :training')
                            ->setParameter('training', $session->getTraining())
                            ->orderBy('m.name');
                    },
                    'required' => false,
                ))
                ->add('newModule', Module::getFormType(), array(
                    'label'    => 'Nouveau module',
                    'required' => false,
                ));
        }

        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Session::class,
        ));
    }
}
