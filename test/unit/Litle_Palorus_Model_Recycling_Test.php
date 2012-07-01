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

class Litle_Palorus_Model_Recycling_Test extends PHPUnit_Framework_TestCase
{

	protected function setUp() {
		Mage::app('default');
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
		$collection = Mage::getModel("palorus/subscription")
			->getCollection();
		foreach($collection as $subscription) {
			$subscription->delete();
		}
		
		$subscriptionHistory = Mage::getModel("palorus/subscriptionHistory")
			->getCollection();
		foreach($subscriptionHistory as $toDelete) {
			$toDelete->delete();
		}
		
		$recyclingCollection = Mage::getModel("palorus/recycling")
			->getCollection();
		foreach($recyclingCollection as $toDelete) {
			$toDelete->delete();
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
		->addAttributeToFilter("name","Litle_Palorus_Model_Recycling_Test");
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
	}
	
	public function testCallFromCron_CallsRecycleOneItemForEachRecyclingItem() {
		$recyleItems = new Varien_Data_Collection();
		$recyleItems->addItem(new Varien_Object()); 
		$recyleItems->addItem(new Varien_Object()); 
		$cut = $this->getMock('Litle_Palorus_Model_Recycling', array('findRecordsToRecycle','recycleOneItem'));
		$cut->expects($this->once())
			->method('findRecordsToRecycle')
			->will($this->returnValue($recyleItems));
		$cut->expects($this->exactly(2))
			->method('recycleOneItem');
		
		$cut->callFromCron();
	}
	
	public function testFindRecordsToRecycle() {
		$waitingOld = Mage::getModel('palorus/recycling')
			->setStatus("waiting")
			->setToRunDate(time()-100000)
			->save();
		$waitingToNew = Mage::getModel('palorus/recycling')
			->setStatus("waiting")
			->setToRunDate(time()+100000)
			->save();
		$notWaiting = Mage::getModel("palorus/recycling")
			->setStatus("other")
			->setToRunDate(time())
			->save();
		
		$cut = new Litle_Palorus_Model_Recycling();
		$ret = $cut->findRecordsToRecycle();
		$this->assertEquals(1, $ret->getSize());
	}
	
	public function testFindSubscriptionItemForRecycling() {
		$subscriptionItem = Mage::getModel('palorus/subscription')
			->setProductId(5)
			->save();
		$notsubscriptionItem = Mage::getModel('palorus/subscription')
			->setProductId(3)
			->save();
		$recyclingItem = Mage::getModel('palorus/recycling')
			->setStatus("waiting")
			->setToRunDate(time()-100000)
			->setSubscriptionId($subscriptionItem->getId())
			->save();
		
		$cut = new Litle_Palorus_Model_Recycling();
		$ret = $cut->findSubscriptionItemForRecycling($recyclingItem);
		$this->assertEquals(5, $ret->getProductId());
	}
	
	public function testShouldRecycleThisSubscription() {
		$cut = new Litle_Palorus_Model_Recycling();
		$subscription = new Varien_Object();
		$subscription->setActive(true);
		$subscription->setNextBillDate(date('Y-m-d', time()+(7*24*60*60))); //Next week
		$this->assertTrue($cut->shouldRecycleThisSubscription($subscription));

		$subscription->setActive(true);
		$subscription->setNextBillDate(date('Y-m-d', time()-(7*24*60*60))); //Last week
		$this->assertFalse($cut->shouldRecycleThisSubscription($subscription));

		$subscription->setActive(false);
		$subscription->setNextBillDate(date('Y-m-d', time()-(7*24*60*60))); //Last week
		$this->assertFalse($cut->shouldRecycleThisSubscription($subscription));
		
		$subscription->setActive(false);
		$subscription->setNextBillDate(date('Y-m-d', time()+(7*24*60*60))); //Next week
		$this->assertFalse($cut->shouldRecycleThisSubscription($subscription));
	}
	
	public function testRecycleOneItem_ShouldRecycle_Success() {
		$subscription = Mage::getModel("palorus/subscription")
			->setProductId(5)
			->setCustomerId(1)
			->setInitialOrderId(3)
			->setNumOfIterationsRan(6)
			->save();
		$recycling = Mage::getModel("palorus/recycling")
			->setSubscriptionId($subscription->getSubscriptionId())
			->save();
		$cut = $this->getMock('Litle_Palorus_Model_Recycling', array('findSubscriptionItemForRecycling','shouldRecycleThisSubscription','createOrder'));
		$cut->expects($this->once())
			->method('findSubscriptionItemForRecycling')
			->with($this->attributeEqualTo("_data",$recycling->getData()))
			->will($this->returnValue($subscription));
		$cut->expects($this->once())
			->method('shouldRecycleThisSubscription')
			->with($this->attributeEqualTo("_data",$subscription->getData()))
			->will($this->returnValue(true));
		$cut->expects($this->once())
			->method('createOrder')
			->with(
				$this->equalTo(5),
				$this->equalTo(1),
				$this->equalTo(3),
				$this->equalTo($subscription->getSubscriptionId())
				)
			->will($this->returnValue(array("success"=>true)));
		
		$cut->recycleOneItem($recycling, 2);
		$history = Mage::getModel("palorus/subscriptionHistory")
			->getCollection()
			->getItemByColumnValue('subscription_id',$subscription->getSubscriptionId());
		$this->assertEquals(true, $recycling->getSuccessful());
		$this->assertEquals('completed', $recycling->getStatus());
		$this->assertEquals($history->getSubscriptionHistoryId(), $recycling->getNextSubscriptionId());
		$this->assertEquals(7, $subscription->getNumOfIterationsRan()); 
		$this->assertEquals(true, $subscription->getRunNextIteration());
		$this->assertEquals($recycling->getSubscriptionId(), $history->getSubscriptionId());
		$this->assertEquals(2, $history->getCronId());
		$this->assertNotNull($history->getRunDate());
	}
	
	public function testRecycleOneItem_ShouldRecycle_Failure() {
		$subscription = Mage::getModel("palorus/subscription")
			->setProductId(5)
			->setCustomerId(1)
			->setInitialOrderId(3)
			->setNumOfIterationsRan(6)
			->save();
		$recycling = Mage::getModel("palorus/recycling")
			->setSubscriptionId($subscription->getSubscriptionId())
			->save();
		$cut = $this->getMock('Litle_Palorus_Model_Recycling', array('findSubscriptionItemForRecycling','shouldRecycleThisSubscription','createOrder'));
		$cut->expects($this->once())
			->method('findSubscriptionItemForRecycling')
			->with($this->attributeEqualTo("_data",$recycling->getData()))
			->will($this->returnValue($subscription));
		$cut->expects($this->once())
			->method('shouldRecycleThisSubscription')
			->with($this->attributeEqualTo("_data",$subscription->getData()))
			->will($this->returnValue(true));
		$cut->expects($this->once())
			->method('createOrder')
			->with(
				$this->equalTo(5),
				$this->equalTo(1),
				$this->equalTo(3),
				$this->equalTo($subscription->getSubscriptionId())
			)
			->will($this->returnValue(array("success"=>false)));
	
		$cut->recycleOneItem($recycling, 2);
		$history = Mage::getModel("palorus/subscriptionHistory")
			->getCollection()
			->getItemByColumnValue('subscription_id',$subscription->getSubscriptionId());
		$this->assertEquals(false, $recycling->getSuccessful());
		$this->assertEquals('failed', $recycling->getStatus());
		$this->assertEquals($history->getSubscriptionHistoryId(), $recycling->getNextSubscriptionId());
		$this->assertEquals(6, $subscription->getNumOfIterationsRan()); 
		$this->assertNull($subscription->getRunNextIteration());
		$this->assertEquals($recycling->getSubscriptionId(), $history->getSubscriptionId());
		$this->assertEquals(2, $history->getCronId());
		$this->assertNotNull($history->getRunDate());
	}
	
	public function testRecycleOneItem_ShouldNotRecycle() {
		$subscription = Mage::getModel("palorus/subscription")
			->setProductId(5)
			->setCustomerId(1)
			->setInitialOrderId(3)
			->setNumOfIterationsRan(6)
			->save();
		$recycling = Mage::getModel("palorus/recycling")
			->setSubscriptionId($subscription->getSubscriptionId())
			->save();
		$cut = $this->getMock('Litle_Palorus_Model_Recycling', array('findSubscriptionItemForRecycling','shouldRecycleThisSubscription','createOrder'));
		$cut->expects($this->once())
			->method('findSubscriptionItemForRecycling')
			->with($this->attributeEqualTo("_data",$recycling->getData()))
			->will($this->returnValue($subscription));
		$cut->expects($this->once())
			->method('shouldRecycleThisSubscription')
			->with($this->attributeEqualTo("_data",$subscription->getData()))
			->will($this->returnValue(false));
		$cut->expects($this->never())
			->method('createOrder');
	
		$cut->recycleOneItem($recycling, 2);
		$history = Mage::getModel("palorus/subscriptionHistory")
			->getCollection()
			->getItemByColumnValue('subscription_id',$subscription->getSubscriptionId());
		$this->assertEquals(false, $recycling->getSuccessful());
		$this->assertNull($recycling->getStatus());
		$this->assertNull($history);
		$this->assertEquals(6, $subscription->getNumOfIterationsRan());
		$this->assertNull($subscription->getRunNextIteration());
	}
	
	
}
