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
		//->addFieldToFilter('customer_id',$customerId);
		//Mage::log(var_dump($collection));
		
		foreach($collection as $collectionItem)
		{
			Mage::log($collectionItem['subscription_id']);
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			Mage::log("Order id is " . $originalOrderId);
			$orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('order_id', $originalOrderId);
			foreach($orderCollection as $order) {
				Mage::log("Actual order total is " . $order->getBaseGrandTotal());
			}
		}
	}

}