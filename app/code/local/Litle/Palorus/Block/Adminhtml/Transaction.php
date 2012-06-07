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

class Litle_Palorus_Block_Adminhtml_Transaction extends Mage_Adminhtml_Block_Sales_Transactions_Detail {
	public function __construct() {
		parent::__construct();		
	}

	protected function _beforeToHtml() {
		parent::_beforeToHtml();
	}
		
	public function getTxnIdHtml() {
		
		$litle = new Litle_CreditCard_Model_PaymentLogic();
		$url = $litle->getConfigData("url");
		$litleTxnId = $this->_txn->getTxnId();
		$txnType = $this->_txn->getTxnType();
		$method = $this->_txn->getOrderPaymentObject()->getMethod();
		
		Mage::log("Litle_Palorus_Block_Adminhtml_Transaction:getTxnIdHtml - method: $method; txnType: $txnType; url: $url; litleTxnId: $litleTxnId", Zend_Log::DEBUG);		
		$html = Litle_Palorus_Block_Adminhtml_Transaction::_getTxnIdHtml($txnType, $method, $url, $litleTxnId);
		if($html == NULL) {
			return parent::getTxnIdHtml();
		}
		else {
			return $html;
		}
	}
	
	static function _getTxnIdHtml($txnType, $method, $url, $litleTxnId) {
		$litleTxnIdOrig = $litleTxnId;
		if($method != 'creditcard' && $method != 'lecheck') {
			return null;
		}
		if($txnType == 'authorization') {
			if($method == 'lecheck'){
				$litleTxnType = 'echeck/verification';
			}
			else{
				$litleTxnType = 'authorization';
			}
		}
		else if($txnType == 'capture') {
			if($method == 'lecheck'){
				$litleTxnType = 'echeck/deposit';
			}
			else{
				$litleTxnType = 'deposit';
			}
		}
		else if($txnType == 'refund') {
			if($method == 'lecheck'){
				$litleTxnType = 'echeck/refund';
			}
			else{
				$litleTxnType = 'refund';
			}
		}
		else if($txnType == 'void') {
			if(preg_match("/(\d{18})-void/",$litleTxnId,$matches)) {
				$litleTxnId = $matches[1];
				$litleTxnType = 'authorization/reversal';
			}
			else {
				return null;
			}
		}
		
		//$baseUrl = Mage::helper("palorus")->getBaseUrl($url);
		$helper = new Litle_Palorus_Helper_Data();
		$baseUrl = $helper->getBaseUrlFrom($url);
		return "<a href='$baseUrl/ui/reports/payments/$litleTxnType/$litleTxnId'>$litleTxnIdOrig</a>";
	}

}