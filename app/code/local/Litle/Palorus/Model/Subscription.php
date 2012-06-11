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
	protected $iterLengthToValMap = array('Daily' => 1, 'Weekly' => 7, 'Bi-Weekly' => 14, 'Semi-Monthly' => 15, 'Monthly' => 30, 'Semi-Annually' => 182, 'Annually' => 365);
	
	protected function _construct()
	{
		$this->_model = 'palorus/subscription';
		$this->_init($this->_model);
	}
	
	public function callFromCron()
	{
		Mage::log("inside call from cron");
		$subscriptionCronHistoryModel = Mage::getModel('palorus/subscriptionCronHistory');
		$subscriptionCronHistoryData = array("time_ran" => date( 'Y-m-d H:i:s', time()) );
		$subscriptionCronHistoryModel->setData($subscriptionCronHistoryData)->save();
		$subscriptionCronHistoryCollection = $subscriptionCronHistoryModel->getCollection();
		$subscriptionCronHistoryCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('MAX(cron_history_id) as cron_id');
		
		$cronId = 0;
		foreach($subscriptionCronHistoryCollection as $subscriptionCronHistoryCollectionItem)
		{
			// do nothing -- DO NOT DELETE!
			$cronId = $subscriptionCronHistoryCollectionItem['cron_id'];
		}
		
		
		// Get all items from Subscription Suspend where turn_on_date is between now and 2 days ago.
		// 2 days is a buffer in the unlikely scenario that the cron jobs didn't run.
		$subscriptionSuspendCollection = Mage::getModel('palorus/subscriptionSuspend')->getCollection();
		$subscriptionSuspendCollection->addFieldToFilter('turn_on_date', array(
		    														'from' => date('d F Y', ( time()-(2 * 24 * 60 * 60) ) ),
		    														'to' => date('d F Y'),
				    												'date' => true,
																	));
		
		// For each record grabbed from above, turn the Active flag in the subscription table to true
		foreach($subscriptionSuspendCollection as $suspendRecord)
		{
			Mage::log("########## Subscription id: " . $suspendRecord['subscription_id'] . " ##########");
			$tempRecord = Mage::getModel('palorus/subscription');
			$tempRecord->load($suspendRecord['subscription_id']);
			$tempRecord->setActive(true);
			$tempRecord->save();
		}
		
		$collection = Mage::getModel('palorus/subscription')->getCollection();		
		foreach($collection as $collectionItem)
		{
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			$customerId = $collectionItem['customer_id'];
			$productId = $collectionItem['product_id'];
			$subscriptionId = $collectionItem['subscription_id'];
			if( strtotime($collectionItem['start_date']) < time() && $collectionItem['active'] === false )
			{
				$collectionItem['active'] = true;
				$collectionItem->save();
			}

			//################################################################
			//############ Implement last ran for each subscription ##########
			//############ so that same subscription does not get run every single cron job..... (see the if statement below!)
			if(		$collectionItem['active'] && 
					($collectionItem['num_of_iterations_ran'] < $collectionItem['num_of_iterations'] )&&
					(strtotime($collectionItem['next_bill_date']) < time())
			  )
			{
				$subscriptionHistoryModel = Mage::getModel('palorus/subscriptionHistory');
				$subscriptionHistoryItemData = array("subscription_id" => $subscriptionId,
													 "cron_id" => $cronId);
				$returnFromCreateOrder = $this->createOrder($productId, $customerId, $originalOrderId);
				if( !$returnFromCreateOrder["success"] )
				{
					$collectionItem->setActive(false);
				}
				else
				{
					$collectionItem->setNumOfIterationsRan($collectionItem['num_of_iterations_ran'] + 1);
				}	
				
				$collectionItem['next_bill_date'] = $this->getNextBillDate($collectionItem['iteration_length']);
				$subscriptionHistoryItemData = array_merge($subscriptionHistoryItemData,$returnFromCreateOrder);
				$subscriptionHistoryModel->setData($subscriptionHistoryItemData)->save();			
				$collectionItem->save();
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
 		}
		return array("success" => $success, "order_id" => $orderId);
	}

	public function getNextBillDate($iterLength)
	{
			Mage::log("inside next bill date");
			$nextDate; 
//			$date = date("Y-m-d");// current date
			$date = mktime(0, 0, 0, 7, 1, 2000);
// 			$date = new DateTime();
			Mage::log("the date is " . date("Y-m-d",($date)));
			$lastDay = date('t',strtotime($date));
			$checkDate = date('d', strtotime($date));
			
			switch($iterLength)
			{
				case 'Daily':
			    $nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +1 day");
				break;	
				
				case 'Weekly':
				$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +1 week");
				break;
			
				case 'Bi-Weekly':
				$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +2 weeks");
				break;
			
				// ###################### //check for 29th
				
				case 'Semi-Monthly': 
				if($checkDate < 15)
				$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +15 days");
				else
				{
					if($lastDay == 28)
					$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +13 days");
					
					else if($lastDay == 29)
					$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +14 days");
					
					else if($lastDay == 30)
					$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +15 days");
					
					else
					$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +16 days");
				}
				
				break;
			
				case 'Monthly':
				$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +1 month");
				
				if($checkDate == 29 || $checkDate == 30 || $checkDate == 31)
					{
						$m = $checkDate->format('m');
						$Y = $checkDate->format('Y');
						$nextDate->setDate($Y , $m , 28);
					}
				break;
			
				case 'Semi-Annually':
				$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +6 months");
				break;
			
				case 'Annually':
				$nextDate = strtotime(date("Y-m-d", strtotime($date)) . " +1 year");
				break;
			}
			return $nextDate;
		}
	
}
