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

class Litle_Palorus_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function saveCustomerInsight($payment, $litleResponse) {
		preg_match('/.*(\d\d\d\d)/', $payment->getCcNumber(), $matches);
		$last4 = $matches[1];
		$data = array(
						'customer_id' => $payment->getOrder()->getCustomerId(), 
						'order_number' => XMLParser::getNode($litleResponse, 'orderId'),
						'order_id' => $payment->getOrder()->getId(),
						'affluence' => Litle_Palorus_Helper_Data::formatAffluence(XMLParser::getNode($litleResponse,"affluence")),
						'last' => $last4,
						'order_amount' => Litle_Palorus_Helper_Data::formatAvailableBalance($amountToPass),
						'affluence' => Litle_Palorus_Helper_Data::formatAffluence(XMLParser::getNode($litleResponse,"affluence")),
						'issuing_country' => XMLParser::getNode($litleResponse, 'issuerCountry'),
						'prepaid_card_type' => Litle_Palorus_Helper_Data::formatPrepaidCardType(XMLParser::getNode($litleResponse, 'prepaidCardType')),
						'funding_source'=> Litle_Palorus_Helper_Data::formatFundingSource(XMLParser::getNode($litleResponse, 'type')),
						'available_balance' => Litle_Palorus_Helper_Data::formatAvailableBalance(XMLParser::getNode($litleResponse, 'availableBalance')),
						'reloadable' => Litle_Palorus_Helper_Data::formatReloadable(XMLParser::getNode($litleResponse, 'reloadable')),
		);
		Mage::getModel('palorus/insight')->setData($data)->save();
	}

	public function saveVault($payment, $litleResponse, $miscData = NULL) {
		// get the token
		$token = XMLParser::getNode($litleResponse, 'litleToken');
		if($token == NULL) 
		{
			$token = $miscData['litleToken'];
				if($token == NULL)
					return;
		}
		
		// get the last 4
		preg_match('/.*(\d\d\d\d)/', $payment->getCcNumber(), $matches);
		$last4 = $matches[1];
		if( empty($last4) ){
			preg_match('/.*(\d\d\d\d)/', $token, $matches);
			$last4 = $matches[1];
		}
		
		$customerId = $payment->getOrder()->getCustomerId();
		$orderId = $payment->getOrder()->getId();
		$type = XMLParser::getNode($litleResponse, 'type');
		if( empty($type) ){
			$type = $miscData['type'];
		}
		$bin = XMLParser::getNode($litleResponse, 'bin');
		$data = array(
			'customer_id' => $customerId, 
			'order_id' => $orderId,
			'last4' => $last4,
			'token'=> $token,
			'type' => $type,
			'bin' => $bin
		);
		Mage::getModel('palorus/vault')->setData($data)->save();
	}
	
	public function getBaseUrl() {
		$litle = new Litle_CreditCard_Model_PaymentLogic();
		$url = $litle->getConfigData("url");
		return Litle_Palorus_Helper_Data::getBaseUrlFrom($url);		
	}
	
	static public function getBaseUrlFrom($url) {
		if(preg_match("/payments/",$url)) {
			$baseUrl = "https://reports.litle.com";
		}
		else if(preg_match("/sandbox/",$url)) {
			$baseUrl = "https://www.testlitle.com/sandbox";
		}
		else if(preg_match("/precert/",$url)) {
			$baseUrl = "https://reports.precert.litle.com";
		}
		else if(preg_match("/cert/",$url)) {
			$baseUrl = "https://reports.cert.litle.com";
		}
		else  {
			$baseUrl = "http://localhost:2190";
		}
		return $baseUrl;
	}
	

	static public function formatAvailableBalance ($balance)
	{
		return Litle_Palorus_Helper_Data::formatMoney($balance);
	}

	static public function formatAffluence($affluence) {
		if($affluence === '' || $affluence === NULL) {
			return '';
		}
		else if($affluence == 'AFFLUENT') {
			return 'Affluent';
		}
		else if($affluence == 'MASS AFFLUENT') {
			return 'Mass Affluent';
		}
		else {
			return $affluence;
		}
	}

	static public function formatFundingSource($prepaid) {
		if($prepaid == 'FSA') {
			return "FSA";
		}
		return Litle_Palorus_Helper_Data::capitalize($prepaid);
	}

	static public function formatPrepaidCardType($prepaidCardType) {
		return Litle_Palorus_Helper_Data::capitalize($prepaidCardType);
	}

	static public function formatReloadable($reloadable) {
		return Litle_Palorus_Helper_Data::capitalize($reloadable);
	}

	static private function capitalize($original) {
		if($original === '' || $original === NULL) {
			return '';
		}
		$lower = strtolower($original);
		return ucfirst($lower);
	}

	static private function formatMoney($balance) {
		if ($balance === '' || $balance === NULL){
			$available_balance = '';
		}
		else{
			$balance = str_pad($balance, 3, '0', STR_PAD_LEFT);
			$available_balance = substr_replace($balance, '.', -2, 0);
			$available_balance = '$' . $available_balance;
		}

		return $available_balance;
	}


}