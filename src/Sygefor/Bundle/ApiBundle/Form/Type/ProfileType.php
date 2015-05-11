<?php
namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\TraineeBundle\Form\AbstractTraineeType;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProfileType
 * @package Sygefor\Bundle\ApiBundle\Form\Type
 */
class ProfileType extends AbstractTraineeType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // HACK : remove 'Professionnel de l'information' from public type choice
        $builder
          ->add('publicCategory', 'entity', array(
              'class' => 'Sygefor\Bundle\CoreBundle\Entity\Term\PublicType',
              'required' => true,
              'label' => "Catégorie de public",
              'query_builder' => function(EntityRepository $er) {
                  return $er->createQueryBuilder('d')
                    ->where('d.parent IS NULL')
                    ->andWhere('d.private = 0');
              }));

        $builder->get('publicCategory')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
              $this->addProfessionalSituationField($event->getForm()->getParent(), $event->getForm()->getData());
          });
    }

    /**
     * @param $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('Default', 'trainee', 'api.profile')
        ));
    }

    /**
     * Helper : request data extractor
     *
     * @param Request $request
     * @param FormInterface $form
     * @return array
     */
    static public function extractRequestData(Request $request, FormInterface $form) {
        // remove extra fields
        $data = $request->request->all();
        $keys = array_keys($form->all());
        $keys = array_merge($keys, array('institution', 'disciplinary', 'professionalSituation'));
        $data = array_intersect_key($data, array_flip($keys));
        $data = array_merge(array("addressType" => 0), $data);
        return $data;
    }
}
