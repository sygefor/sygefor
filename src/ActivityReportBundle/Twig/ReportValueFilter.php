<?php

namespace ActivityReportBundle\Twig;

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 11/15/17
 * Time: 4:51 PM.
 */
class ReportValueFilter extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('reportValue', array($this, 'twigReportValue')),
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
        return 'reportValue';
    }

    /**
     * Order an array with key.
     *
     * @param array|\Traversable $crosstab
     * @param string             $entity
     * @param string             $type
     *
     * @return array
     */
    public function twigReportValue($crosstab, $entity, $type)
    {
        foreach ($crosstab['rows'] as $row) {
            if (strstr($row['key'], $entity)) {
                if ($type) {
                    foreach ($row['data'] as $data) {
                        if (strstr($data['key'], $type)) {
                            return $data['value'];
                        }
                    }
                } else {
                    return $row['value'];
                }
            }
        }

        return 0;
    }
}
