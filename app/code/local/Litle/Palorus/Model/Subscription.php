<?php

class Litle_Palorus_Model_Subscription extends Mage_Core_Model_Abstract
{
	protected $_model = NULL;

	protected function _construct()
	{
		$this->_model = 'palorus/subscription';
		$this->_init($this->_model);
	}
	
	public function callFromCron()
	{
		Mage::log("callFromCron ");
		$collection = Mage::getModel('palorus/subscription')
		->getCollection();
		
		foreach($collection as $collectionItem)
		{
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			$customerId = $collectionItem['customer_id'];
			$productId = $collectionItem['product_id'];
			//$orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('order_id', $originalOrderId);
			//foreach($orderCollection as $order) {
				//Mage::log("Actual order total is " . $order->getBaseGrandTotal());
			//}
			$this->createOrder($productId, $customerId, $originalOrderId);
			Mage::log("did something from subscription.");
		}
	}
	
	public function createOrder($productId, $customerId, $initialOrderId){
		$store = Mage::app()->getStore('default');
		
		$customer = Mage::getModel('customer/customer');
		$customer->setStore($store);
		$customer->load($customerId);
		
		$quote = Mage::getModel('sales/quote');
		$quote->setStore($store);
		$quote->assignCustomer($customer);
		
		$vault = Mage::getModel('palorus/vault');
		$vaultCollection = $vault->getCollection()->addFieldToFilter('order_id',$initialOrderId);
		$vaultRecord = "";
		foreach($vaultCollection as $vaultRecord){
			// do nothing
		}
		
		$product1 = Mage::getModel('catalog/product')->load($productId); /* HTC Touch Diamond */
		$buyInfo1 = array('qty' => "1");
		
		$quote->addProduct($product1, new Varien_Object($buyInfo1));
		$billingAddress = $quote->getBillingAddress()->addData($customer->getPrimaryBillingAddress()->getData());
		$shippingAddress = $quote->getShippingAddress()->addData($customer->getPrimaryShippingAddress()->getData());
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
		->setShippingMethod('flatrate_flatrate')
		->setPaymentMethod('litlesubscription');
		$quote->getPayment()->importData(array(
														'method' => 'litlesubscription', 
														'litletoken' => $vaultRecord['token'],
														'litletokentype' => $vaultRecord['type'],
														'litletokenexpdate' => $vaultRecord['expdate']
												)
										);
		
		$quote->collectTotals()->save();
		$service = Mage::getModel('sales/service_quote', $quote);
		$service->submitAll();
	}

}