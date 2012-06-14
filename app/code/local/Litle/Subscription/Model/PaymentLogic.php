<?php
/**
* Litle Subscription Module
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
* @package    Litle_Subscription
* @copyright  Copyright (c) 2012 Litle & Co.
* @license    http://www.opensource.org/licenses/mit-license.php
* @author     Litle & Co <sdksupport@litle.com> www.litle.com/developers
*/
require_once('Litle/LitleSDK/LitleOnline.php');

class Litle_Subscription_Model_PaymentLogic extends Litle_CreditCard_Model_PaymentLogic
{
	/**
	 * unique internal payment method identifier
	 */
	protected $_code = 'litlesubscription';

	/**
	 * can we capture only partial amounts?
	 */
	protected $_canCapturePartial       = false;

	/**
	 * show this method on the checkout page
	 */
	protected $_canUseCheckout          = false;

	public function getConfigData($fieldToLookFor, $store = NULL)
	{
		$returnFromThisModel = Mage::getStoreConfig('payment/Subscription/' . $fieldToLookFor);
		if( $returnFromThisModel == NULL )
		$returnFromThisModel = parent::getConfigData($fieldToLookFor, $store);
	
		return $returnFromThisModel;
	}
	
	public function assignData($data)
	{
		Mage::log("subscription assigndata being called");
		$info = $this->getInfoInstance();
		
		$info->setAdditionalInformation('litletoken', $data->getLitletoken());
		$info->setAdditionalInformation('litletokentype', $data->getLitletokentype());
		$info->setAdditionalInformation('litletokenexpdate', $data->getLitletokenexpdate());
		$info->setAdditionalInformation('litleissubscription', $data->getLitleissubscription());
		
		return parent::assignData($data);
	}
	
	public function creditCardOrPaypageOrToken($payment){
		Mage::log("subscription creditCardOrPaypage being called");
		$info = $this->getInfoInstance();
		$payment_hash = array();
		
		if( $info->getAdditionalInformation('litletoken') != ""){
			$payment_hash['token']['litleToken'] = $info->getAdditionalInformation('litletoken');
			$payment_hash['token']['type'] = $info->getAdditionalInformation('litletokentype');
			$payment_hash['token']['expDate'] = $info->getAdditionalInformation('litletokenexpdate');
			$payment->setCcLast4(substr($payment_hash['token']['litleToken'], -4));
			$payment->setCcType($payment_hash['token']['type']);
		}
		
		return $payment_hash;
	}
	
// 	public function processResponse(Varien_Object $payment,$litleResponse){
// 		try{
// 			parent::processResponse($payment, $litleResponse);
// 			return true;
// 		} catch (Exception $e){
// 			return false;
// 		}
// 	}
}
