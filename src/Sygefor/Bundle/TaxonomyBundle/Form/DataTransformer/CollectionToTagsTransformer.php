<?php
namespace Sygefor\Bundle\TaxonomyBundle\Form\DataTransformer;

/*
 * Auteur: Blaise de Carné - blaise@concretis.com
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CollectionToTagsTransformer implements DataTransformerInterface
{
    protected $em;
    protected $choiceList;
    protected $property;
    protected $class;
    protected $classMetadata;
    protected $accessor;
    protected $prePersist;

    /**
     * @param EntityManager $em
     * @param EntityChoiceList $choiceList
     * @param $property
     */
    public function __construct(EntityManager $em, EntityChoiceList $choiceList, $class, $property, $prePersist = null)
    {
        $this->em = $em;
        $this->choiceList = $choiceList;
        $this->property = $property;
        $this->prePersist = $prePersist;

        $this->classMetadata = $em->getClassMetadata($class);
        $this->class = $this->classMetadata->getName();
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Transforms a collection into tags
     *
     * @param Collection $collection A collection of entities
     *
     * @return mixed An array of entities
     *
     * @throws TransformationFailedException
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }
        if (!$collection instanceof Collection) {
            throw new TransformationFailedException('Expected a Doctrine\Common\Collections\Collection object.');
        }
        $tags = array();
        foreach($collection as $entity) {
            $tags[] = $this->accessor->getValue($entity, $this->property);
        }
        return implode(", ", $tags);
    }

    /**
     * Transforms tags into entities.
     *
     * @param mixed $tags An array of entities
     *
     * @return Collection   A collection of entities
     */
    public function reverseTransform($tags)
    {
        if(empty($tags)) {
            return new ArrayCollection();
        }
        $entities = array();
        $repository = $this->em->getRepository($this->class);
        if(!is_array($tags)) {
            $tags = explode(",", $tags);
        }
        foreach($tags as $tag) {
            $tag = trim($tag);
            $entity = $repository->findOneBy(array($this->property => $tag));
            if(!$entity) {
                $entity = new $this->class();
                $this->accessor->setValue($entity, $this->property, $tag);
                if($this->prePersist) {
                    call_user_func($this->prePersist, $entity);
                }
                $this->em->persist($entity);
            }
            $entities[] = $entity;
        }
        $this->em->flush();
        return new ArrayCollection($entities);
    }
}
