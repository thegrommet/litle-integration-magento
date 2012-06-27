<?php
/**
* Litle CreditCard Module
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
* @package    Litle_CreditCard
* @copyright  Copyright (c) 2012 Litle & Co.
* @license    http://www.opensource.org/licenses/mit-license.php
* @author     Litle & Co <sdksupport@litle.com> www.litle.com/developers
*/
require_once('Litle/LitleSDK/LitleOnline.php');

class Litle_CreditCard_Model_PaymentLogic extends Mage_Payment_Model_Method_Cc
{
	/**
	 * unique internal payment method identifier
	 */
	protected $_code = 'creditcard';
	
	protected $_formBlockType = 'creditcard/form_creditCard';
	/**
	 * this should probably be true if you're using this
	 * method to take payments
	 */
	protected $_isGateway               = true;

	/**
	 * can this method authorise?
	 */
	protected $_canAuthorize            = true;

	/**
	 * can this method capture funds?
	 */
	protected $_canCapture              = true;

	/**
	 * can we capture only partial amounts?
	 */
	protected $_canCapturePartial       = true;

	/**
	 * can this method refund?
	 */
	protected $_canRefund               = true;

	protected $_canRefundInvoicePartial		= true;

	/**
	 * can this method void transactions?
	 */
	protected $_canVoid                 = true;

	/**
	 * can admins use this payment method?
	 */
	protected $_canUseInternal          = true;

	/**
	 * show this method on the checkout page
	 */
	protected $_canUseCheckout          = true;

	/**
	 * available for multi shipping checkouts?
	 */
	protected $_canUseForMultishipping  = true;

	/**
	 * can this method save cc info for later use?
	 */
	protected $_canSaveCc = false;

	public function getConfigData($fieldToLookFor, $store = NULL)
	{
		$returnFromThisModel = Mage::getStoreConfig('payment/CreditCard/' . $fieldToLookFor);
		if( $returnFromThisModel == NULL )
		$returnFromThisModel = parent::getConfigData($fieldToLookFor, $store);

		return $returnFromThisModel;
	}

	public function isFromVT($payment, $txnType)
	{
		$parentTxnId = $payment->getParentTransactionId();
		if( $parentTxnId == "Litle VT" )
		{
			Mage::throwException("This order was placed using Litle Virtual Terminal. Please process the $txnType by logging into Litle Virtual Terminal (https://vt.litle.com).");
		}
	}

	public function assignData($data)
	{
		$info = $this->getInfoInstance();
		if( $this->getConfigData('paypage_enabled') == "1")
		{
			if (!($data instanceof Varien_Object)) {
				$data = new Varien_Object($data);
			}

			
			$info->setAdditionalInformation('paypage_enabled', $data->getPaypageEnabled());
			$info->setAdditionalInformation('paypage_registration_id', $data->getPaypageRegistrationId());
			$info->setAdditionalInformation('paypage_order_id', $data->getOrderId());
			$info->setAdditionalInformation('cc_vaulted', $data->getCcVaulted());
		}

		$info->setAdditionalInformation('ordersource', $data->getOrdersource());
		
		return parent::assignData($data);
	}



	public function validate()
	{
		//no cc validation required.
		return $this;
	}

	public function litleCcTypeEnum(Varien_Object $payment)
	{
		$typeEnum = "";
		if ($payment->getCcType() == "AE"){
			$typeEnum = "AX";
		}
		elseif ($payment->getCcType() == "JCB"){
			$typeEnum = "JC";
		}
		else{
			$typeEnum =$payment->getCcType();
		}
		return $typeEnum;
	}

	public function getCreditCardInfo(Varien_Object $payment)
	{
		$retArray = array();
		$retArray["type"] = $this->litleCcTypeEnum($payment);
		$retArray["number"] = $payment->getCcNumber();
		preg_match("/\d\d(\d\d)/", $payment->getCcExpYear(), $expYear);
		$retArray["expDate"] = sprintf('%02d%02d', $payment->getCcExpMonth(), $expYear[1]);
		$retArray["cardValidationNum"] = $payment->getCcCid();

		return $retArray;
	}

