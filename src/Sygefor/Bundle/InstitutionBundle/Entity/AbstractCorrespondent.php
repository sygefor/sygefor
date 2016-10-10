<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 5/23/16
 * Time: 5:54 PM.
 */
namespace Sygefor\Bundle\InstitutionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\PersonTrait\PersonTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\InstitutionBundle\Form\BaseCorrespondentType;

/**
 * AbstractCorrespondent.
 *
 * @ORM\Table(name="correspondent")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
class AbstractCorrespondent
{
    use PersonTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"Default", "api"})
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getFullName();
    }

    public static function getFormType()
    {
        return BaseCorrespondentType::class;
    }

    public static function getType()
    {
        return 'correspondent';
    }
}
