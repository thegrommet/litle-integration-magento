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


class Litle_CreditCard_Block_Adminhtml_Orderview extends Mage_Adminhtml_Block_Sales_Order_View {
		
	public function __construct() {
		parent::__construct();
		
		
 		$order = $this->getOrder();
	    if(Mage::helper("creditcard")->isMOPLitle($order->getPayment()))
		{
// 			check if Auth-Reversal needs to be shown
			if( Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH) &&
				!(Mage::helper("creditcard")->isMOPLitleECheck($order->getPayment()->getData('method')))
			)
			{
				$message = 'Are you sure you want to reverse the authorization?';
				$this->_updateButton('void_payment', 'label','Auth-Reversal');
				$this->_updateButton('void_payment', 'onclick', "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')");
			}
// 			check if Void-Refund needs to be shown		
			else if( Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND))
			{
				$onclickJs = 'deleteConfirm(\''
				. Mage::helper('sales')->__('Are you sure? The refund request will be canceled.')
				. '\', \'' . $this->getVoidPaymentUrl() . '\');';
				
				$this->_addButton('void_refund', array(
				                'label'    => 'Void Refund',
				                'onclick'  => $onclickJs,
				));
			}
			//check if void capture or void sale needs to be shown
			else if(Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE) &&
				$this->wasLastTxnLessThan48HrsAgo($order->getPayment()))
			{
				$mop = $order->getPayment()->getData('method');
				//check if paying with a credit card
				if(Mage::helper("creditcard")->isMOPLitleCC($mop)){
					$onclickJs = 'deleteConfirm(\''
					. Mage::helper('sales')->__('Are you sure?  If any previous partial captures were done on this order, or if capture was not done today then do a refund instead.')
					. '\', \'' . $this->getVoidPaymentUrl() . '\');';
				
					$this->_addButton('void_capture', array(
								                'label'    => 'Void Capture',
								                'onclick'  => $onclickJs,
					));
				}
				//check if paying with Litle echeck
				elseif(Mage::helper("creditcard")->isMOPLitleECheck($mop)){
					$onclickJs = 'deleteConfirm(\''
					. Mage::helper('sales')->__('Are you sure?  If any previous partial captures were done on this order, or if capture was not done today then do a refund instead.')
					. '\', \'' . $this->getVoidPaymentUrl() . '\');';
					
					$this->_addButton('void_sale', array(
													                'label'    => 'Void Sale',
													                'onclick'  => $onclickJs,
					));
				}
			}
		}
	}
	
	public function wasLastTxnLessThan48HrsAgo(Varien_Object $payment)
	{
		$lastTxnId = $payment->getLastTransId();
		$lastTxn = $payment->getTransaction($lastTxnId);
		$timeOfLastTxn = $lastTxn->getData('created_at');
	
		//check if last txn was less than 48 hrs ago (172800 seconds == 48 hrs)
		return ((time()-strtotime($timeOfLastTxn)) < 172800);
	}
  
}