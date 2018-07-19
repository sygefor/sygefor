<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 11/28/17
 * Time: 4:54 PM.
 */

namespace FrontBundle\Twig;

class CkEditor extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('ckeditor', array($this, 'twigCkeditor')),
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
        return 'ckeditor';
    }

    /**
     * Order an array with key.
     *
     * @param mixed
     *
     * @return array
     */
    public function twigCkeditor($attr)
    {
        return strip_tags($attr, '<p><br/><a><strong><em><s><ol><ul><li><blockquote><h1>');
    }
}
