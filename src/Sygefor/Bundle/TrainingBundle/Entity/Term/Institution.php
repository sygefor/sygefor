<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractOrganizationTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface;

/**
 * Institution
 *
 * @ORM\Table(name="institution")
 * @ORM\Entity
 */
class Institution extends AbstractOrganizationTerm implements LocalVocabularyInterface
{
     /**
     * @ORM\Column(name="address", type="string")
     * @Assert\NotBlank()
     */
    protected $address;

    /**
     * @ORM\Column(name="postal", type="integer")
     * @Assert\NotBlank()
     */
    protected $postal;

    /**
     * @ORM\Column(name="city", type="string")
     * @Assert\NotBlank()
     */
    protected $city;

    /**
     * @ORM\Column(name="bp", type="string", nullable=true)
     */
    protected $bp;

    /**
     * @ORM\Column(name="phone_number", type="string", nullable=true)
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(name="is_school", type="boolean", nullable=true)
     */
    protected $isSchool;

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $postal
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;
    }

    /**
     * @return mixed
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $bp
     */
    public function setBp($bp)
    {
        $this->bp = $bp;
    }

    /**
     * @return mixed
     */
    public function getBp()
    {
        return $this->bp;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $isSchool
     */
    public function setIsSchool($isSchool)
    {
        $this->isSchool = $isSchool;
    }

    /**
     * @return mixed
     */
    public function getIsSchool()
    {
        return $this->isSchool;
    }

     /**
     * @return mixed
     */
    public function getVocabularyName(){
        return "Etablissements";
    }

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return 'institution';
    }

    /**
     * @return mixed
     * This static method is used to set a specific order field
     * when fetch terms (api)
     */
    public static function orderBy()
    {
        return 'name';
    }
}
