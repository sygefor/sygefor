<?php
namespace Sygefor\Bundle\TaxonomyBundle\Form\Type;

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

        if ( !$builder->getData()->isNational() ) {
            $this->addOrganizationField($builder) ;
        }
    }

    /**
     * Adds an organization field to the builder.
     * If user is granted, a selectbox is displayed, Otherwise an hidden field is added
     * @param FormBuilderInterface $builder
     *
     */
    private function addOrganizationField(FormBuilderInterface $builder)
    {
        $user = $this->securityContext->getToken()->getUser();
        $vocId = $builder->getData()->getId() ;
        //we check if user can create a new term or edit existing term
        if ( ( empty( $vocId ) && !$this->securityContext->isGranted('ADD', 'Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface') )
            || (!$this->securityContext->isGranted('EDIT', $builder->getData())) ) {

            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function(FormEvent $event) use ($user) {
                    $event->getData()->setOrganization($user->getOrganization());
                }
            );
        }
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