	public function getPaypageInfo($payment)
	{
		$info = $this->getInfoInstance();

		$retArray = array();
		$retArray["type"] = $this->litleCcTypeEnum($payment);
		$retArray["paypageRegistrationId"] = $info->getAdditionalInformation('paypage_registration_id');
		preg_match("/\d\d(\d\d)/", $payment->getCcExpYear(), $expYear);
		$retArray["expDate"] = sprintf('%02d%02d', $payment->getCcExpMonth(), $expYear[1]);
		$retArray["cardValidationNum"] = $payment->getCcCid();

		return $retArray;
	}

	public function getTokenInfo($payment)
	{
		$info = $this->getInfoInstance();
	
		$vaultIndex = $info->getAdditionalInformation('cc_vaulted');
		$purchases = Mage::helper('creditcard')->uniqueCreditCard(Mage::helper('customer')->getCustomer()->getEntityId());

		$retArray = array();
		$retArray["type"] = $purchases[$vaultIndex - 1]['type'];
		$retArray["litleToken"] = $purchases[$vaultIndex - 1]['token'];
		$retArray["cardValidationNum"] = $payment->getCcCid();
		$payment->setCcLast4(substr($retArray["litleToken"], -4));
		$payment->setCcType($retArray["type"]);
		return $retArray;
	}
	
	public function creditCardOrPaypageOrToken($payment){
		$info = $this->getInfoInstance();
		$vaultIndex = $info->getAdditionalInformation('cc_vaulted');
		$payment_hash = array();
		if ($vaultIndex > 0){
			$payment_hash['token'] = $this->getTokenInfo($payment);
		}
		elseif ($info->getAdditionalInformation('paypage_enabled') == "1" ){
			$payment_hash['paypage'] = $this->getPaypageInfo($payment);
		}
		else{
			$payment_hash['card'] = $this->getCreditCardInfo($payment);
		}
		return $payment_hash;
	}

	public function getContactInformation($contactInfo)
	{
		if(!empty($contactInfo)){
			$retArray = array();
			$retArray["firstName"] =$contactInfo->getFirstname();
			$retArray["lastName"] = $contactInfo->getLastname();
			$retArray["companyName"] = $contactInfo->getCompany();
			$retArray["addressLine1"] = $contactInfo->getStreet(1);
			$retArray["addressLine2"] = $contactInfo->getStreet(2);
			$retArray["addressLine3"] = $contactInfo->getStreet(3);
			$retArray["city"] = $contactInfo->getCity();
			$retArray["state"] = $contactInfo->getRegion();
			$retArray["zip"] = $contactInfo->getPostcode();
			$retArray["country"] = $contactInfo->getCountry();
			$retArray["email"] = $contactInfo->getCustomerEmail();
			$retArray["phone"] = $contactInfo->getTelephone();
			return $retArray;
		}
		return NULL;
	}


	public function getBillToAddress(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		if(!empty($order)){
			$billing = $order ->getBillingAddress();
			if(!empty($billing)){
				return $this->getContactInformation($billing);
			}
		}
		return NULL;
	}

	public function getShipToAddress(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		if(!empty($order)){
			$shipping = $order->getShippingAddress();
			if(!empty($shipping)){
				return $this->getContactInformation($shipping);
			}
		}
		return NULL;
	}


	public function getIpAddress(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		if(!empty($order)){
			return $order->getRemoteIp();
		}
		return NULL;
	}



	public function getMerchantId(Varien_Object $payment){
		$order = $payment->getOrder();
		$currency = $order->getOrderCurrencyCode();
		$string2Eval = 'return array' . $this->getConfigData("merchant_id") . ';';
		$merchant_map = eval($string2Eval);
		$merchantId = $merchant_map[$currency];
		return $merchantId;
	}


	public function merchantData(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		$hash = array('user'=> $this->getConfigData("user"),
 					'password'=> $this->getConfigData("password"),
					'merchantId'=> $this->getMerchantId($payment),
					'version'=>'8.10',
					'merchantSdk'=>'Magento;8.14.0',
					'reportGroup'=>$this->getMerchantId($payment),
					'customerId'=> $order->getCustomerEmail(),
					'url'=>$this->getConfigData("url"),	
					'proxy'=>$this->getConfigData("proxy"),
					'timeout'=>$this->getConfigData("timeout")
		);
		return $hash;
	}


