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
		
		$subscriptionCronHistory = Mage::getModel("palorus/subscriptionCronHistory")
			->getCollection();
		foreach($subscriptionCronHistory as $toDelete) {
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
	
}
