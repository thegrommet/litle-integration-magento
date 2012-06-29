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

class Litle_Subscription_Model_PaymentLogic_Test extends PHPUnit_Framework_TestCase
{
	
	protected function setUp() {
		Mage::app('default');
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
	}
	
	protected function tearDown() {
		$collection = Mage::getModel("catalog/product")
			->getCollection()
			->addAttributeToFilter("name","Litle_Subscription_Model_PaymentLogic_Test");
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
	}
	
	public function testGetConfigData() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
		
		$cut = new Litle_Subscription_Model_PaymentLogic();
		$this->assertEquals("0",$cut->getConfigData("active"));
	}
	
	public function testGetConfigData_2() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","1");
		
		$cut = new Litle_Subscription_Model_PaymentLogic();
		$this->assertEquals("1",$cut->getConfigData("active"));
	}
	
	public function testAssignData() {
		$cut = new Litle_Subscription_Model_PaymentLogic();
		$data = new Varien_Object();
		$data->setLitletoken("a");
		$data->setLitletokentype("b");
		$data->setLitletokenexpdate("c");
		$data->setLitleissubscription("d");
		$info = new Mage_Payment_Model_Info();
		$cut['info_instance'] = $info;
		$info = $cut->getInfoInstance();
		$cut->assignData($data);
		$this->assertEquals("a",$info->getAdditionalInformation("litletoken"));
		$this->assertEquals("b",$info->getAdditionalInformation("litletokentype"));
		$this->assertEquals("c",$info->getAdditionalInformation("litletokenexpdate"));
		$this->assertEquals("d",$info->getAdditionalInformation("litleissubscription"));
	}
	
	public function testCreditCardOrPaypageOrToken_TokenSet() {
		$cut = new Litle_Subscription_Model_PaymentLogic();
		$data = new Varien_Object();
		$data->setLitletoken("1234567890123456");
		$data->setLitletokentype("VI");
		$data->setLitletokenexpdate("0415");
		$info = new Mage_Payment_Model_Info();
		$cut['info_instance'] = $info;
		$info = $cut->getInfoInstance();
		$cut->assignData($data);
		
		$payment = new Mage_Sales_Model_Order_Payment();
		$paymentHash = $cut->creditCardOrPaypageOrToken($payment);
		$this->assertEquals("1234567890123456",$paymentHash['token']['litleToken']);
		$this->assertEquals("VI",$paymentHash['token']['type']);
		$this->assertEquals("0415",$paymentHash['token']['expDate']);
		$this->assertEquals("3456",$payment->getCcLast4());
		$this->assertEquals("VI",$payment->getCcType());
	}
	
	public function testCreditCardOrPaypageOrToken_TokenNotSet() {
		$cut = new Litle_Subscription_Model_PaymentLogic();
		$data = new Varien_Object();
		$info = new Mage_Payment_Model_Info();
		$cut['info_instance'] = $info;
		$info = $cut->getInfoInstance();
		$cut->assignData($data);
		
		$payment = new Mage_Sales_Model_Order_Payment();
		$paymentHash = $cut->creditCardOrPaypageOrToken($payment);
		$this->assertNull($paymentHash['token']['litleToken']);
		$this->assertNull($paymentHash['token']['type']);
		$this->assertNull($paymentHash['token']['expDate']);
		$this->assertNull($payment->getCcLast4());
		$this->assertNull($payment->getCcType());
	}
	
	public function testCleanseProductList_InactiveSubscription() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0"); #Inactive
		
		$newproduct1 = new Litle_Subscription_Model_Product();
		$newproduct1->setTypeId('simple');
		$newproduct1->setAttributeSetId(4);
		$newproduct1->setLitleSubscription(true); #Subscription Product
		$newproduct1->setName("Litle_Subscription_Model_PaymentLogic_Test");
		$newproduct1->save();
		
		$newproduct2 = new Litle_Subscription_Model_Product();
		$newproduct2->setTypeId('simple');
		$newproduct2->setAttributeSetId(4);
		$newproduct2->setLitleSubscription(false); #Not a Subscription Product
		$newproduct2->setName("Litle_Subscription_Model_PaymentLogic_Test");
		$newproduct2->save();

		$cut = new Litle_Subscription_Model_PaymentLogic();
		$observer = new Varien_Object();
		$event = new Varien_Object();
		$collection = new Varien_Data_Collection();
		$collection->addItem($newproduct1);
		$collection->addItem($newproduct2);
		$this->assertEquals(2, count($collection->getAllIds()));
		$event->setCollection($collection);
		$observer->setEvent($event);
		$cut->cleanseProductList($observer);
		$this->assertEquals(1, count($collection->getAllIds()));
	}
	
	public function testCleanseProductList_ActiveSubscription() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","1"); #Active
	
		$newproduct1 = new Litle_Subscription_Model_Product();
		$newproduct1->setTypeId('simple');
		$newproduct1->setAttributeSetId(4);
		$newproduct1->setLitleSubscription(true); #Subscription Product
		$newproduct1->setName("Litle_Subscription_Model_PaymentLogic_Test");
		$newproduct1->save();
	
		$newproduct2 = new Litle_Subscription_Model_Product();
		$newproduct2->setTypeId('simple');
		$newproduct2->setAttributeSetId(4);
		$newproduct2->setLitleSubscription(false); #Not a Subscription Product
		$newproduct2->setName("Litle_Subscription_Model_PaymentLogic_Test");
		$newproduct2->save();
	
		$cut = new Litle_Subscription_Model_PaymentLogic();
		$observer = new Varien_Object();
		$event = new Varien_Object();
		$collection = new Varien_Data_Collection();
		$collection->addItem($newproduct1);
		$collection->addItem($newproduct2);
		$this->assertEquals(2, count($collection->getAllIds()));
		$event->setCollection($collection);
		$observer->setEvent($event);
		$cut->cleanseProductList($observer);
		$this->assertEquals(2, count($collection->getAllIds()));
	}
	
}
