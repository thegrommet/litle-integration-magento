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

class Litle_Subscription_Block_Product_View_Test extends PHPUnit_Framework_TestCase
{
	
	protected function setUp() {
		Mage::app('default');
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
	}
	
	protected function tearDown() {
		$collection = Mage::getModel("catalog/product")
			->getCollection()
			->addAttributeToFilter("name","Litle_Subscription_Block_Product_View_Test");
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
	}
	
	public function testGetTierPriceHtml_NotASubscriptionProduct() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0"); 
		
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(false); 
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
		
		$this->assertEquals("Original", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	private function assignLitleSubsItrLen($product, $value) {
		$attribute = Mage::getModel("eav/entity_attribute");
		$attribute->loadByCode(4, 'litle_subs_itr_len');
		$values = array();
		$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
		->setAttributeFilter( $attribute->getId() )
		->setStoreFilter( Mage_Core_Model_App::ADMIN_STORE_ID, false)
		->load();
		
		foreach ($valuesCollection as $item) {
			$values[$item->getValue()] = $item->getId();
		}
		$product->setLitleSubsItrLen($values[$value]);
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_Daily() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(1.234);
		$newproduct->setLitleSubsNumOfItrs(3);
		$this->assignLitleSubsItrLen($newproduct, 'Daily');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
	
		$this->assertEquals("Subscription amount: $1.23 Daily for 3 daysOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_Weekly() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(12.34);
		$newproduct->setLitleSubsNumOfItrs(7);
		$this->assignLitleSubsItrLen($newproduct, 'Weekly');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
	
		$this->assertEquals("Subscription amount: $12.34 Weekly for 7 weeksOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_Biweekly() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(12.345);
		$newproduct->setLitleSubsNumOfItrs(7);
		$this->assignLitleSubsItrLen($newproduct, 'Bi-Weekly');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
	
		$this->assertEquals("Subscription amount: $12.35 Bi-Weekly for 14 weeksOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_SemiMonthly() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(12.34);
		$newproduct->setLitleSubsNumOfItrs(7);
		$this->assignLitleSubsItrLen($newproduct, 'Semi-Monthly');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();

		$this->assertEquals("Subscription amount: $12.34 Semi-Monthly for 3.5 monthsOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_Monthly() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(12.34);
		$newproduct->setLitleSubsNumOfItrs(7);
		$this->assignLitleSubsItrLen($newproduct, 'Monthly');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
	
		$this->assertEquals("Subscription amount: $12.34 Monthly for 7 monthsOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_SemiAnnually() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(12.34);
		$newproduct->setLitleSubsNumOfItrs(7);
		$this->assignLitleSubsItrLen($newproduct, 'Semi-Annually');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
	
		$this->assertEquals("Subscription amount: $12.34 Semi-Annually for 3.5 yearsOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	public function testGetTierPriceHtml_SubscriptionProduct_Annually() {
		$store = Mage::getModel("core/store")->load(Mage_Core_Model_App::ADMIN_STORE_ID);
		$store->setConfig("payment/Subscription/active","0");
	
		$newproduct = new Litle_Subscription_Model_Product();
		$newproduct->setTypeId('simple');
		$newproduct->setAttributeSetId(4);
		$newproduct->setLitleSubscription(true);
		$newproduct->setLitleSubsAmountPerItr(12.34);
		$newproduct->setLitleSubsNumOfItrs(7);
		$this->assignLitleSubsItrLen($newproduct, 'Annually');
		$newproduct->setName("Litle_Subscription_Block_Product_View_Test");
		$newproduct->save();
	
		$this->assertEquals("Subscription amount: $12.34 Annually for 7 yearsOriginal", Litle_Subscription_Block_Product_View::_getTierPriceHtml($newproduct, "Original"));
	}
	
	
}
