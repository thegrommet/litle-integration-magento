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

class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome_Test extends PHPUnit_Framework_TestCase
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
		->addAttributeToFilter("name","Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome_Test");
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
	}

	public function testGetSubcriptionTable() {
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

		$cut = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome();
		$table = $cut->getSubscriptionTable();
		$this->assertEquals(1, count($table));
		$this->assertEquals(1, $table[0]['product_id']);
		$this->assertEquals(2, $table[0]['customer_id']);
		$this->assertEquals(3, $table[0]['initial_order_id']);
		$this->assertEquals(400, $table[0]['amount']);
		$this->assertEquals(500, $table[0]['initial_fees']);
		$this->assertEquals(6, $table[0]['num_of_iterations']);
		$this->assertEquals('Weekly', $table[0]['iteration_length']);
		$this->assertEquals(1, $table[0]['num_of_iterations_ran']);
		$this->assertEquals(true, $table[0]['active']);
		$this->assertEquals('2012-01-02 00:00:00', $table[0]['start_date']);
		$this->assertEquals('2012-01-03 00:00:00', $table[0]['next_bill_date']);
	}
	
	public function testGetProductName() {
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setName("Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome_Test");
		$newproduct->save();
		
		$subscriptionRow = array('product_id'=>$newproduct->getId());
		
		$cut = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome();
		$name = $cut->getProductName($subscriptionRow);
		$this->assertEquals("Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome_Test", $cut->getProductName($subscriptionRow));
	}
	
	public function testGetRowUrl() {
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setName("Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome_Test");
		$newproduct->save();
		
		$subscriptionRow = array('product_id'=>$newproduct->getId(), 'subscription_id'=>6);
		
		$cut = $this->getMock('Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome', array('getUrl'));
		$cut->expects($this->once())
			->method('getUrl')
			->with($this->equalTo('palorus/adminhtml_myform/subscriptionview/'), $this->equalTo(array('subscription_id' => 6)));
		$url = $cut->getRowUrl($subscriptionRow);
	}
}
