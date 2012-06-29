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
	
	public function testassignData() {
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
}
