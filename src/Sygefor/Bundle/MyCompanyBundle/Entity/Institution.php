<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/8/16
 * Time: 12:55 PM
 */

namespace Sygefor\Bundle\MyCompanyBundle\Entity;


use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Sygefor\Bundle\MyCompanyBundle\Form\InstitutionType;

/**
 *
 * @ORM\Table(name="institution")
 * @ORM\Entity
 */
class Institution extends AbstractInstitution
{
    public static function getFormType()
    {
        return InstitutionType::class;
    }

    /**
     * loadValidatorMetadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('zip', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner un code postal.',
        )));
        $metadata->addPropertyConstraint('city', new Assert\NotBlank(array(
            'message' => 'Vous devez renseigner une ville.',
        )));
    }
}