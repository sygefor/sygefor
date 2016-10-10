<?php

namespace Sygefor\Bundle\TrainingBundle\Entity\Session\Term;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TrainingBundle\Form\PlaceType;

/**
 * Place.
 *
 * @ORM\Table(name="place")
 * @ORM\Entity
 */
class Place extends AbstractTerm implements VocabularyInterface
{
    /**
     * @ORM\Column(name="address", type="string", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $address;

    /**
     * @ORM\Column(name="postal", type="integer", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $postal;

    /**
     * @ORM\Column(name="city", type="string", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $city;

    /**
     * @ORM\Column(name="embed_map", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $embedMap;

    /**
     * @ORM\Column(name="room", type="string", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $room;

    /**
     * @ORM\Column(name="floor", type="string", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $floor;

    /**
     * @ORM\Column(name="staircase", type="string", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $staircase;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $phone;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     * @Serializer\Groups({"Default", "api.training"})
     */
    protected $precision;

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
     * @return mixed
     */
    public function getEmbedMap()
    {
        return $this->embedMap;
    }

    /**
     * @param mixed $embedMap
     */
    public function setEmbedMap($embedMap)
    {
        $this->embedMap = $embedMap;
    }

    /**
     * @return mixed
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param mixed $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return mixed
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param mixed $floor
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param mixed $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * @return mixed
     */
    public function getStaircase()
    {
        return $this->staircase;
    }

    /**
     * @param mixed $staircase
     */
    public function setStaircase($staircase)
    {
        $this->staircase = $staircase;
    }

    /**
     * @return mixed
     */
    public function getVocabularyName()
    {
        return 'Lieux de formation';
    }

    /**
     * @return mixed
     */
    public static function getFormType()
    {
        return PlaceType::class;
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_LOCAL;
    }

    /**
     * @return mixed
     *               This static method is used to set a specific order field
     *               when fetch terms (api)
     */
    public static function orderBy()
    {
        return 'name';
    }

    public function __toString()
    {
        return parent::__toString();
    }
}
