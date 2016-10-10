<?php

namespace Sygefor\Bundle\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;

/**
 * @ORM\Entity
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    protected $user;
}
