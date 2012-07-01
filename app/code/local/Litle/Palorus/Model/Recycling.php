<?php
/**
* Litle Palorus Module
*
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
*
* @category   Litle
* @package    Litle_Palorus
* @copyright  Copyright (c) 2012 Litle & Co.
* @license    http://www.opensource.org/licenses/mit-license.php
* @author     Litle & Co <sdksupport@litle.com> www.litle.com/developers
*/
class Litle_Palorus_Model_Recycling extends Mage_Core_Model_Abstract
{
	protected $_model = NULL;

	protected function _construct()
	{
		$this->_model = 'palorus/recycling';
		$this->_init($this->_model);
	}
	
	public function callFromCron($cronId)
	{
		$recyclingCollection = $this->findRecordsToRecycle();

		foreach($recyclingCollection as $recyclingCollectionItem)
		{
			$this->recycleOneItem($recyclingCollectionItem);
		}
	}
	
	public function findRecordsToRecycle() {
		$recyclingCollection = Mage::getModel('palorus/recycling')->getCollection();
		// Select records where date to run is less than the current date and status is currently waiting.
		$recyclingCollection->addFieldToFilter("status", array("in", array('waiting')));
		$recyclingCollection->addFieldToFilter('to_run_date', array(
						    											'from' => date('d F Y', ( time()-(60 * 24 * 60 * 60) ) ),
						    											'to' => date('d F Y'),
								    									'date' => true,
		));
		return $recyclingCollection;
	}

	public function findSubscriptionItemForRecycling($recyclingItem) {
		$subscriptionCollection = Mage::getModel('palorus/subscription')->getCollection();
		$subscriptionCollection->addFieldToFilter("subscription_id",array("in",array($recyclingItem['subscription_id'])));
			
		foreach($subscriptionCollection as $subscriptionItem)
		{
		}
		return $subscriptionItem;
	}
	
	public function shouldRecycleThisSubscription($subscription) {
		return $subscription['active'] && (time() < strtotime($subscription['next_bill_date']));
	}
	
	public function recycleOneItem($recyclingCollectionItem) {
		
		Mage::log("inside recycling collection");
		$subscriptionItem = $this->findSubscriptionItemForRecycling($recyclingCollectionItem);
		// if subscription is still active, and current time < "next_bill_date" time in subscription table ...
		// (we do not want to run re-cycling if "next_bill_date" time was in the past -- we want to deactivate the subscription
		// and notify the admins via email etc.)
		if(shouldRecycleThisSubscription($subscriptionItem))
		{
			$subscriptionHistoryModel = Mage::getModel('palorus/subscriptionHistory');
			$subscriptionHistoryItemData = array(	"subscription_id" => $recyclingCollectionItem['subscription_id'],
													"cron_id" => $cronId,
													"run_date" => time());
		
			$returnFromCreateOrder = $this->createOrder($subscriptionItem['product_id'], $subscriptionItem['customer_id'], $subscriptionItem['initial_order_id'], $recyclingCollectionItem['subscription_id']);
			if( !$returnFromCreateOrder["success"] )
			{
				Mage::log("the transaction failed");
				$recyclingCollectionItem->setSuccessful(false);
				$recyclingCollectionItem->setStatus('failed');			
			}
			else
			{
				Mage::log("the transaction passed");
				$subscriptionItem->setNumOfIterationsRan($subscriptionItem['num_of_iterations_ran'] + 1);
				$subscriptionItem->setRunNextIteration(true);
				$subscriptionItem->save();
				$recyclingCollectionItem->setSuccessful(true);
				$recyclingCollectionItem->setStatus('completed');
			}
			$subscriptionHistoryItemData = array_merge($subscriptionHistoryItemData, $returnFromCreateOrder);
			$subscriptionHistoryModel->setData($subscriptionHistoryItemData)->save();
			$nextSubscriptionHistoryModel = Mage::getModel('palorus/subscriptionHistory');
			$nextSubscriptionIdCollection = $nextSubscriptionHistoryModel->getCollection();
			$nextSubscriptionIdCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('MAX(subscription_history_id) as subscription_history_id');
			$nextSubscriptionId = 0;
			foreach ($nextSubscriptionIdCollection as $nextSubscriptionIdCollectionItem)
			{
				$nextSubscriptionId = $nextSubscriptionIdCollectionItem['subscription_history_id'];
			}
			Mage::log("the next subscription id is " . $nextSubscriptionId);
			$recyclingCollectionItem->setNextSubscriptionId($nextSubscriptionId);
			$recyclingCollectionItem->save();
		}
		else
		{
			//$subscriptionItem->setActive(false);
			$subscriptionItem->save();
		}
		
	}

