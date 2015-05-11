<?php
namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Elastica\Filter\Query;
use Elastica\Query\FuzzyLikeThis;
use Elastica\Query\Match;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\QueryString;
use Elastica\Suggest\Phrase;
use FOS\RestBundle\View\View;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sygefor\Bundle\ApiBundle\Controller\SecurityController;
use Sygefor\Bundle\ApiBundle\Form\Type\ProfileType;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeArray;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Sygefor\Bundle\TraineeBundle\Form\ApiRegisterType;
use Sygefor\Bundle\TraineeBundle\Form\ArrayTraineeType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeArrayType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * This controller regroup actions related to account profile
 *
 * @package Sygefor\Bundle\TraineeBundle\Controller
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ProfileAccountController extends Controller
{
    /**
     * Profile
     *
     * @Route("/profile", name="api.account.profile", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.profile"})
     * @Method({"GET", "POST"})
     */
    public function profileAction(Request $request)
    {
        /** @var Trainee $trainee */
        $trainee = $this->getUser();
        if($request->getMethod() == 'POST') {
            $form = $this->createForm(new ProfileType(), $trainee);
            // remove extra fields
            $data = ProfileType::extractRequestData($request, $form);
            // if shibboleth, remove email
            if($trainee->getShibbolethPersistentId()) {
                $data['email'] = $trainee->getEmail();
            }
            // submit
            $form->submit($data, true);
            if($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                return array("updated" => true);
            } else {
                /** @var FormError $error */
                $parser = $this->get('sygefor_api.form_errors.parser');
                return new View(array('errors' => $parser->parseErrors($form)), 422);
            }
        }
        return $trainee;
    }
}
