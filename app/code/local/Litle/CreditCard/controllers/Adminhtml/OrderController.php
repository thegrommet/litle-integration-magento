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
class Litle_CreditCard_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action{

	public function massCaptureAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids', array());
		$countCaptureOrder = 0;
		foreach ($orderIds as $orderId) {
			$order = Mage::getModel('sales/order')->load($orderId);
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice(array());
			if ($order->canInvoice()){
				$invoice->register();
				$this->captureInvoice($invoice);
				$countCaptureOrder++;
			} else {
				$this->_getSession()->addError($this->__('The order #' .  $invoice->getOrder()->getIncrementId() . ' cannot be Captured '));
			}
		}
		if ($countCaptureOrder) {
			$this->_getSession()->addSuccess($this->__('%s order(s) have been Captured', $countCaptureOrder));
		}
		$referrer = $_SERVER['HTTP_REFERER'];
		$this->_redirectUrl($referrer);
	}

	private function captureInvoice($invoice)
	{
		try
		{
			$invoice->setRequestedCaptureCase('online');
			$invoice->sendEmail(true);
			$invoice->setEmailSent(true);
			$invoice->getOrder()->setCustomerNoteNotify(true);
			$invoice->getOrder()->setIsInProcess(true);
			$invoice->capture();
			$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder());
			$transactionSave->save();
		}

		catch (Exception $e)
		{
			Mage::logException($e);
		}
	}
}