<?php
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
}
