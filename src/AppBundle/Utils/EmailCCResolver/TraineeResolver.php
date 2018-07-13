<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/1/17
 * Time: 4:13 PM.
 */

namespace AppBundle\Utils\EmailCCResolver;

use AppBundle\Entity\Inscription;
use AppBundle\Entity\Session\Participation;
use Sygefor\Bundle\CoreBundle\Utils\Email\EmailResolverInterface;

/**
 * Class TraineeResolver.
 */
class TraineeResolver implements EmailResolverInterface
{
    /**
     * @return string
     */
    public static function getName()
    {
        return 'Intervenant';
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public static function supports($class)
    {
        return $class === Inscription::class;
    }

    /**
     * @return bool
     */
    public static function checkedByDefault()
    {
        return false;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public static function resolveName($entity)
    {
        $trainerNames = [];
        /** @var Participation $participation */
        foreach ($entity->getSession()->getParticipations() as $participation) {
            $trainerNames[] = $participation->getTrainer()->getFullName();
        }

        return $trainerNames;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public static function resolveEmail($entity)
    {
        $trainerEmails = [];
        /** @var Participation $participation */
        foreach ($entity->getSession()->getParticipations() as $participation) {
            $trainerEmails[] = $participation->getTrainer()->getEmail();
        }

        return $trainerEmails;
    }
}
