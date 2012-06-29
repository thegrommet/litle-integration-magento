<?php
/**
 * NOTICE OF LICENSE
 *
 * Copyright (c) 2012 Litle & Co.
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
require_once(getenv('MAGENTO_HOME')."/app/Mage.php");

class Litle_Palorus_Model_Subscription_Test extends PHPUnit_Framework_TestCase
{

	protected function setUp() {
		Mage::app('default');
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
		$collection = Mage::getModel("palorus/subscription")
			->getCollection();
		foreach($collection as $subscription) {
			$subscription->delete();
		}
	}

	protected function tearDown() {
		$collection = Mage::getModel("palorus/subscription")
		->getCollection();
		foreach($collection as $subscription) {
			$subscription->delete();
		}
		$collection = Mage::getModel("catalog/product")
		->getCollection()
		->addAttributeToFilter("name","Litle_Palorus_Model_Subscription_Test");
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
	}
	
	public function testCallFromCron() {
		
		$cut = $this->getMock('Litle_Palorus_Model_Subscription', array('addRecordForCronRunToCronHistory','calculateTheCurrentRunCronId','recycle','createOrdersForAllActiveSubscriptions','syncSubscriptionIdBetweenSourceAndTarget'));
		$cut->expects($this->once())->method('addRecordForCronRunToCronHistory');
		$cut->expects($this->once())->method('calculateTheCurrentRunCronId')->will($this->returnValue(4));
		$cut->expects($this->once())->method('recycle')->with($this->equalTo(4));
		$cut->expects($this->once())->method('createOrdersForAllActiveSubscriptions');
		$cut->expects($this->once())->method('syncSubscriptionIdBetweenSourceAndTarget');
		
		$cut->callFromCron();
	}
	
	public function testSyncSubscriptionIdBetweenSourceAndTarget() {
		$mock = $this->getMock('Litle_Palorus_Model_Recycling', array('syncSubscriptionIdWithHistory','syncSubscriptionHistoryId'));
		$mock->expects($this->once())->method('syncSubscriptionIdWithHistory');
		$mock->expects($this->once())->method('syncSubscriptionHistoryId');
		$cut = new Litle_Palorus_Model_Subscription();		
		$cut->syncSubscriptionIdBetweenSourceAndTarget($mock);
	}
	
	public function testCreateOrdersForAllActiveSubscriptions() {
		Mage::getModel("palorus/subscription")
			->setProductId(1)
			->setCustomerId(2)
			->setInitialOrderId(3)
			->setAmount(400)
			->setInitialFees(500)
			->setNumOfIterations(6)
			->setIterationLength('Weekly')
			->setNumOfIterationsRan(1)
			->setRunNextIteration(true)
			->setActive(true)
			->setStartDate('2012-01-02')
			->setNextBillDate('2012-01-03')
			->save();
		
		Mage::getModel("palorus/subscription")
			->setProductId(1)
			->setCustomerId(2)
			->setInitialOrderId(3)
			->setAmount(400)
			->setInitialFees(500)
			->setNumOfIterations(6)
			->setIterationLength('Weekly')
			->setNumOfIterationsRan(1)
			->setRunNextIteration(true)
			->setActive(true)
			->setStartDate('2012-01-02')
			->setNextBillDate('2012-01-03')
			->save();

		Mage::getModel("palorus/subscription")
			->setProductId(1)
			->setCustomerId(2)
			->setInitialOrderId(3)
			->setAmount(400)
			->setInitialFees(500)
			->setNumOfIterations(6)
			->setIterationLength('Weekly')
			->setNumOfIterationsRan(1)
			->setRunNextIteration(true)
			->setActive(false)
			->setStartDate('2012-01-02')
			->setNextBillDate('2012-01-03')
			->save();
		
		$cut = $this->getMock('Litle_Palorus_Model_Subscription', array('createAnOrderForThis'));
		$cut->expects($this->exactly(2))->method('createAnOrderForThis');
		$cut->createOrdersForAllActiveSubscriptions();
		
	}
}
