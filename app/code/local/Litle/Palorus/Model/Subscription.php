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
class Litle_Palorus_Model_Subscription extends Mage_Core_Model_Abstract
{
	protected $_model = NULL;

	protected $recycleNextRunDate;
	
	protected $recycleAdviceEnd;
	
	protected $shouldRecycleDateBeRead;
	
	public function getRecycleAdviceEnd()
	{
		return $this->recycleAdviceEnd;
	}
	
	public function setRecycleAdviceEnd($in_val)
	{
		$this->recycleAdviceEnd = $in_val;
	}
	
	public function getRecycleNextRunDate()
	{
		return $this->recycleNextRunDate;
	}
	
	public function setRecycleNextRunDate($in_date)
	{
		$this->recycleNextRunDate = strtotime($in_date);
	}
	
	public function getShouldRecycleDateBeRead(){
		return $this->shouldRecycleDateBeRead;
	}
	
	public function setShouldRecycleDateBeRead( $in_updated ){
		$this->shouldRecycleDateBeRead = $in_updated;
	}

	protected function _construct()
	{
		$this->_model = 'palorus/subscription';
		$this->_init($this->_model);
		
		$this->shouldRecycleDateBeRead = false;
	}

	public function callFromCron()
	{
		// Add record for cron run to cron history and calculate the current run cron_id
		$subscriptionCronHistoryModel = Mage::getModel('palorus/subscriptionCronHistory');
		$subscriptionCronHistoryData = array("time_ran" => date( 'Y-m-d H:i:s', time()) );
		$subscriptionCronHistoryModel->setData($subscriptionCronHistoryData)->save();
		$subscriptionCronHistoryCollection = $subscriptionCronHistoryModel->getCollection();
		$subscriptionCronHistoryCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('MAX(cron_history_id) as cron_id');

		$cronId = 0;
		foreach($subscriptionCronHistoryCollection as $subscriptionCronHistoryCollectionItem)
		{
			$cronId = $subscriptionCronHistoryCollectionItem['cron_id'];
		}

		$recyclingModel = Mage::getModel('palorus/recycling');
		$recyclingModel->callFromCron($cronId);
		
		// Get all items from Subscription Suspend where turn_on_date is between now and 2 days ago.
		// 2 days is a buffer in the unlikely scenario that the cron jobs didn't run.
		$subscriptionSuspendCollection = Mage::getModel('palorus/subscriptionSuspend')->getCollection();
		$subscriptionSuspendCollection->addFieldToFilter('turn_on_date', array(
		    														'from' => date('d F Y', ( time()-(2 * 24 * 60 * 60) ) ),
		    														'to' => date('d F Y'),
				    												'date' => true,
		));

		// For each record grabbed from above, turn the run_next_iteration flag in the subscription table to true
		foreach($subscriptionSuspendCollection as $suspendRecord)
		{
			Mage::log("########## Subscription id: " . $suspendRecord['subscription_id'] . " ##########");
			$tempRecord = Mage::getModel('palorus/subscription');
			$tempRecord->load($suspendRecord['subscription_id']);
			$tempRecord->setRunNextIteration(true);
			$tempRecord->save();
		}

		// Get all the subscription items from the subscription table where next_run_date < current time and
		// active flag is true
		$collection = Mage::getModel('palorus/subscription')->getCollection();

		foreach($collection as $collectionItem)
		{
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			$customerId = $collectionItem['customer_id'];
			$productId = $collectionItem['product_id'];
			$subscriptionId = $collectionItem['subscription_id'];
				
				
			$subscriptionSuspendCollectionForSubsId = Mage::getModel('palorus/subscriptionSuspend')->getCollection();
			$subscriptionSuspendCollectionForSubsId->addFieldToFilter("subscription_id", array("in", array($collectionItem['subscription_id'])));
			$subscriptionSuspendCollectionForSubsId->addFieldToFilter("turn_on_date", array("from", date('d F Y', ( time()-(30 * 24 * 60 * 60) ) )));
				
			$subscriptionSuspendCollectionForSubsId->addAttributeToSort('turn_on_date','ASC');
			$turnOnDate = NULL;
			foreach ($subscriptionSuspendCollectionForSubsId as $suspendedItem)
			{
				$turnOnDate = $suspendedItem['turn_on_date'];
			}
				
			//Notify merchant that the previous transcation has not gone through yet and it is time for
			//next charge.
			//Subscription is Active, and run_next_iteration is false (which mean it's in recycling OR suspended)
			//and next_bill_date is in the past, AND subscription is not suspended as per subscriptionSuspend.
			if( $collectionItem['active'] && !$collectionItem['run_next_iteration'] &&
			(strtotime($collectionItem['next_bill_date']) < time()) &&
			( is_null($turnOnDate) || (!is_null($turnOnDate) && (strtotime($turnOnDate) < time())) )
			)
			{
				$recipientEmail = $collectionItem->getConfigData('email_id');
				$description = "This subscription has now become invalid.";
				$this->notifyMerchant($originalOrderId, $customerId, $productId, $subscriptionId, $recipientEmail, $description);
				continue;
			}

			//################################################################
			//############ Implement last ran for each subscription ##########
			//############ so that same subscription does not get run every single cron job..... (see the if statement below!)
			if(		$collectionItem['active'] && $collectionItem['run_next_iteration'] &&
			($collectionItem['num_of_iterations_ran'] < $collectionItem['num_of_iterations'] )&&
			(strtotime($collectionItem['next_bill_date']) < time())
			)
			{
				$subscriptionHistoryModel = Mage::getModel('palorus/subscriptionHistory');
				$subscriptionHistoryItemData = array("subscription_id" => $subscriptionId,
													 "cron_id" => $cronId,
													 "run_date" => time());
				$returnFromCreateOrder = $this->createOrder($productId, $customerId, $originalOrderId, $subscriptionId);
				if( !$returnFromCreateOrder["success"] )
				{
					$collectionItem->setRunNextIteration(false);
				}
				else
				{
					$collectionItem->setNumOfIterationsRan($collectionItem['num_of_iterations_ran'] + 1);
					if($collectionItem[num_of_iterations_ran] == $collectionItem['num_of_iterations'])
					$collectionItem->setActive(false);
				}

				$collectionItem['next_bill_date'] = $this->getNextBillDate($collectionItem['iteration_length'], $collectionItem['next_bill_date']);
				$subscriptionHistoryItemData = array_merge($subscriptionHistoryItemData,$returnFromCreateOrder);
				$subscriptionHistoryModel->setData($subscriptionHistoryItemData)->save();
				$collectionItem->save();
			}
		}
		//sync the subscription id in the recycle with the subscription id in the History
		$recyclingModel->syncSubscriptionIdWithHistory();
		$recyclingModel->syncSubscriptionHistoryId();
		
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
			$recipientEmail = $collectionItem->getConfigData('email_id');
			$description = "No payment information found for this transaction.";
			$this->notifyMerchant($initialOrderId, $customerId, $productId, $subscriptionId, $recipientEmail, $description);
		}
		else{
			try{
				$product1 = Mage::getModel('catalog/product')->load($productId);
				$buyInfo1 = array('qty' => "1");

				$orderModel = Mage::getModel("sales/order");
				$initialOrderObj = $orderModel->load($initialOrderId);
				
				$quote->addProduct($product1, new Varien_Object($buyInfo1));
				$billingAddress = $quote->getBillingAddress()->addData($customer->getPrimaryBillingAddress()->getData());
				$shippingAddress = $quote->getShippingAddress()->addData($customer->getPrimaryShippingAddress()->getData());
				$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
				->setShippingMethod($initialOrderObj->getShippingMethod())
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
				
				if( $this->shouldRecycleDateBeRead )
					$this->saveDataInSubscriptionHistory($initialOrderId, $customerId, $productId, $subscriptionId);
				
				$this->setShouldRecycleDateBeRead( false );
			}
		}
		return array("success" => $success, "order_id" => $orderId);
	}

	public function getNextBillDate($iterLength, $previousNextBillDate)
	{
		$nextDate;
		$date = strtotime($previousNextBillDate);
		// AMIT-TODO : Do not export for testing purposes only.
		//$date = mktime(0, 0, 0, 1, 30, 2012);
			
		$lastDay = date('t',($date));
		$checkDate = date('d',($date));
		switch($iterLength)
		{
			// Add one day to the current day to get the next bill date
			case 'Daily':
				$nextDate = (date("Y-m-d", ($date)) . " +1 day");
				break;

				// Add one week to the current day to get the next bill date
			case 'Weekly':
				$nextDate = (date("Y-m-d", ($date)) . " +1 week");
				break;
					
				// Add two weeks to the current date to get the next bill date
			case 'Bi-Weekly':
				$nextDate = (date("Y-m-d", ($date)) . " +2 weeks");
				break;

				// Add days in a manner where the billing cycle remains the same, and the customer
				// gets billed twice a month.
			case 'Semi-Monthly':
				if($checkDate < "15")
				{
					$nextDate = date("Y-m-d",($date)) . " +15 days";
				}
				else
				{
					if($lastDay === "28")
					{
						$nextDate = (date("Y-m-d", ($date)) . " +13 days");
					}
					else if($lastDay === "29")
					{
						$nextDate = (date("Y-m-d", ($date)) . " +14 days");
					}
					else if($lastDay === "30")
					{
						$nextDate = (date("Y-m-d", ($date)) . " +15 days");
					}
					else
					{
						$nextDate = (date("Y-m-d", ($date)) . " +16 days");
					}
				}
				break;

				// Add one month to the current bill date, if the date is on the 29,30.31 then
				// move it to the first of the following month.
			case 'Monthly':
				$nextDate = (date("Y-m-d", ($date)) . " +1 month");
				if($checkDate === "29" || $checkDate === "30"|| $checkDate === "31")
				{
					$m = date('m', strtotime($nextDate));
					$Y = date('Y', strtotime($nextDate));
					$nextDate = mktime(0, 0, 0, $m , 1 , $Y);
				}
				break;

				// Add 6 months to get the next billing date
			case 'Semi-Annually':
				$nextDate = (date("Y-m-d", ($date)) . " +6 months");
				break;
					
				// Add one year to get the next billing date
			case 'Annually':
				$nextDate = (date("Y-m-d", ($date)) . " +1 year");
				break;
		}
		return $nextDate;
		//return date("Y-m-d"); // TODO : Do not export for testing purposes only.
	}

	
	public function saveDataInSubscriptionHistory($initialOrderId, $customerId, $productId, $subscriptionId, $nextRunDate = "")
	{
		if( $nextRunDate === "" )
		{
			$nextRunDate = $this->getRecycleNextRunDate();
		}
		
		$subscriptionHistoryModel = Mage::getModel('palorus/subscriptionHistory');
		$subsHistoryForLastSubsHistIdCollection = $subscriptionHistoryModel->getCollection();
		$subsHistoryForLastSubsHistIdCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('MAX(subscription_history_id) as subscription_history_id');
			
		$lastSubscriptionHistoryId = 0;
		foreach($subsHistoryForLastSubsHistIdCollection as $subscriptionHistoryCollectionItem)
		{
			$lastSubscriptionHistoryId = $subscriptionHistoryCollectionItem['subscription_history_id'];
		}

		$recyclingModel = Mage::getModel('palorus/recycling');
		$status = "waiting";
		if( $this->recycleAdviceEnd && $nextRunDate == "" ){
			// TODO :  Set subscription as inactive ! 
			$status = "cancelled";
			$recipientEmail = $collectionItem->getConfigData('email_id');
			$description = "All payment recycle patterns have been exhausted and the customer still has not been successfully charged.";
			$this->notifyMerchant($initialOrderId, $customerId, $productId, $subscriptionId, $recipientEmail, $description);
		}
		
		$recyclingItemData = array(
		 							"subscription_id" => $subscriptionId,
		 							"subscription_history_id" => $lastSubscriptionHistoryId + 1,
		 							"successful" => false,
		 							"status" => $status,
		 							"to_run_date" => $nextRunDate		
								);
		$recyclingModel->setData($recyclingItemData)->save();
	}
	
	public function getConfigData($fieldToLookFor, $store = NULL)
	{
		$returnFromThisModel = Mage::getStoreConfig('payment/Subscription/' . $fieldToLookFor);
		if( $returnFromThisModel == NULL )
		$returnFromThisModel = parent::getConfigData($fieldToLookFor, $store);
		Mage::log($returnFromThisModel);
		return $returnFromThisModel;
	}
	
	public function notifyMerchant($originalOrderId, $customerId, $productId, $subscriptionId, $addressToSendTo, $description)
	{
//		$emailTemplate  = Mage::getModel('core/email_template')->loadDefault('custom_email_template1');
				
// 		//Create an array of variables to assign to template
// 		$emailTemplateVariables = array();
// 		$emailTemplateVariables['myvar1'] = $originalOrderId;
// 		$emailTemplateVariables['myvar2'] = $customerId;
// 		$emailTemplateVariables['myvar3'] = $productId;
// 		$emailTemplateVariables['myvar4'] = $subscriptionId;
				
// 		$emailTemplate->setSenderName('Litle & Co.');
// 		$emailTemplate->setSenderEmail('sdksupport@litle.com');
// 		$emailTemplate->setTemplateSubject('Invalid Subscription Status');
// 		//$ret = $collectionItem->getConfigData('email_id');
// 		//Mage::log($ret);
				
// 		$emailTemplate->send($addressToSendTo,'', $emailTemplateVariables);
				
		$notificationModel = Mage::getModel('adminnotification/inbox');
		$notification="Invalid subscription Email";
		$notificationItemData = array(
			 							"severity" => 2,
			 							"date_added" => time(),
			 							"title" => $notification,
			 							"description" => $description,//"the subscription has now become invalid",
			 							//"url" => "www.litle.com",
			 							"is_read" => false,
			 							"is_remove" => false		
									);
		$notificationModel->setData($notificationItemData)->save();
	}
}
