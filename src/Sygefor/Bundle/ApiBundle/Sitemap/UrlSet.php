<?php

namespace Sygefor\Bundle\ApiBundle\Sitemap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("urlset")
 * @Serializer\XmlNamespace(uri="http://www.sitemaps.org/schemas/sitemap/0.9")
 * @Serializer\XmlNamespace(uri="http://www.google.com/schemas/sitemap-image/1.1", prefix="image")
 * @Serializer\XmlNamespace(uri="http://www.google.com/schemas/sitemap-video/1.1", prefix="video")
 * @Serializer\XmlNamespace(uri="http://www.google.com/schemas/sitemap-mobile/1.0", prefix="mobile")
 * @Serializer\XmlNamespace(uri="http://www.google.com/schemas/sitemap-news/0.9", prefix="news")
 */
class UrlSet
{
    /**
     * @Serializer\XmlList(inline = true, entry = "url")
     */
    private $urls = array();

    /**
     * @param Url $url
     */
    public function addUrl($url) {
        if( ! $url instanceof Url) {
            $url = new Url($url);
        }
        $this->urls[] = $url;
    }
}
