<?php

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
		Mage::log("callFromCron ");
		$collection = Mage::getModel('palorus/subscription')
		->getCollection();
		//->addFieldToFilter('customer_id',$customerId);
		//Mage::log(var_dump($collection));
		
		
		foreach($collection as $collectionItem)
		{
			//Mage::log($collectionItem['subscription_id']);
			//Get the original order for that subscription
			$originalOrderId = $collectionItem['initial_order_id'];
			//Mage::log("Order id is " . $originalOrderId);
			$orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('order_id', $originalOrderId);
			foreach($orderCollection as $order) {
				//Mage::log("Actual order total is " . $order->getBaseGrandTotal());
			}
			
			//$this->createOrder();
		}
		
		createOrder2();
	}
	
	public function createOrder()
	{
		$salesOrderCreateModel = Mage::getSingleton('adminhtml/sales_order_create');
		try {
// 				$this->_processActionData('save');
// 				if ($paymentData = $this->getRequest()->getPost('payment')) {
// 					$this->_getOrderCreateModel()->setPaymentData($paymentData);
// 					$this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
// 				}
				$paymentData = array('method'=>'creditcard');
				$salesOrderCreateModel->setPaymentData($paymentData);
				
				$orderData = array('currency'=>'USD', 
									'account' => array(	'group_id' => '1', 
														'email' => 'gdake@litle.com'),
									'billing_address' => array(	'customer_address_id' => '2',
															   	'prefix' => '',
																'firstname' => 'Greg',
																'middlename' => '',
																'lastname' => 'Dake',
																'suffix' => '',
																'street' => array(	'0' => '900 Chelmsford St.',
																					'1' => ''
																				),
																'city' => 'Lowell',
																'country_id' => 'US',
																'region' => '',
																'region_id' => '32',
																'post_code' => '01824',
																'telephone'	=> '911',
																'fax' => ''
														),
									'shipping_address' => array(	'customer_address_id' => '2',
																	'prefix' => '',
																	'firstname' => 'Greg',
																	'middlename' => '',
																	'lastname' => 'Dake',
																	'suffix' => '',
																	'street' => array(	'0' => '900 Chelmsford St.',
																						'1' => ''
																					),
																	'city' => 'Lowell',
																	'country_id' => 'US',
																	'region' => '',
																	'region_id' => '32',
																	'post_code' => '01824',
																	'telephone'	=> '911',
																	'fax' => ''
														),
									'shipping_method' => 'flatrate_flatrate',
									'comment' => array('customer_note' => '')
								 );
		
			$order = $salesOrderCreateModel
						->setIsValidate(true)
						->importPostData($orderData)
						->createOrder();
			Mage::log("Order created successfully!");
			//$this->_getSession()->clear();
			//Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
			//$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		} catch (Mage_Payment_Model_Info_Exception $e) {
			$this->_getOrderCreateModel()->saveQuote();
			$message = $e->getMessage();
			if( !empty($message) ) {
				$this->_getSession()->addError($message);
			}
			$this->_redirect('*/*/');
		} catch (Mage_Core_Exception $e){
			$message = $e->getMessage();
			if( !empty($message) ) {
				$this->_getSession()->addError($message);
			}
			$this->_redirect('*/*/');
		}
		catch (Exception $e){
			$this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
			$this->_redirect('*/*/');
		}
	}
	
	public function createOrder2(){
		Mage::log("entry point in createOrder2");
		$store = Mage::app()->getStore('default');
		
		Mage::log("1");
		$customer = Mage::getModel('customer/customer');
		$customer->setStore($store);
		$customer->loadByEmail('gdake@litle.com');
		
		Mage::log("2");
		$quote = Mage::getModel('sales/quote');
		$quote->setStore($store);
		$quote->assignCustomer($customer);
		
		Mage::log("3");
		$product1 = Mage::getModel('catalog/product')->load(166); /* HTC Touch Diamond */
		$buyInfo1 = array('qty' => 1);
		
// 		$product2 = Mage::getModel('catalog/product')->load(18); /* Sony Ericsson W810i */
// 		$buyInfo2 = array('qty' => 3);
		Mage::log("4");
		$quote->addProduct($product1, new Varien_Object($buyInfo1));
		//$quote->addProduct($product2, new Varien_Object($buyInfo2));
		Mage::log("5");
		$billingAddress = $quote->getBillingAddress()->addData($customer->getPrimaryBillingAddress()->getData());
		$shippingAddress = $quote->getShippingAddress()->addData($customer->getPrimaryShippingAddress()->getData());
		Mage::log("6");
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
		->setShippingMethod('flatrate_flatrate')
		->setPaymentMethod('litlesubscription');
		Mage::log("7");
		$quote->getPayment()->importData(array(
												'method' => 'litlesubscription', 
												'litletoken' => '2132132132132131',
												'litletokentype' => 'VI',
												'litletokenexpdate' => '0314'
											)
										);
		$quote->collectTotals()->save();
		Mage::log("8");
		$service = Mage::getModel('sales/service_quote', $quote);
		$service->submitAll();
		$order = $service->getOrder();
		Mage::log("9");
		//$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
		//$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		//$invoice->register();
		Mage::log("10");
		//$transaction = Mage::getModel('core/resource_transaction')
		//->addObject($invoice)
		//->addObject($invoice->getOrder());
		Mage::log("11");
		//$transaction->save();
		Mage::log("12");
	}

}