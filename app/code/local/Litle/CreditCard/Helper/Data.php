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
class Litle_CreditCard_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isStateOfOrderEqualTo($order, $inOrderState){
		$payment = $order->getPayment();
		$lastTxnId = $payment->getLastTransId();
		$lastTxn = $payment->getTransaction($lastTxnId);

		if( $lastTxn->getTxnType() === $inOrderState )
		return true;
		else
		return false;
	}

	// TODO:: Needs to be implemented.
	public function isMOPLitleCC($mop){
		return ($mop === "creditcard");
	}

	// TODO:: Needs to be implemented.
	public function isMOPLitleECheck($mop){
		return ($mop === "lecheck");
	}

	public function isMOPLitle($payment){
		$mop = $payment->getData('method');
		return ($this->isMOPLitleCC($mop) || $this->isMOPLitleECheck($mop));
	}

	public function uniqueCreditCard($customerId) {
		$collection = array();
		$collection = Mage::getModel('palorus/vault')
		->getCollection()
		->addFieldToFilter('customer_id',$customerId);
		
		$purchases = array();
		$unique = array();
		$i=0;
		foreach ($collection as $purchase) {
			$purchases[$i] = $purchase->getData();
			$i++;
		}
		
		return $this->populateStoredCreditCard($purchases);
	}
	
	public function populateStoredCreditCard($purchases) {
		
		$unique = array();
		$unique[0] = $purchases[0];
		for ($y=1; $y < count($purchases); $y++){
			$setter = 0;
			for ($x=0; $x <= count($unique); $x++){
				if (($purchases[$y]['type'] === $unique[$x]['type']) && ($purchases[$y]['last4'] === $unique[$x]['last4']))
				{
					$setter = 1;
				}
			}
			if ($setter === 0)
			{
				array_push($unique, $purchases[$y]);
			}
		}
		return $unique;
	}
}
