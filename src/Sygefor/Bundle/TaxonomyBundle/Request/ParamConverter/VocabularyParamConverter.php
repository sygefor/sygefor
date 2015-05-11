<?php
namespace Sygefor\Bundle\TaxonomyBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VocabularyParamConverter
 * @package Sygefor\Bundle\TaxonomyBundle\Request\ParamConverter
 */
class VocabularyParamConverter implements ParamConverterInterface
{
    /**
     * @var VocabularyRegistry $registry
     */
    private $registry;

    /**
     * {@inheritdoc}
     */
    public function __construct(VocabularyRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        // if there is no manager,
        if (null === $this->registry) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();
        $options = $this->getOptions($configuration);
        $id = $this->getIdentifier($request, $options, $name);
        if($vocabulary = $this->registry->getVocabularyById($id)) {
            $request->attributes->set($name, $vocabulary);
            return true;
        }
        return false;
    }

    /**
     * @param Request $request
     * @param $options
     * @param $name
     * @return bool|mixed
     */
    protected function getIdentifier(Request $request, $options, $name)
    {
        if (isset($options['id'])) {
            $name = $options['id'];
        }
        if ($request->attributes->has($name)) {
            return $request->attributes->get($name);
        }
        if ($request->attributes->has('id')) {
            return $request->attributes->get('id');
        }
        return false;
    }

    /**
     * @param ParamConverter $configuration
     * @return array
     */
    protected function getOptions(ParamConverter $configuration)
    {
        return array_replace(array(
            'security' => true
        ), $configuration->getOptions());
    }
}
