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
		$collection = Mage::getModel('palorus/subscription')
		->getCollection();
		
// 		$subscriptionSuspend = Mage::getModel('palorus/subscriptionSuspend')->getCollection()->addFieldToFilter('subscription_id', $originalOrderId);
		
// 		foreach($subscriptionSuspend as $itemTemp)
// 		{
// 			Mage::log("in itemTemp blabla");
// 		}
		
		foreach($collection as $collectionItem)
		{
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			$customerId = $collectionItem['customer_id'];
			$productId = $collectionItem['product_id'];
			$subscriptionId = $collectionItem['subscription_id'];
			$subscriptionTurnOnDate;
			//$orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('order_id', $originalOrderId);
			//foreach($orderCollection as $order) {
				//Mage::log("Actual order total is " . $order->getBaseGrandTotal());
			//}
			$subscriptionSuspendCollection = Mage::getModel('palorus/subscriptionSuspend')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
			foreach($subscriptionSuspendCollection as $subscriptionTurnOnDate){
				// do nothing
			}
			
			if(		$collectionItem['active'] && 
					($collectionItem['num_of_iterations_ran'] < $collectionItem['num_of_iterations'] )
			  )
			{
				if(!$this->createOrder($productId, $customerId, $originalOrderId))
				{
					$collectionItem->setActive(false);
				}
				else
				{
					$collectionItem->setNumOfIterationsRan($collectionItem['num_of_iterations_ran'] + 1);
				}			
				$collectionItem->save();
			}
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
 		if( empty($vaultRecord) )
 		{
 			Mage::log("Payment information could not be retrieved for intial order id: " . $initialOrderId . " and customer id: " . $customerId);
 			return false;
 		}
		$product1 = Mage::getModel('catalog/product')->load($productId);
		$buyInfo1 = array('qty' => "1");
		
		$quote->addProduct($product1, new Varien_Object($buyInfo1));
		$billingAddress = $quote->getBillingAddress()->addData($customer->getPrimaryBillingAddress()->getData());
		$shippingAddress = $quote->getShippingAddress()->addData($customer->getPrimaryShippingAddress()->getData());
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
		->setShippingMethod('flatrate_flatrate') //TODO Make based on original order id
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
		return true;
	}

}