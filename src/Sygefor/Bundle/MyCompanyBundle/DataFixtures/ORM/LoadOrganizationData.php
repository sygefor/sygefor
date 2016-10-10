<?php

namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class LoadOrganizationData.
 */
class LoadOrganizationData extends AbstractTermLoad
{
    static $class = Organization::class;

    public function getTerms()
    {
        return array(
            array(
                'name'        => 'MyCompany',
                'code'        => 'company',
                'address'     => '',
                'zip'         => '',
                'city'        => '',
                'email'       => 'contact@my-company.dev',
                'phoneNumber' => '',
                'faxNumber'   => '',
                'website'     => '',
                'trainee_registrable' => true,
            ),
        );
    }

    function getOrder()
    {
        return 0;
    }
}