	public function getCustomBilling($url){
		$retArray = array();

		if (strlen($url)>13){
			$url = str_replace('http://','',$url);
			$url = str_replace('https://','',$url);
			$url_temp = explode('/',$url);
			$url = $url_temp['0'];
			if (strlen($url)>13){
				$url = str_replace('www.','',$url);
				if (strlen($url)>13){
					$url_temp2 = explode('.',$url);
					$count = count($url_temp2);
				}if($count < 3){
					if (strlen($url_temp2['0'] . '.' . $url_temp2['1']) > 13){
						$url = $url_temp2['0'];
					}else{
						$url = $url_temp2['0'] . '.' . $url_temp2['1'];
					}
				}
			}
		}
	
		$url = substr($url,0,13);
		if(substr($url,12) === '.'){
			$url = substr($url,0,12);
		}
		elseif (substr($url,0) === '.'){
			$url = substr($url,1,12);
		}
		$retArray['url'] = $url;

		return $retArray;
	}

	public function getOrderDate(Varien_Object $payment){
		$order = $payment->getOrder();
		$date = $order->getCreatedAtFormated(short);
		$date_temp = explode('/',$date);
		$month = $date_temp['0'];
		if ((int)$month < 10){
			$month = '0' . $month;
		}
		$day=$date_temp['1'];
		if ((int)$day < 10){
			$day = '0' . $day;
		}
		$year_temp = explode(' ',$date_temp['2']);
		$year = '20' . $year_temp['0'];
		return $year . '-' . $month . '-' . $day;
	}
	
	public function getProductAttribute($productId, $attributeName) {
		Mage::log("product id is " .$productId . "Attribute name is " . $attributeName);
		$product = Mage::helper("catalog/product")->getProduct($productId, null);
		$attributeValue = $product->getAttributeText($attributeName);
		return $attributeValue;
	}
	
	public function getLineItemData(Varien_Object $payment){
 		$order = $payment->getOrder();
 		$items = $order->getAllItems();
 		$i = 0;
 		$lineItemArray = array();
		foreach ($items as $itemId => $item)
		{
 			$name = $item->getName();
 			$unitPrice=$item->getPrice();
 			$sku=$item->getSku();
 			$productId=$item->getProductId();
 			$qty=$item->getQtyToInvoice();
 			$product = Mage::getModel('catalog/product')->load($productId);
 
 			if( strlen($name) > 26 ) {
 				$name = substr($name,0,26);
 			}
			$lineItemArray[$i] = array(
			'itemSequenceNumber'=>($i+1),
			'itemDescription'=>$name,
			'productCode'=>$productId,
			'quantity'=>$qty,
			'lineItemTotal'=>(($unitPrice*$qty)*100),
			'unitCost'=>($unitPrice * 100));
 			$i++;
 		}
 		return $lineItemArray;
	}


	public function getEnhancedData(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		$billing = $order->getBillingAddress();
		$i = 0;
		$hash = array('salesTax'=> $order->getTaxAmount()*100,
			'discountAmount'=>$order->getDiscountAmount()*100,
			'shippingAmount'=>$order->getShippingAmount()*100,
			'destinationPostalCode'=>$billing->getPostcode(),
			'destinationCountryCode'=>$billing->getCountry(),
			'orderDate'=>$this->getOrderDate($payment),
			'detailTax'=>array(array('taxAmount'=>$order->getTaxAmount()*100)),
			'lineItemData' => $this->getLineItemData($payment)
		);
		return $hash;
	}

	public function getFraudCheck(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		$hash = array('customerIpAddress'=> $order->getRemoteIp()
		);
		return $hash;
	}
	
	public function getUpdater($litleResponse, $parentNode, $childNode=NULL){
	
		if($childNode === NULL){
			$new = $litleResponse->getElementsByTagName($parentNode)->item(0);
		}
		else{
			$new = $litleResponse->getElementsByTagName($parentNode)->item(0)->getElementsByTagName($childNode)->item(0)->nodeValue;
		}
	
		return $new;
	}
	
