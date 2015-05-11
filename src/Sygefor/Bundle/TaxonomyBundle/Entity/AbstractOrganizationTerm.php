<?php
namespace Sygefor\Bundle\TaxonomyBundle\Entity;

use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class AbstractOrganizationTerm
 * @package Sygefor\Bundle\TaxonomyBundle\Entity
 * @ORM\MappedSuperclass()
 */
abstract class AbstractOrganizationTerm extends AbstractTerm
{
    /**
     * @var Organization $organization
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\Organization")
     */
    protected $organization;

    /**
     * @return boolean
     */
    public final function isNational()
    {
        return false;
    }

    /**
     * @param \Sygefor\Bundle\CoreBundle\Entity\Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return \Sygefor\Bundle\CoreBundle\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * api helper
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "api"})
     * @return integer
     */
    public function getOrganizationId()
    {
        return $this->getOrganization() ? $this->getOrganization()->getId() : null;
    }
}
