<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Sygefor\Bundle\CoreBundle\Entity\Term\AbstractTerm;
use Sygefor\Bundle\CoreBundle\Tests\Entity\MyNationalVocabulary;
use Sygefor\Bundle\CoreBundle\Tests\Entity\MyOrganizationVocabulary;
use Symfony\Bundle\FrameworkBundle\Client as Client;
use Symfony\Component\HttpFoundation\Request;

class TaxonomyControllerTest extends WebTestCase
{
    /**
     *
     */
    public function testIndex()
    {
        $this->createTempUser('user_norights');
        $this->loginAs('user_norights');

        $url = $this->client->getContainer()->get('router')->generate('taxonomy.index', array(), false);

        $this->client->request('GET', $url);

        $this->assertResponseSuccess(false);

        $this->createTempUser('user_rightown', array('sygefor_core.rights.vocabulary.own', 'sygefor_core.rights.vocabulary.all', 'sygefor_core.rights.vocabulary.national') );
        $this->loginAs('user_rightown');
        $this->client->request('GET', $url);

        $this->assertResponseSuccess(true);
    }

    /**
     *
     */
    public function testViewNationalVocabulary()
    {

        $this->createTempUser('view_user_noright' );
        $this->loginAs('view_user_noright');
        $this->client->request('GET', $this->generateUrl('taxonomy.view', array('id' => 'sygefor_core.vocabulary_national'), false));

        $this->assertResponseSuccess(false);

        $this->createTempUser('view_user_rightnat', array('sygefor_core.rights.vocabulary.national') );
        $this->loginAs('view_user_rightnat');
        $this->client->request('GET', $this->generateUrl('taxonomy.view', array('id' => 'sygefor_core.vocabulary_national'), false));

        $this->assertResponseSuccess(true);
    }