	public function accountUpdater(Varien_Object $payment,$litleResponse){

 		if($this->getUpdater($litleResponse, 'newCardInfo') !==  NULL){

			$payment->setCcLast4(substr($this->getUpdater($litleResponse, 'newCardInfo', 'number'), -4));
			$payment->setCcType($this->getUpdater($litleResponse, 'newCardInfo','type'));
			$payment->setCcExpDate($this->getUpdater($litleResponse, 'newCardInfo','expDate'));
			
 		}
 		elseif($this->getUpdater($litleResponse, 'newCardTokenInfo') !==  NULL){
 			
 			$payment->setCcNumber($this->getUpdater($litleResponse, 'newCardTokenInfo','litleToken'));
			$payment->setCcLast4(substr($this->getUpdater($litleResponse, 'newCardTokenInfo', 'litleToken'), -4));
			$payment->setCcType($this->getUpdater($litleResponse, 'newCardTokenInfo','type'));
			$payment->setCcExpDate($this->getUpdater($litleResponse, 'newCardTokenInfo','expDate'));
 		}
 		
	}
	

	public function processResponse(Varien_Object $payment,$litleResponse){
		$info = $this->getInfoInstance();
		$this->accountUpdater($payment,$litleResponse);
		$message = XmlParser::getAttribute($litleResponse,'litleOnlineResponse','message');
		if ($message == "Valid Format"){
			$isSale = ($payment->getCcTransId() != NULL)? FALSE : TRUE;
			if( isset($litleResponse))
			{
				$litleResponseCode = XMLParser::getNode($litleResponse,'response');
				if($litleResponseCode != "000")
				{
					if(($litleResponseCode === "362") && Mage::helper("creditcard")->isStateOfOrderEqualTo($payment->getOrder(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE))
					{
						Mage::throwException("The void did not go through. Do a refund instead.");
					}
					else
					{
						$payment
						->setStatus("Rejected")
						->setCcTransId(XMLParser::getNode($litleResponse,'litleTxnId'))
						->setLastTransId(XMLParser::getNode($litleResponse,'litleTxnId'))
						->setTransactionId(XMLParser::getNode($litleResponse,'litleTxnId'))
						->setIsTransactionClosed(0)
						->setTransactionAdditionalInfo("additional_information", XMLParser::getNode($litleResponse,'message'));
						
						$ordersource = $info->getAdditionalInformation('ordersource');
						if(empty($ordersource))
							$ordersource = "ecommerce";
						
						if( $ordersource === "recurring" )
						{
							$subscriptionSingleton = Mage::getSingleton('palorus/subscription');
							//$subscriptionSingleton->setRecycleNextRunDate((time()+(2 * 24 * 60 * 60)));
							$subscriptionSingleton->setRecycleNextRunDate(XMLParser::getNode($litleResponse,'nextRecycleTime'));
							$subscriptionSingleton->setRecycleAdviceEnd(XMLParser::getNode($litleResponse,'recycleAdviceEnd'));
							$subscriptionSingleton->setShouldRecycleDateBeRead(true);
						}
						
						if($isSale)
							throw new Mage_Payment_Model_Info_Exception(Mage::helper('core')->__("Transaction was not approved. Contact us or try again later."));
						else
							throw new Mage_Payment_Model_Info_Exception(Mage::helper('core')->__("Transaction was not approved. Contact Litle or try again later."));
					}
				}
				else
				{
					$payment
					->setStatus("Approved")
					->setCcTransId(XMLParser::getNode($litleResponse,'litleTxnId'))
					->setLastTransId(XMLParser::getNode($litleResponse,'litleTxnId'))
					->setTransactionId(XMLParser::getNode($litleResponse,'litleTxnId'))
					->setIsTransactionClosed(0)
					->setTransactionAdditionalInfo("additional_information", XMLParser::getNode($litleResponse,'message'));
					
				}
				return true;
			}
		}
		else{
			Mage::throwException($message);
		}
		
		
	}
	/**
	 * this method is called if we are just authorising
	 * a transaction
	 */
	public function authorize(Varien_Object $payment, $amount)
	{
		$info = $this->getInfoInstance();
		if (preg_match("/sales_order_create/i", $_SERVER['REQUEST_URI']) && ($this->getConfigData('paypage_enable') == "1") )
		{
			$payment
			->setStatus("N/A")
			->setCcTransId("Litle VT")
			->setLastTransId("Litle VT")
			->setTransactionId("Litle VT")
			->setIsTransactionClosed(0)
			->setCcType("Litle VT");
		}
		else{
			$order = $payment->getOrder();
			$orderId =  $order->getIncrementId();
			$amountToPass = ($amount* 100);
			$ordersource = $info->getAdditionalInformation('ordersource');
			if(empty($ordersource))
				$ordersource = "ecommerce";
			//	$ordersource = (empty($info->getAdditionalInformation('ordersource'))) ? "ecommerce" : $info->getAdditionalInformation('ordersource');
			if (!empty($order)){
				$hash = array(
				 					'orderId'=> $orderId,
				 					'amount'=> $amountToPass,
				 					'orderSource'=> $ordersource,
									'billToAddress'=> $this->getBillToAddress($payment),
									'shipToAddress'=> $this->getAddressInfo($payment),
									'cardholderAuthentication'=> $this->getFraudCheck($payment),
									'enhancedData'=>$this->getEnhancedData($payment),
									'customBilling'=>$this->getCustomBilling(Mage::app()->getStore()-> getBaseUrl())
				);
				$payment_hash = $this->creditCardOrPaypageOrToken($payment);
				$hash_temp = array_merge($hash,$payment_hash);
				$merchantData = $this->merchantData($payment);
				$hash_in = array_merge($hash_temp,$merchantData);
				$litleRequest = new LitleOnlineRequest();
				$litleResponse = $litleRequest->authorizationRequest($hash_in);
				$this->processResponse($payment,$litleResponse);
				
				if( $ordersource != "recurring" )
					$this->populateSubscription($payment);
				
				Mage::helper("palorus")->saveCustomerInsight($payment, $litleResponse);
				Mage::helper("palorus")->saveVault($payment, $litleResponse, $this->getTokenInfo($payment));
			}
		}
	}

	/**
	 * this method is called if we are authorising AND
	 * capturing a transaction
	 */
	public function capture (Varien_Object $payment, $amount)
	{
		$info = $this->getInfoInstance();
		if (preg_match("/sales_order_create/i", $_SERVER['REQUEST_URI']) && ($this->getConfigData('paypage_enable') == "1") )
		{
			$payment
			->setStatus("N/A")
			->setCcTransId("Litle VT")
			->setLastTransId("Litle VT")
			->setTransactionId("Litle VT")
			->setIsTransactionClosed(0)
			->setCcType("Litle VT");

			return;
		}

		$this->isFromVT($payment, "capture");
		$ordersource = $info->getAdditionalInformation('ordersource');
		if(empty($ordersource))
			$ordersource = "ecommerce";
		$order = $payment->getOrder();
		if (!empty($order)){

			$orderId =$order->getIncrementId();
			$amountToPass = ($amount* 100);
			$isPartialCapture = ($amount < $order->getGrandTotal()) ? "true" : "false";
			$isSale = ($payment->getCcTransId() != NULL)? FALSE : TRUE;

			if( !$isSale )
			{
				$hash = array(
								'litleTxnId' => $payment->getParentTransactionId(),
								'amount' => $amountToPass,
								'partial' => $isPartialCapture
				);
			} else {
				$hash_temp = array(
			 					'orderId'=> $orderId,
			 					'amount'=> $amountToPass,
			 					'orderSource'=> $ordersource,
								'billToAddress'=> $this->getBillToAddress($payment),
								'shipToAddress'=> $this->getAddressInfo($payment),
				);
				$payment_hash = $this->creditCardOrPaypageOrToken($payment);
				$hash = array_merge($hash_temp,$payment_hash);
			}
			$merchantData = $this->merchantData($payment);
			$hash_in = array_merge($hash,$merchantData);
			$litleRequest = new LitleOnlineRequest();

			if( $isSale )
			{
				$litleResponse = $litleRequest->saleRequest($hash_in);
				
			} else {
				$litleResponse = $litleRequest->captureRequest($hash_in);
			}
		}
		$this->processResponse($payment,$litleResponse);
		
		if($isSale)
		{
			
			if( $ordersource != "recurring" )
				$this->populateSubscription($payment);
			
			Mage::helper("palorus")->saveCustomerInsight($payment, $litleResponse);
			Mage::helper("palorus")->saveVault($payment, $litleResponse);
		}
	}

	/**
	 * called if refunding
	 */
	public function refund (Varien_Object $payment, $amount)
	{
		$this->isFromVT($payment, "refund");
		
		$order = $payment->getOrder();
		$isPartialRefund = ($amount < $order->getGrandTotal()) ? true : false;
		
			$amountToPass = ($amount* 100);
			if (!empty($order)){
				$hash = array(
							'litleTxnId' => $payment->getCcTransId(),
							'amount' => $amountToPass
				);
				$merchantData = $this->merchantData($payment);
				$hash_in = array_merge($hash,$merchantData);
				$litleRequest = new LitleOnlineRequest();
				$litleResponse = $litleRequest->creditRequest($hash_in);
			}
			$this->processResponse($payment,$litleResponse);
		
		return $this;
	}

	/**
	 * called if voiding a payment
	 */
	public function void (Varien_Object $payment)
	{
		$this->isFromVT($payment, "void");

		$order = $payment->getOrder();
		if (!empty($order)){
			$hash = array(
						'litleTxnId' => $payment->getCcTransId()
			);
			$merchantData = $this->merchantData($payment);
			$hash_in = array_merge($hash,$merchantData);
			$litleRequest = new LitleOnlineRequest();
			
			if(Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH)){
				$litleResponse = $litleRequest->authReversalRequest($hash_in);
        	} else {
        		$litleResponse = $litleRequest->voidRequest($hash_in);
        	}	
		}
		$this->processResponse($payment,$litleResponse);
	}
	
