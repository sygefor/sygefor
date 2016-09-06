<?php
namespace Sygefor\Bundle\ApiBundle\Sitemap;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Url
 * @package Veilhan\Bundle\APIBundle\Model
 */
class Url
{
    /**
     * @Serializer\XmlElement(cdata=false)
     */
    private $loc;

    /**
     * @Serializer\XmlList(inline = true, entry = "image:image")
     */
    private $images = array();

    /**
     * @Serializer\XmlList(inline = true, entry = "news:news")
     */
    private $news = array();

    /**
     * @param $loc
     */
    public function __construct($loc) {
        $this->loc = $loc;
    }

    /**
     * @param $image
     */
    public function addImage($image) {
        if(!$image instanceof Image) {
            $image = new Image($image);
        }
        $this->images[] = $image;
    }

    /**
     * @param News $news
     */
    public function addNews(News $news) {
        $this->news[] = $news;
    }

    /**
     * SPECIFIC
     * @param $medias
     */
    public function addMedias($medias) {
        foreach($medias as $media) {
            $media = $media['media'];
            if($media['type'] == 'image') {
                $image = new Image('http://media.veilhan.com/' . $media['publicPath'] . '/large.jpg');
                $image->setTitle($media['title']);
                $image->setCaption($media['description']);
                $this->addImage($image);
            }
        }
    }
}
