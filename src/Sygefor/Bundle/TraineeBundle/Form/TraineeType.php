<?php
namespace Sygefor\Bundle\TraineeBundle\Form;

use Doctrine\ORM\EntityRepository;
use Sygefor\Bundle\UserBundle\AccessRight\AccessRightRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContext;


/**
 * Class TraineeType
 * @package Sygefor\Bundle\TraineeBundle\Form
 */
class TraineeType extends AbstractTraineeType
{
    /** @var  AccessRightRegistry $accessRightsRegistry */
    protected $accessRightsRegistry;

    /**
     * @param AccessRightRegistry $accessRightsRegistry
     */
    public function __construct(AccessRightRegistry $accessRightsRegistry)
    {
        $this->accessRightsRegistry = $accessRightsRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // If the user does not have the rights, remove the organization field and force the value
        $hasAccessRightForAll = $this->accessRightsRegistry->hasAccessRight('sygefor_trainee.rights.trainee.all.create');
        if(!$hasAccessRightForAll) {
            $securityContext = $this->accessRightsRegistry->getSecurityContext();
            $user = $securityContext->getToken()->getUser();
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user) {
                $trainee = $event->getData();
                $trainee->setOrganization($user->getOrganization());
                $event->getForm()->remove('organization');
            });
        }
    }

}
