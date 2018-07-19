<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 8/10/17
 * Time: 5:53 PM.
 */

namespace FrontBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProgramFilterType.
 */
class ProgramFilterType extends AbstractType
{
    /** @var array */
    protected $keyNames;

    /** @var array */
    protected $facets;

    /** @var ArrayCollection */
    protected $entities;

    public function __construct()
    {
        $this->keyNames = array(
            'theme' => ['key' => 'getId', 'label' => 'getName', 'name' => 'Thème'],
            'typeLabel' => ['name' => 'Type de formation'],
            'place' => ['key' => 'getName', 'label' => 'getName', 'name' => 'Lieu de formation'],
            'year' => ['name' => 'Année'],
            'semester' => ['name' => 'Semestre'],
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->facets = $options['facets'];
        $this->entities = $options['entities'];

        foreach ($this->keyNames as $key => $values) {
            $builder->add($key, ChoiceType::class, array(
                'label' => $values['name'],
                'choices' => $this->getFacetChoices($key),
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'attr' => array(
                    'count' => $this->getFacetCount($key),
                ),
            ));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'facets',
            'entities',
        ));
    }

    /**
     * @param $field
     *
     * @return array
     */
    protected function getFacetChoices($field)
    {
        $choices = array();
        foreach ($this->facets[$field]['terms'] as $facet) {
            $choices[$facet['term']] = $this->getLabel($this->entities, $field, $facet['term']);
        }

        return $choices;
    }

    /**
     * @param $field
     *
     * @return array
     */
    protected function getFacetCount($field)
    {
        $counts = array();
        foreach ($this->facets[$field]['terms'] as $facet) {
            $counts[$facet['term']] = $facet['count'];
        }

        return $counts;
    }

    /**
     * @param $entities
     * @param $name
     * @param $key
     *
     * @return mixed
     */
    protected function getLabel($entities, $name, $key)
    {
        if (isset($this->keyNames[$name]['key'])) {
            $keyFunc = $this->keyNames[$name]['key'];
            $labelFunc = $this->keyNames[$name]['label'];
            foreach ($entities[$name] as $entity) {
                if ($entity->$keyFunc() === $key) {
                    return $entity->$labelFunc();
                }
            }
        }

        return $this->overrideName($name, $key);
    }

    /**
     * @param $name
     * @param $key
     *
     * @return string
     */
    protected function overrideName($name, $key)
    {
        if ($name == 'semester') {
            $key = ($key == 1 ? '1er' : '2ème').' semestre';
        }

        return $key;
    }
}
