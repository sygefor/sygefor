<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/1/17
 * Time: 12:12 PM.
 */

namespace AppBundle\Entity\Session;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

trait SessionPublipost
{
    /**
     * @Serializer\VirtualProperty
     *
     * @param $front_root_url
     * @param $apiSerialization
     *
     * @return string
     * @return string
     */
    public function getFrontUrl($front_root_url = 'https://sygefor.com', $apiSerialization = false)
    {
        $url = $front_root_url.'/training/'.$this->getTraining()->getId().'/';
        if (!$apiSerialization) {
            // URL permitting to register a private session
            if ($this->getRegistration() === Session::REGISTRATION_PRIVATE) {
                return $url.$this->getId().'/'.md5($this->getId() + $this->getTraining()->getId());
            }
        }

        // return public URL
        return $url.$this->getId();
    }

    /**
     * @return mixed
     */
    public function letterDateBegin()
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        return strftime('%A %e %B %Y', $this->dateBegin->getTimestamp());
    }

    /**
     * @return mixed
     */
    public function letterDateEnd()
    {
        if (!$this->dateEnd) {
            return null;
        }
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        return strftime('%A %e %B %Y', $this->dateEnd->getTimestamp());
    }

    /**
     * Get date range for OpenTBS.
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Default", "session", "api"})
     *
     * @return string
     */
    public function getDateRange()
    {
        if (!$this->dateBegin) {
            return '';
        }
        if (!$this->dateEnd || $this->dateBegin->format('d/m/y') === $this->dateEnd->format('d/m/y')) {
            return 'le '.$this->dateBegin->format('d/m/Y');
        }

        return 'du '.$this->dateBegin->format('d/m/Y').' au '.$this->dateEnd->format('d/m/Y');
    }

    /**
     * @return ArrayCollection
     */
    public function getDatesArray()
    {
        $dates = new ArrayCollection();
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');

        if ($this->dateBegin == $this->dateEnd) {
            $date = new \stdClass();
            $date->date = ucfirst($this->letterDateBegin());
            $dates->add($date);
        } elseif ($this->dateBegin < $this->dateEnd) {
            $dateIncr = clone $this->dateBegin;
            while ($dateIncr <= $this->dateEnd) {
                if (!in_array(strftime('%A', $dateIncr->getTimestamp()), array('samedi', 'dimanche'))) {
                    $date = new \stdClass();
                    $date->date = ucfirst(strftime('%A %e %B %Y', $dateIncr->getTimestamp()));
                    $dates->add($date);
                }
                $dateIncr->modify('+1 day');
            }
        } else {
            $date = new \stdClass();
            $date->date = 'La date de début fini avant la date de fin. Merci de vérifier les informations de la session.';
            $dates->add($date);
        }

        return $dates;
    }

    /**
     * Get date range in letter for OpenTBS.
     *
     * @return string
     */
    public function letterDateRange()
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        if (!$this->dateBegin) {
            return '';
        }
        if (!$this->letterDateEnd() || $this->letterDateBegin() === $this->letterDateEnd()) {
            return 'le '.$this->letterDateBegin();
        }

        return 'du '.$this->letterDateBegin().' au '.$this->letterDateEnd();
    }
}
