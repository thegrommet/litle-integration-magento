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
class Litle_CreditCard_Model_Order_Payment extends Mage_Sales_Model_Order_Payment
{
	//     /**
	//      * Cancel a creditmemo: substract its totals from the payment
	//      *
	//      * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
	//      * @return Mage_Sales_Model_Order_Payment
	//      */
	//     public function cancelCreditmemo($creditmemo)
	//     {
	//         $this->_updateTotals(array(
	//             'amount_refunded' => -1 * $creditmemo->getGrandTotal(),
	//             'base_amount_refunded' => -1 * $creditmemo->getBaseGrandTotal(),
	//             'shipping_refunded' => -1 * $creditmemo->getShippingAmount(),
	//             'base_shipping_refunded' => -1 * $creditmemo->getBaseShippingAmount()
	//         ));
	//         Mage::dispatchEvent('sales_order_payment_cancel_creditmemo',
	//             array('payment' => $this, 'creditmemo' => $creditmemo)
	//         );
	//         return $this;
	//     }

	protected function _reverseRefund($isOnline, $amount = null, $gatewayCallback = 'void')
	{
		$order = $this->getOrder();
		// attempt to void
		if ($isOnline) {
			$this->getMethodInstance()->setStore($order->getStoreId())->$gatewayCallback($this);
		}
		if ($this->_isTransactionExists()) {
			return $this;
		}
		 
		foreach($order->getItemsCollection() as $item){
			if ($item->getQtyRefunded() > 0)
			$item->setQtyRefunded(0)->save();
		}
		 
		$order
		->setBaseDiscountRefunded(0)
		->setBaseShippingRefunded(0)
		->setBaseSubtotalRefunded(0)
		->setBaseTaxRefunded(0)
		->setBaseShippingTaxRefunded(0)
		->setBaseTotalOnlineRefunded(0)
		->setBaseTotalOfflineRefunded(0)
		->setBaseTotalRefunded(0)
		->setTotalOnlineRefunded(0)
		->setTotalOfflineRefunded(0)
		->setDiscountRefunded(0)
		->setShippingRefunded(0)
		->setShippingTaxRefunded(0)
		->setSubtotalRefunded(0)
		->setTaxRefunded(0)
		->setTotalRefunded(0)->save();
		 
		// update transactions, order state and add comments
		$transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, null, true);
		$message = $this->hasMessage() ? $this->getMessage() : "Voided Refund.";
		$message = $this->_prependMessage($message);
		$message = $this->_appendTransactionToMessage($transaction, $message);
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
	}

	protected function _voidCapture($isOnline, $amount = null, $gatewayCallback = 'void')
	{
		$order = $this->getOrder();
		// attempt to void
		if ($isOnline) {
			$this->getMethodInstance()->setStore($order->getStoreId())->$gatewayCallback($this);
		}
		if ($this->_isTransactionExists()) {
			return $this;
		}

		foreach($order->getItemsCollection() as $orderItem){
			$orderItem->setQtyInvoiced(0);
			$orderItem->setTaxInvoiced(0);
        	$orderItem->setBaseTaxInvoiced(0);
        	$orderItem->setHiddenTaxInvoiced(0);
        	$orderItem->setBaseHiddenTaxInvoiced(0);

        	$orderItem->setDiscountInvoiced(0);
        	$orderItem->setBaseDiscountInvoiced(0);

        	$orderItem->setRowInvoiced(0);
        	$orderItem->setBaseRowInvoiced(0);
		}
		
		$order
		->setBaseDiscountInvoiced(0)
		->setBaseShippingInvoiced(0)
		->setBaseSubtotalInvoiced(0)
		->setBaseTaxInvoiced(0)
		->setBaseTotalInvoiced(0)
		->setBaseTotalInvoicedCost(0)
		->setDiscountInvoiced(0)
		->setShippingInvoiced(0)
		->setSubtotalInvoiced(0)
		->setTaxInvoiced(0)
		->setTotalInvoiced(0)
		->setHiddenTaxInvoiced(0)
		->setBaseHiddenTaxInvoiced(0)
		->setShippingTaxInvoiced(0)
		->setBaseShippingTaxInvoiced(0)
		->setTotalPaid(0)
		->setBaseTotalPaid(0);
		
		$this->setBaseShippingCaptured(0);
		$this->setShippingCaptured(0);
		$this->setAmountPaid(0);
		$this->setBaseAmountPaid(0);
		$this->setBaseAmountPaidOnline(0);
		
		$order->setBaseGrandTotal($order->getGrandTotal());
		
		foreach ($order->getInvoiceCollection() as $invoice) {
 			$invoice->setState("3")->save();	//3 means cancelled
		}
		


		// update transactions, order state and add comments
		$transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, true);
		$message = $this->hasMessage() ? $this->getMessage() : "Voided Capture.";
		$message = $this->_prependMessage($message);
		$message = $this->_appendTransactionToMessage($transaction, $message);
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
	}
	

	/**
	 * Void payment either online or offline (process void notification)
	 * NOTE: that in some cases authorization can be voided after a capture. In such case it makes sense to use
	 *       the amount void amount, for informational purposes.
	 * Updates payment totals, updates order status and adds proper comments
	 *
	 * @param bool $isOnline
	 * @param float $amount
	 * @param string $gatewayCallback
	 * @return Mage_Sales_Model_Order_Payment
	 */
	protected function _void($isOnline, $amount = null, $gatewayCallback = 'void')
	{
		if(Mage::helper("creditcard")->isMOPLitle($this))
		{
			$order = $this->getOrder();
			if(Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND))
			{
				$this->_reverseRefund($isOnline, $amount, $gatewayCallback);
			} else if(Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH)){
				parent::_void($isOnline, $amount, $gatewayCallback);
			} else if(Mage::helper("creditcard")->isStateOfOrderEqualTo($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE)){
				$this->_voidCapture($isOnline, $amount, $gatewayCallback);
			} else {
				parent::_void($isOnline, $amount, $gatewayCallback);
			}
		} else {
			parent::_void($isOnline, $amount, $gatewayCallback);
		}

		return $this;
	}
}
