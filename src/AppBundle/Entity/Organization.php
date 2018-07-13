<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Form\Type\OrganizationType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractOrganization;

/**
 * Organization.
 *
 * IMPORTANT : serialization is handle by YML
 * to prevent rules from CoordinatesTrait being applied to private infos (trainee, trainer)
 *
 * @see Resources/config/serializer/Entity.Organization.yml
 * NO SERIALIZATION INFO IN ANNOTATIONS !!!
 *
 * @ORM\Table(name="organization")
 * @ORM\Entity
 */
class Organization extends AbstractOrganization
{
    use CoordinatesTrait;

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return OrganizationType::class;
    }
}
