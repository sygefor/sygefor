<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 25/06/14
 * Time: 15:02
 */

namespace Sygefor\Bundle\CoreBundle\Form\DataTransformer;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ObjectToIdTransformer
 * @package Sygefor\Bundle\TraineeBundle\Form\DataTransformer
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /** @var ObjectManager $om */
    private $om;
    private $entityClass;
    private $entityRepository;


    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param $entity
     * @return mixed
     * @throws TransformationFailedException
     */
    public function transform($entity)
    {
        if (null === $entity || '' === $entity ) {
            return null;
        }
        return $entity->getId();
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * This method is called when {@link Form::submit()} is called to transform the requests tainted data
     * into an acceptable format for your data processing/model layer.
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as empty strings). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param mixed $id The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $object = $this->om->getRepository($this->entityClass)->find($id);

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
                'An instance of "%s" with id "%s" does not exist!',
                $this->entityClass,
                $id
            ));// return null;
        }

        return $object;
    }

    /**
     * @param $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @param $entityRepository
     */
    public function setEntityRepository($entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

} 