<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/8/16
 * Time: 4:22 PM.
 */
namespace Sygefor\Bundle\MyCompanyBundle\Entity\Term;

use Doctrine\ORM\Mapping as ORM;
use Sygefor\Bundle\MyCompanyBundle\Form\MenuItemType;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Vocabulary\VocabularyInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Onglet de menu.
 *
 * @ORM\Table(name="menu_item")
 * @ORM\Entity
 */
class MenuItem extends AbstractTerm implements VocabularyInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255)
     * @Assert\NotBlank()
     * @Serializer\Groups({"Default", "api"})
     */
    private $link;

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * returns the form type name for template edition.
     *
     * @return string
     */
    public static function getFormType()
    {
        return MenuItemType::class;
    }

    public static function getVocabularyStatus()
    {
        return VocabularyInterface::VOCABULARY_NATIONAL;
    }

    /**
     * @return string
     */
    public function getVocabularyName()
    {
        return 'Onglet de menu';
    }
}
