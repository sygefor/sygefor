<?php
namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;

use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class VocabularyType
 * @package Sygefor\Bundle\TaxonomyBundle\Form
 */
class VocabularyType extends AbstractType
{

    /**
     * @var SecurityContext $securityContext;
     */
    protected $securityContext;

    /**
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array ('label' => 'Nom'));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'vocabulary';
    }
}
