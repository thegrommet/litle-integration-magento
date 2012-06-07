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