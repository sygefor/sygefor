<?php

namespace FrontBundle\Twig;

use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class OrderBy.
 */
class OrderBy extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
             new \Twig_SimpleFilter('orderBy', array($this, 'twigOrderBy')),
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
        return 'orderBy';
    }

    /**
     * Order an array with key.
     *
     * @param array|\Traversable $array An array
     * @param string             $key
     *
     * @return array
     */
    public function twigOrderBy($array, $key)
    {
        $propertyAccessor = new PropertyAccessor();
        usort($array, function ($a, $b) use ($propertyAccessor, $key) {
            return $propertyAccessor->getValue($a, $key) > $propertyAccessor->getValue($b, $key);
        });

        return $array;
    }
}
