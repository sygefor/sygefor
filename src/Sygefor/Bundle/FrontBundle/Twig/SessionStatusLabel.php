<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/16/16
 * Time: 12:19 PM
 */

namespace Sygefor\Bundle\FrontBundle\Twig;


class SessionStatusLabel extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('sessionStatusLabel', array($this, 'twigSessionStatusLabel')),
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
        return 'sessionStatusLabel';
    }

    /**
     * @param string $statusLabel
     *
     * @return string
     */
    public function twigSessionStatusLabel($statusLabel)
    {
        $labels = array(
            '',
            '[ REPORTEE ]',
            '[ ANNULEE ]'
        );

        return isset($labels[$statusLabel]) ? $labels[$statusLabel] : "";
    }
}