	public function createOrder($productId, $customerId, $initialOrderId, $subscriptionId){
		$store = Mage::app()->getStore('default');
		$success = false;
		$orderId = 0;
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
			// do nothing -- DO NOT DELETE; this is a hack and we need it!
		}
		if( empty($vaultRecord) )
		{
			Mage::log("Payment information could not be retrieved for intial order id: " . $initialOrderId . " and customer id: " . $customerId);
		}
		else{
			try{
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
	 				 											'litletokenexpdate' => $vaultRecord['expdate'],
	 				 											'ordersource' => 'recurring',
	 				 											'subscriptionid' => $subscriptionId
				)
				);
					
				$quote->collectTotals()->save();
				$service = Mage::getModel('sales/service_quote', $quote);
				$service->submitAll();
				$order = $service->getOrder();
				$orderId = $order->getId();
				$success = true;
			} catch (Exception $e)
			{
				$success = false;
				
				$subscriptionSingleton = Mage::getSingleton('palorus/subscription');
				if( $subscriptionSingleton->getShouldRecycleDateBeRead() )
					$subscriptionSingleton->saveDataInSubscriptionHistory($initialOrderId, $customerId, $productId, $subscriptionId);
				
				$subscriptionSingleton->setShouldRecycleDateBeRead( false );
			}
		}
		return array("success" => $success, "order_id" => $orderId);
	}
	
	public function syncSubscriptionIdWithHistory()
	{
		$recyclingCollection = Mage::getModel('palorus/recycling')->getCollection();
		// Select records where date to run is less than the current date and status is currently waiting.
// 		$recyclingCollection->addFieldToFilter('to_run_date', array(
// 						    														'from' => date('d F Y', ( time()-(7 * 24 * 60 * 60) ) ),
// 						    														'to' => date('d F Y'),
// 								    												'date' => true,
// 		));
		//select all the records in recycling table where time is greater than current time   
		$recyclingCollection->addFieldToFilter('to_run_date', array(
								    														'from' => date('d F Y'),
										    												'date' => true,
		));
		foreach($recyclingCollection as $recyclingCollectionItem)
		{
			$id = $this->syncRecycleWithSubscription($recyclingCollectionItem['subscription_id']);
			$recyclingCollectionItem->setSubscriptionHistoryId($id);
			$recyclingCollectionItem->save();
		}
		
	}
	
	public function syncSubscriptionHistoryId()
	{
		$recyclingCollection = Mage::getModel('palorus/recycling')->getCollection();
		$recyclingCollection->addFieldToFilter('to_run_date', array(
		    														'from' => date('d F Y'),
					   												'date' => true,
									     		));
		foreach($recyclingCollection as $recyclingCollectionItem)
		{
			$id = $this->syncRecycleWithSubscription($recyclingCollectionItem['subscription_id']);
			if($recyclingCollectionItem['next_subscription_id'] === NULL)
			{
			// There is no other subscription history record to point to ! happens at the very beginning	
			}
			else
			$recyclingCollectionItem->setNextSubscriptionId($id);
			
			$recyclingCollectionItem->save();
		}
	
	}
	
	
	
	
	public function syncRecycleWithSubscription($subscriptionHistoryId)
	{
		$subscriptionHistoryCollection = Mage::getModel('palorus/subscriptionHistory')->getCollection();
		$subscriptionHistoryCollection->addFieldToFilter('subscription_id',array('in',($subscriptionHistoryId)));
		$subscriptionHistoryItem = "";
		foreach( $subscriptionHistoryCollection as $subscriptionHistoryItem)
		{
		
		}
		// check if both the subscription id's match if yes return the history id !
		if($subscriptionHistoryItem['subscription_id'] == $subscriptionHistoryId)
		{
			return $subscriptionHistoryItem['subscription_history_id'];
		}
		// Boom, something went wrong, fix it return the correct  history ID 
		else
		{
			$subsHistoryForSubsHistIdCollection = Mage::getModel('palorus/subscriptionHistory')->getCollection();
			$subsHistoryForSubsHistIdCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('MAX(subscription_history_id) as subscription_history_id');
			$subsHistoryForSubsHistIdCollection->addFieldToFilter('subscription_id',array("in" , $subscriptionHistoryId));
			foreach($subsHistoryForSubsHistIdCollection as $subsHistoryForSubsHistIdCollectionItem)
			{
					
			}
		}
		return $subsHistoryForSubsHistIdCollectionItem['subscription_history_id'];
	}
}

