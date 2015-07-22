<?php

namespace Sygefor\Bundle\ElasticaBundle\Transformer;

use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Elastica\Document;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ModelToElasticaTransformer extends ModelToElasticaAutoTransformer
{
    /**
     * Transforms an object into an elastica object having the required keys
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     *
     * @return Document
     **/
    public function transform($object, array $fields)
    {
        $identifier = $this->propertyAccessor->getValue($object, $this->options['identifier']);
        $document = new Document($identifier);

        foreach ($fields as $key => $mapping) {
            if ($key == '_parent') {
                $property = (null !== $mapping['property'])?$mapping['property']:$mapping['type'];
                $value = $this->propertyAccessor->getValue($object, $property);
                $document->setParent($this->propertyAccessor->getValue($value, $mapping['identifier']));
                continue;
            }

            try {
                $value = $this->propertyAccessor->getValue($object, $key);
            } catch(NoSuchPropertyException $e) {   // catch the NoSuchPropertyException to avoid error
                continue;
            } catch(UnexpectedTypeException $e) {   // catch the UnexpectedTypeException to avoid error
                continue;
            }

            if (isset($mapping['type']) && in_array($mapping['type'], array('nested', 'object')) && isset($mapping['properties']) && !empty($mapping['properties'])) {
                /* $value is a nested document or object. Transform $value into
                 * an array of documents, respective the mapped properties.
                 */
                $document->set($key, $this->transformNested($value, $mapping['properties']));
                continue;
            }

            if (isset($mapping['type']) && $mapping['type'] == 'attachment') {
                // $value is an attachment. Add it to the document.
                if ($value instanceof \SplFileInfo) {
                    $document->addFile($key, $value->getPathName());
                } else {
                    $document->addFileContent($key, $value);
                }
                continue;
            }

            //if($value !== null) {
                $document->set($key, $this->normalizeValue($value));
            //}
        }

        return $document;
    }

    /**
     * Attempts to convert any type to a string or an array of strings
     *
     * @param mixed $value
     *
     * @return string|array
     */
    protected function normalizeValue($value)
    {
        $normalizeValue = function(&$v)
        {
            if ($v instanceof \DateTime) {
                // deactivate timezone support
                // $v = $v->format('c');
                $v = $v->format('Y-m-d\TH:i:s');
            } elseif (!is_scalar($v) && !is_null($v)) {
                $v = (string)$v;
            }
        };

        if (is_array($value) || $value instanceof \Traversable || $value instanceof \ArrayAccess) {
            $value = is_array($value) ? $value : iterator_to_array($value, false);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }
}
