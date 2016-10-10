<?php

namespace Sygefor\Bundle\CoreBundle\Tests\Search;

use Elastica\Client;
use Elastica\Filter\Exists;
use Elastica\Index;
use Elastica\Response;
use Elastica\ResultSet;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchServiceTest.
 */
class SearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    private $index;

    /**
     *
     */
    public function setUp()
    {
        $client      = new Client();
        $this->index = new Index($client, 'test');
    }

    /**
     * @return SearchService
     */
    public function getSearch()
    {
        return new SearchService($this->index);
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::setFields
     */
    public function testAddFields()
    {
        $search = $this->getSearch();
        $search->setFields(array('foo', 'bar'));
        $this->assertCount(2, $search->getFields());
        $this->assertCount(2, $search->getQuery()->getParam('fields'));
        $this->assertArrayEqualsJsonString('["foo","bar"]', $search->getQuery()->getParam('fields'));
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::addFacet
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::addTermsFacet
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::removeFacet
     */
    public function testAddTermsFacet()
    {
        $search = $this->getSearch();
        $search->addTermsFacet('foo', 'bar');
        $this->assertCount(1, $search->getFacets());
        $this->assertArrayEqualsJsonString('{"foo":{"terms":{"field":"bar"}}}', $search->getQuery()->getParam('facets'));
        $search->removeFacet('foo');
        $this->assertCount(0, $search->getQuery()->getParam('facets'));
        $this->assertCount(0, $search->getFacets());
        $this->assertArrayNotHasKey('facets', $search->getQuery()->toArray());
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::addFilter
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::addTermFilter
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::setFilters
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::removeFilter
     */
    public function testAddFilter()
    {
        $search = $this->getSearch();
        $search->addTermFilter('foo', 'bar');
        $this->assertCount(1, $search->getFilters());
        $this->assertArrayEqualsJsonString('{"term":{"foo":"bar"}}', $search->getQuery()->getParam('filter'));

        $search->addFilter('foo2', new Exists('foo'), true);
        $this->assertCount(2, $search->getFilters());
        $this->assertArrayEqualsJsonString('{"bool":{"must":[{"term":{"foo":"bar"}},{"exists":{"field":"foo"}}]}}', $search->getQuery()->getParam('filter'));

        $search->removeFilter('foo');
        $this->assertCount(1, $search->getFilters());
        $this->assertArrayEqualsJsonString('{"exists":{"field":"foo"}}', $search->getQuery()->getParam('filter'));

        $search->removeFilter('foo2');
        $this->assertCount(0, $search->getFilters());
        $this->assertEmpty($search->getQuery()->getParam('filter'));
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::addSort
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::setSorts
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::removeSort
     */
    public function testAddSort()
    {
        $search = $this->getSearch();
        $search->addSort('foo', 'asc');
        $this->assertCount(1, $search->getSorts());
        $this->assertArrayEqualsJsonString('[{"foo":"asc"}]', $search->getQuery()->getParam('sort'));

        $search->addSort('bar', 'desc', array('mode' => 'avg'));
        $this->assertCount(2, $search->getSorts());
        $this->assertArrayEqualsJsonString('[{"foo":"asc"},{"bar":{"order":"desc","mode":"avg"}}]', $search->getQuery()->getParam('sort'));

        $search->removeSort('foo');
        $this->assertCount(1, $search->getSorts());
        $this->assertArrayEqualsJsonString('[{"bar":{"order":"desc","mode":"avg"}}]', $search->getQuery()->getParam('sort'));

        $search->removeSort('bar');
        $this->assertCount(0, $search->getSorts());
        $this->assertEmpty($search->getQuery()->getParam('sort'));
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchServive::setSize
     */
    public function testSetSize()
    {
        $search = $this->getSearch();
        $this->assertSame(10, $search->getSize());
        $search->setSize(30);
        $this->assertSame(30, $search->getSize());
        $search->setSize(150);
        $this->assertSame(150, $search->getSize());
        $search->setSize(null);
        $this->assertSame(10, $search->getSize());
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchServive::setPage
     */
    public function testSetPage()
    {
        $search = $this->getSearch();
        $this->assertSame(1, $search->getPage());

        $search->setPage(3);
        $this->assertSame(3, $search->getPage());
        $this->assertSame(20, $search->getQuery()->getParam('from'));

        $search->setSize(20);
        $this->assertSame(3, $search->getPage());
        $this->assertSame(40, $search->getQuery()->getParam('from'));
    }

    /**
     * @dataProvider handleRequestDataProvider
     */
    public function testHandleRequest($method, $data, $expected)
    {
        $search  = $this->getSearch();
        $request = Request::create('http://localhost', $method, $data);
        $search->handleRequest($request);
        $this->assertArrayEqualsJsonString($expected, $search->getQuery()->toArray());
    }

    /**
     * @return array
     */
    public function handleRequestDataProvider()
    {
        return array(
            array('GET', array(), '{"query":{"match_all":{}}}'),
            array('GET', array('filters' => array('term:foo' => 'bar')), '{"filter":{"term":{"foo":"bar"}},"query":{"match_all":{}}}'),
            array('GET', array('filters' => array('term:foo' => 'bar', 'foo2' => 'bar2')), '{"filter":{"bool":{"must":[{"term":{"foo":"bar"}},{"term":{"foo2":"bar2"}}]}},"query":{"match_all":{}}}'),
            array('GET', array('sorts' => array('title' => 'asc')), '{"sort":[{"title":"asc"}],"query":{"match_all":{}}}'),
            array('GET', array('size' => 20, 'page' => 5), '{"query":{"match_all":{}},"size":20,"from":80}'),
            array('GET', array('fields' => array('foo', 'bar')), '{"fields":["foo","bar"],"query":{"match_all":{}}}'),
        );
    }

    /**
     * @covers_ Sygefor\Bundle\CoreBundle\Search\SearchService::search
     */
    public function testSearch()
    {
        // setup
        $client   = new Client();
        $index    = $this->getMock('Elastica\Index', array('createSearch'), array($client, 'test'));
        $essearch = $this->getMock('Elastica\Search', array('search'), array($client));

        $response = array(
            'hits' => array(
                'total'    => 2,
                'pageSize' => 10,
                'hits'     => array(
                    array('_id' => 1, '_type' => 'type1', '_source' => array('name' => 'foo')),
                    array('_id' => 2, '_type' => 'type2', '_source' => array('name' => 'bar')),
                ),
            ),
            'facets' => array(
                'facet1' => array('_type' => 'terms'),
            ),
        );
        $search    = new SearchService($index);
        $resultSet = new ResultSet(new Response(json_encode($response)), $search->getQuery());

        $index->expects($this->once())->method('createSearch')->will($this->returnValue($essearch));
        $essearch->expects($this->once())->method('search')->will($this->returnValue($resultSet));

        $expected = array(
            'total'    => 2,
            'pageSize' => 10,
            'items'    => array(
                array(
                    'id'    => 1,
                    '_type' => 'type1',
                    'name'  => 'foo',
                ),
                array(
                    'id'    => 2,
                    '_type' => 'type2',
                    'name'  => 'bar',
                ),
            ),
            'facets' => array(
                'facet1' => array('_type' => 'terms'),
            ),
        );

        // test
        $response = $search->search();
        $this->assertSame($expected, $response);
    }

    /**
     * @param $expectedJson
     * @param $actualArray
     * @param string $message
     *
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    protected function assertArrayEqualsJsonString($expectedJson, $actualArray, $message = '') {
        try {
            $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($actualArray), $message);
        } catch(\PHPUnit_Framework_ExpectationFailedException $e) {
            $message = $e->getMessage() . '. Actual JSON : ' . json_encode($actualArray);
            throw new \PHPUnit_Framework_ExpectationFailedException($message);
        }
    }
}