	public function cancel(Varien_Object $payment)
	{
		$this->void($payment);
	}

	
	public function populateSubscription(Varien_Object $payment)
	{
		$order = $payment->getOrder();
		$items = $order->getAllItems();
		foreach ($items as $itemId => $item)
		{
			$unitPrice=$item->getPrice();
			$productId=$item->getProductId();
			$qty=$item->getQtyToInvoice();
			if($qty == 0)
			{
				$qty = $item->getQtyToShip();
			}
			$product = Mage::getModel('catalog/product')->load($productId);
			// grabs the iteration length value
			$option_id = $product->getLitleSubsItrLen();
			$litleSubscriptionItrLengthValue = "";
			$attributes = Mage::getModel('eav/entity_attribute_option')->getCollection()->setStoreFilter()->join('attribute','attribute.attribute_id=main_table.attribute_id', 'attribute_code');
			foreach ($attributes as $attribute) {
				if ($attribute->getOptionId()==$option_id) {
					$litleSubscriptionItrLengthValue = $attribute->getValue();
				}
			}
			if($this->getProductAttribute($productId, 'litle_subscription') === "Yes") {
				for($j = 0; $j < $qty; $j++) {
					$data = array(
								'product_id' => $productId,
								'initial_order_id' => $payment->getOrder()->getId(),
								'customer_id' => $payment->getOrder()->getCustomerId(),
								'amount' => $product->getLitleSubsAmountPerItr()*100,
								'initial_fees' => $unitPrice*100,
								'num_of_iterations' => $product->getLitleSubsNumOfItrs(),
								'iteration_length' => $litleSubscriptionItrLengthValue,
								'start_date' => time(),
								'next_bill_date' => $this->getNextBillDateAddBasedOnTrial($productId), 
								'active' => true 
					);
					Mage::getModel('palorus/subscription')->setData($data)->save();
				}
			}
		}
	}
	
	public function getNextBillDateAddBasedOnTrial($productId)
	{
		
		$numOfTrialDays = $this->getProductAttribute($productId, 'litle_subs_days_for_trial');
		Mage::log("Here");
		Mage::log("the number of trial days " . $numOfTrialDays);
		$nextDate =  time() + ($numOfTrialDays * 24 * 60 * 60);
		Mage::log("The next date is " . (date("Y-m-d",($nextDate))));
		return $nextDate;
	}
}