    /**
     *
     */
    public function testViewSameOrganizationVocabulary()
    {
        $em = $this->getEntityManager();

        $org = new Organization();
        $org->setName('org_temp');
        $org->setCode('org_temp');
        $em->persist($org);

        $otherOrg = new Organization();
        $otherOrg->setName('other_org_temp');
        $otherOrg->setCode('other_org_temp');
        $em->persist($otherOrg);

        $em->flush();

        $first0rg = $em->getRepository('SygeforCoreBundle:Organization')->findOneBy(array());

        $this->createTempUser('user_noright', array());
        $this->createTempUser('view_user_rightown', array('sygefor_core.rights.vocabulary.own'), array('organization' => $org) );

        $this->loginAs('view_user_rightown');
        $this->client->request('GET', $this->generateUrl('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => $otherOrg->getId()), false));
        $this->assertResponseSuccess(false);

        $this->client->request('GET', $this->generateUrl('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => $org->getId()), false));
        //$this->assertResponseRedirect($this->client->getContainer()->get('router')->generate('taxonomy.view', array('id'=>'sygefor_core.vocabulary_local')));

        $this->client->request('GET', $this->generateUrl('taxonomy.view', array('id' => 'sygefor_core.vocabulary_local'), false));
        $this->assertResponseSuccess(true);

        $this->createTempUser('view_user_rightall', array('sygefor_core.rights.vocabulary.all', 'sygefor_core.rights.vocabulary.national'), array('organization' => $org) );
        $this->loginAs('view_user_rightall');
        $this->client->request('GET', $this->generateUrl('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => $otherOrg->getId()), false));
        $this->assertResponseSuccess(true);

        $this->client->request('GET', $this->generateUrl('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => 100000000), false));
        $this->assertResponseSuccess(false);

        //national vocabulary is seen using simple interface
        $this->client->request('GET', $this->generateUrl('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_national', 'organization_id' => $otherOrg->getId()), false));
        $this->assertResponseRedirect($this->client->getContainer()->get('router')->generate('taxonomy.view', array('id' => 'sygefor_core.vocabulary_national')));

        $this->client->request('GET', $this->generateUrl('taxonomy.view', array('id' => 'sygefor_core.vocabulary_local'), false));
        $this->assertResponseRedirect($this->client->getContainer()->get('router')->generate('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_national', 'organization_id' => $first0rg->getId())));

        $this->loginAs('user_noright');
        $this->client->request('GET', $this->generateUrl('taxonomy.view_by_org', array('id' => 'sygefor_core.vocabulary_national', 'organization_id' => $otherOrg->getId()), false));
        $this->assertResponseSuccess(false);

        array_push($this->tempEntities, $org);
        array_push($this->tempEntities, $otherOrg);

    }

    public function testAddEditRemoveLocalVocabularyTerm()
    {
        /**
         * @var EntityManager
         */
        $em = $this->getEntityManager();

        $org = new Organization();
        $org->setName('org_temp');
        $org->setCode('org_temp');
        $em->persist($org);

        $otherOrg = new Organization();
        $otherOrg->setName('other_org_temp');
        $otherOrg->setCode('other_org_temp');
        $em->persist($otherOrg);
        $em->flush();

        $termOrg      = $this->createTerm('termOrg', new MyOrganizationVocabulary(), $org);
        $termOtherOrg = $this->createTerm('termOtherOrg', new MyOrganizationVocabulary(), $otherOrg);

        $em->persist($termOrg);
        $em->persist($termOtherOrg);
        $em->flush();

        $this->createTempUser('user_noright', array());
        $this->loginAs('user_noright');
        $this->client->request('GET', $this->generateUrl('taxonomy.addLocal', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => $org->getId()), false));
        $this->assertResponseSuccess(false);
        $this->client->request('POST', $this->generateUrl('taxonomy.remove', array('id' => 'sygefor_core.vocabulary_local', 'term_id' => 5), false));
        $this->assertResponseSuccess(false);

        $this->createTempUser('user_rightown', array('sygefor_core.rights.vocabulary.own'), array('organization' => $org));
        $this->loginAs('user_rightown');
        $crawler = $this->client->request('GET', $this->generateUrl('taxonomy.addLocal', array('organization_id' => $org->getId(), 'id' => 'sygefor_core.vocabulary_local'), false));

        $this->assertResponseSuccess(true);

        $token = $this->extractCsrfToken($crawler, 'vocabulary[_token]');

        $this->postData($this->client->getContainer()->get('router')->generate('taxonomy.addLocal', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => $org->getId())), array('vocabulary' => array('name' => 'foo', '_token' => $token)));

        $this->assertResponseRedirect(true);

        //test term edition

        $crawler = $this->client->request('GET', $this->generateUrl('taxonomy.editLocal', array('organization_id' => $otherOrg->getId(), 'id' => 'sygefor_core.vocabulary_local', 'term_id' => $termOtherOrg->getId()), false));
        $this->assertResponseSuccess(false);

        $crawler = $this->client->request('GET', $this->generateUrl('taxonomy.editLocal', array('organization_id' => $org->getId(), 'id' => 'sygefor_core.vocabulary_local', 'term_id' => $termOrg->getId()), false));
        $this->assertResponseSuccess(true);

        $token = $this->extractCsrfToken($crawler, 'vocabulary[_token]');

        $this->postData($this->client->getContainer()->get('router')->generate('taxonomy.editLocal', array('id' => 'sygefor_core.vocabulary_local', 'organization_id' => $org->getId(), 'term_id' => $termOtherOrg->getId())), array('vocabulary' => array('name' => 'fooFoo', '_token' => $token)));
        $this->assertResponseRedirect(true);

        /** @var ObjectRepository $rep */
        $rep  = $em->getRepository('Sygefor\Bundle\CoreBundle\Tests\Entity\MyOrganizationVocabulary');
        $term = $rep->findOneBy(array('name' => 'foo', 'organization' => $org->getId()));

        $this->postData($this->generateUrl('taxonomy.remove', array('id' => 'sygefor_core.vocabulary_local', 'term_id' => $term->getId())), array());
        $this->assertResponseRedirect(true);
    }

    public function testAddNationalVocabularyTerm()
    {
        //no adequate rights means no success
        $this->createTempUser('user_noright', array());
        $this->loginAs('user_noright');
        $this->client->request('GET', $this->generateUrl('taxonomy.addNational', array('id' => 'sygefor_core.vocabulary_local', array('id' => 'sygefor_core.vocabulary_national')), false));
        $this->assertResponseSuccess(false);
        //$this->client->request('GET', $this->generateUrl('taxonomy.remove', array('id'=>'sygefor_core.vocabulary_local',' term_id'=>5), false));
        //$this->assertResponseSuccess(false);

        $this->createTempUser('user_rightnat', array('sygefor_core.rights.vocabulary.national'));

        $this->loginAs('user_rightnat');

        $crawler = $this->client->request('GET', $this->generateUrl('taxonomy.addNational', array('id' => 'sygefor_core.vocabulary_national'), false));

        $this->assertResponseSuccess(true);

        $token = $this->extractCsrfToken($crawler, 'vocabulary[_token]');

        $this->postData($this->client->getContainer()->get('router')->generate('taxonomy.addNational', array('id' => 'sygefor_core.vocabulary_national')), array('vocabulary' => array('name' => 'foo', '_token' => $token)));

        $this->assertResponseRedirect(true);
    }

    public function createTerm($termName, AbstractTerm $vocabulary, Organization $organization)
    {
        $term = null;
        if ($vocabulary instanceof MyNationalVocabulary) {
            $term = new MyNationalVocabulary();
        } else if ($vocabulary instanceof MyOrganizationVocabulary) {
            $term = new MyOrganizationVocabulary();
            $term->setOrganization($organization);
        }
        $term->setName($termName);

        return $term;
    }
}
