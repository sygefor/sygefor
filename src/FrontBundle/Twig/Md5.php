<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/8/17
 * Time: 10:20 AM.
 */

namespace FrontBundle\Twig;

/**
 * Class Md5.
 */
class Md5 extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('md5', array($this, 'twigMd5')),
        );

        return $filters;
    }

    /**
     * Name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'md5';
    }

    /**
     * Order an array with key.
     *
     * @param mixed
     *
     * @return array
     */
    public function twigMd5($attr)
    {
        return md5($attr);
    }
}
