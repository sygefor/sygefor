<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/04/14
 * Time: 15:11
 */
namespace Sygefor\Bundle\ListBundle\Tests\Controller;

use Sygefor\Bundle\CoreBundle\Test\WebTestCase;

class BatchOperationControllerTest extends WebTestCase{

    /**
     * @runInSeparateProcess
     */
    public function testExecute()
    {
        $this->createTempUser('admin');
        $this->loginAs('admin');
        $this->getRequest($this->generateUrl('sygefor_list.batch_operation.execute',array("id"=>"sygefor_list.batch.csv.user ")),array('ids'=>array(2,4,6),'options'=>array("delimiter"=>';')));
    }
}
