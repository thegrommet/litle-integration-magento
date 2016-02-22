<?php
/*
 * Copyright (c) 2011 Litle & Co.
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
require_once(getenv('MAGENTO_HOME')."/app/Mage.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Core/Block/Abstract.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Core/Block/Template.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Adminhtml/Block/Template.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Adminhtml/Block/Widget/Container.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Adminhtml/Block/Sales/Transactions/Detail.php");
require_once(getenv('MAGENTO_HOME')."/app/code/local/Litle/CreditCard/Model/PaymentLogic.php");

class PaymentLogicTest extends PHPUnit_Framework_TestCase
{
	public function testGenerateUniqueId()
	{
	    Mage::init();
	    $payment = new Varien_Object();
	    $order = new Varien_Object();
	    $billingAddress = new Varien_Object();
	    $order->setBillingAddress($billingAddress);
	    $payment->setOrder($order);
	    $litle = new Litle_CreditCard_Model_PaymentLogic();
	    $mock = $this->getMock('Mage_Payment_Model_Info');
		$hash = $litle->generateAuthorizationHash('123','100', $mock, $payment);
		$this->assertEquals('123', $hash['id']);
	}
	
	public function testFindCaptureLitleTxnToRefundForPayment()
    {
        $capture = new Varien_Object();
        $capture->setTxnId(2);
        $payment = $this->getMock('Mage_Sales_Model_Order_Payment');
        $payment->expects($this->any())
                ->method('lookupTransaction')
                ->with($this->equalTo(false),
                       $this->equalTo(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE))
                ->will($this->returnValue($capture));
        
        $litle = new Litle_CreditCard_Model_PaymentLogic();
        
        $litleTxnId = $litle->findCaptureLitleTxnToRefundForPayment($payment);
        
        $this->assertEquals(2, $litleTxnId);
    }
    
    public function testGetTokenInfo()
    {
        $payment = new Varien_Object();
        $payment->setCcCid('789');
        $vaultCard = new Varien_Object();
        $vaultCard->setCcType('VI');
        $vaultCard->setLast4('1234');
        $vaultCard->setType('VI');
        $vaultCard->setExpirationMonth('12');
        $vaultCard->setExpirationYear('2050');
        $vaultCard->setToken('1111222233331234');
        
        $litle = new Litle_CreditCard_Model_PaymentLogic();
        
        $modelPalorusVault = $this->getMock('Litle_Palorus_Model_Vault');
        $modelPalorusVault->expects($this->any())
                          ->method('load')
                          ->with($this->equalTo(50))
                          ->will($this->returnValue($vaultCard));
        $litle->setModelPalorusVault($modelPalorusVault);
        $info = new Mage_Payment_Model_Info();
        $info->setAdditionalInformation('cc_vaulted', 50);
        
        $arr = array('info_instance' => $info);
        $litle->addData($arr);
        
        $tokenInfo = $litle->getTokenInfo($payment);
        
        $this->assertEquals('1234', $payment->getCcLast4());
        $this->assertEquals('VI', $payment->getCcType());

        $this->assertEquals('789', $tokenInfo['cardValidationNum']);        
        $this->assertEquals('VI', $tokenInfo['type']);
        $this->assertEquals('1111222233331234', $tokenInfo['litleToken']);
        $this->assertEquals('1250', $tokenInfo['expDate']);
    }
    
    public function testGetModelPalorusVault_NotSetYet() {
        $litle = new Litle_CreditCard_Model_PaymentLogic();
        $vault = $litle->getModelPalorusVault();
        $this->assertNotNull($vault);
    }
}
