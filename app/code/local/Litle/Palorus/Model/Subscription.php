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
	
	protected function _construct()
	{
		$this->_model = 'palorus/subscription';
		$this->_init($this->_model);
	}
	
	public function callFromCron()
	{
		// Add record for cron run to cron history and calculate the current run cron_id
		Mage::log("in call from cron");
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
		Mage::log("cronId to use: " . $cronId );
		
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
// 		$collection->addFieldToFilter('next_bill_date', array('to' => date('d F Y'),
// 				    												'date' => true,
// 									));
		
// 		$collection->addFieldToFilter(array(
// 											array(
// 												    'attribute' => 'next_bill_date',
// 												    'to'        => date('d F Y'),
// 											),
// 											array(
// 												    'attribute' => 'active',
// 												    'in'      => array(true),
// 											),
// 										));
		
		//$collection->addFieldToFilter("next_bill_date", array('to' => date('d F Y')));
		//$collection->addFieldToFilter("active", array('in' => array(true)));
		
		foreach($collection as $collectionItem)
		{
			Mage::log("going over subscription table");
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			$customerId = $collectionItem['customer_id'];
			$productId = $collectionItem['product_id'];
			$subscriptionId = $collectionItem['subscription_id'];
			
			
			Mage::log("1");
			$subscriptionSuspendCollectionForSubsId = Mage::getModel('palorus/subscriptionSuspend')->getCollection();
// 			$subscriptionSuspendCollectionForSubsId->addFieldToFilter(array(
// 																		    array(
// 																		        'attribute' => 'subscription_id',
// 																		        'in'        => array($collectionItem['subscription_id']),
// 																		        ),
// 																		    array(
// 																		        'attribute' => 'turn_on_date',
// 																		        'from'      => date('d F Y', ( time()-(365 * 24 * 60 * 60) ) ),
// 																		        ),
// 																		));
			
			$subscriptionSuspendCollectionForSubsId->addFieldToFilter("subscription_id", array("in", array($collectionItem['subscription_id'])));
			$subscriptionSuspendCollectionForSubsId->addFieldToFilter("turn_on_date", array("from", date('d F Y', ( time()-(365 * 24 * 60 * 60) ) )));
			
			Mage::log("2");
			$subscriptionSuspendCollectionForSubsId->addAttributeToSort('turn_on_date','ASC');
			$turnOnDate;
			foreach ($subscriptionSuspendCollectionForSubsId as $suspendedItem)
			{
				$turnOnDate = $suspendedItem['turn_on_date'];
			
			}
			
			Mage::log("3");
			//Notify merchant that the previous transcation has not gone through yet and it is time for
			//next charge.
			//Subscription is Active, and run_next_iteration is false (which mean it's in recycling OR suspended)
			//and next_bill_date is in the past, AND subscription is not suspended as per subscriptionSuspend.
			if( $collectionItem['active'] && !$collectionItem['run_next_iteration'] &&
				(strtotime($collectionItem['next_bill_date']) < time()) &&
				( is_null($turnOnDate) || (!is_null($turnOnDate) && (strtotime($turnOnDate) < time())) )
				)
				{
			 		// TODO :  Notify the merchant about this case ! 
			 		continue;
				}
				
				Mage::log("4");
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
				$returnFromCreateOrder = $this->createOrder($productId, $customerId, $originalOrderId);
				Mage::log("5");
				if( !$returnFromCreateOrder["success"] )
				{
					$collectionItem->setRunNextIteration(false);
					
					// Add data to recycling table
					$recyclingCollection = Mage::getModel('palorus/recycling')->getCollection();
					$subscriptionHistoryItemData = array(	"subscription_id" => $subscriptionId,
					 														"cron_id" => $cronId,
					 														"run_date" => time());
					$recyclingItemData = array(
					 							"subscription_id" => $subscriptionId,
					 							"subscription_history_id" => something,
					 							"successful" => false,
					 							"status" => "waiting",
					 							"to_run_date"				
												);
					$subscriptionHistoryItemData = array_merge($subscriptionHistoryItemData,$returnFromCreateOrder);
					$subscriptionHistoryModel->setData($subscriptionHistoryItemData)->save();
					Mage::log("6");
				}
				else
				{
					$collectionItem->setNumOfIterationsRan($collectionItem['num_of_iterations_ran'] + 1);
					Mage::log("7");
				}	
				
				$collectionItem['next_bill_date'] = $this->getNextBillDate($collectionItem['iteration_length'], $collectionItem['next_bill_date']);
				$subscriptionHistoryItemData = array_merge($subscriptionHistoryItemData,$returnFromCreateOrder);
				$subscriptionHistoryModel->setData($subscriptionHistoryItemData)->save();			
				$collectionItem->save();
				Mage::log("8");
			}
		}
	}
	
	public function createOrder($productId, $customerId, $initialOrderId){
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
 				Mage::log("creating order");
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
 				$order = $service->getOrder();
 				$orderId = $order->getId();
 				$success = true;
 			} catch (Exception $e)
 			{
 				$success = false;
 				//Mage::log("Exception is: " . $e);
 			}
 		}
		return array("success" => $success, "order_id" => $orderId);
	}

	public function getNextBillDate($iterLength, $previousNextBillDate)
	{
			Mage::log("inside next bill date");
			$nextDate; 
// 			$dateobj = new DateTime();
// 			$date = $dateobj->getTimestamp();// current date
			$date = strtotime($previousNextBillDate); 
			// AMIT-TODO : Do not export for testing purposes only. 
			//$date = mktime(0, 0, 0, 1, 30, 2012);
			
			
			Mage::log("the previous billd date is" .$previousNextBillDate);
			Mage::log("the date is " . (date("Y-m-d",$date)));
			$lastDay = date('t',($date));
			$checkDate = date('d',($date));
			Mage::log("the last day of the month is " . $lastDay);
			Mage::log("the current next date is " . $checkDate);
			switch($iterLength)
			{
				case 'Daily':
			    $nextDate = (date("Y-m-d", ($date)) . " +1 day");
			    Mage::log("the next date (Daily) " . date("Y-m-d", strtotime($nextDate)));
				break;	
				
				case 'Weekly':
				$nextDate = (date("Y-m-d", ($date)) . " +1 week");
				Mage::log("the next date (weekly) " . date("Y-m-d", strtotime($nextDate)));
				break;
			
				case 'Bi-Weekly':
				$nextDate = (date("Y-m-d", ($date)) . " +2 weeks");
				Mage::log("the next date (Bi-weekly) " . date("Y-m-d", strtotime($nextDate)));
				break;
			
				// Needs optimization !
				
				case 'Semi-Monthly': 
				if($checkDate < "15")
				{
					Mage::log("in semi-monthly -- less than 15");
					$nextDate = date("Y-m-d",($date)) . " +15 days";
				}
				else
				{
					if($lastDay === "28")
					{
						Mage::log("in semi-monthly -- last day 28");
						$nextDate = (date("Y-m-d", ($date)) . " +13 days");
					}
					else if($lastDay === "29")
					{
						Mage::log("in semi-monthly -- last day 29");
						$nextDate = (date("Y-m-d", ($date)) . " +14 days");
					}
					else if($lastDay === "30")
					{
						Mage::log("in semi-monthly -- last day 30");
						$nextDate = (date("Y-m-d", ($date)) . " +15 days");
					}
					else
					{
						Mage::log("in semi-monthly -- else");
						$nextDate = (date("Y-m-d", ($date)) . " +16 days");
					}
				}
				Mage::log("the next date (semi-monthly) " . date("Y-m-d", strtotime($nextDate)));
				break;
			
				
				//// ###### Please confirm this !
				
				case 'Monthly':
				$nextDate = (date("Y-m-d", ($date)) . " +1 month");
				Mage::log("in monthly -- ");
				if($checkDate === "29" || $checkDate === "30"|| $checkDate === "31")
					{					
						$m = date('m', strtotime($nextDate));
						$Y = date('Y', strtotime($nextDate));
						$nextDate = mktime(0, 0, 0, $m , 1 , $Y);
						Mage::log("mktime gives me month " . $m . "year " . $Y);
					}
				Mage::log("the next date (Monthly) " . date("Y-m-d", strtotime($nextDate)));
				break;
			
				// ###################### //check for 29th needed ??
				
				case 'Semi-Annually':
				$nextDate = (date("Y-m-d", ($date)) . " +6 months");
				Mage::log("the next date (Semi-annually) " . date("Y-m-d", strtotime($nextDate)));
				break;
			
				case 'Annually':
				$nextDate = (date("Y-m-d", ($date)) . " +1 year");
				Mage::log("the next date (Annually) " . date("Y-m-d", strtotime($nextDate)));
				break;
			}
		//	return $nextDate; 
			return date("Y-m-d"); // TODO : Do not export for testing purposes only.
		}
		
		// ################### FOR FAILED TRANSACTIONS ############################
// 		$transaction = Mage::getModel('core/resource_transaction');
// 		$transaction->addObject($order);
// 		$transaction->addCommitCallback(array($order, 'place'));
// 		$transaction->addCommitCallback(array($order, 'save'));
// 		$transaction->save();
		// ################### FOR FAILED TRANSACTIONS ############################
		public function catchFailedSubscriptionTxnInfo(Varien_Event_Observer $observer)
		{
			Mage::log("here in catchFailedblabla");
			Mage::log($observer->getRecycletime());
			//Mage::log();
		}
}
