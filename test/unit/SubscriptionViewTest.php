<?php
/**
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
*/
require_once(getenv('MAGENTO_HOME')."/app/Mage.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Core/Block/Abstract.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Core/Block/Template.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Adminhtml/Block/Template.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Adminhtml/Block/Widget/Container.php");
require_once(getenv('MAGENTO_HOME')."/app/code/core/Mage/Adminhtml/Block/Sales/Transactions/Detail.php");
require_once(getenv('MAGENTO_HOME')."/app/code/local/Litle/Palorus/Block/Adminhtml/Transaction.php");
require_once(getenv('MAGENTO_HOME')."/app/code/local/Litle/Palorus/Block/Adminhtml/Palorus/Insight/Subscriptionview.php");

class SubscriptionViewTest extends PHPUnit_Framework_TestCase
{
	
	
	protected function setUp()
	{
		Mage::app('default');
	}
		
	protected function tearDown(){
	}
		
	public function testDollarFormat(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$this->assertEquals($subView->dollarFormat(1000),10.00);
	}
	
	public function testSetRunNext(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setRunNextIteration('2')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->setRunNext('3');
		$this->assertEquals('3',Mage::getModel('palorus/subscription')->load($subscription->getId())->getRunNextIteration());
	}
	
// 	public function testSetNextBillDate(){
// 		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
// 		$subscription = Mage::getModel('palorus/subscription')->setNextBillDate('2015-15-15 00:00:00')->save();
// 		$subView->setSubscriptionId($subscription->getId());
// 		$subView->setNextBillDate('2015-16-16 00:00:00');
// 		$temp = $subView->getNextBillDate();
// 		//echo Mage::getModel('palorus/subscription')->load($subscription->getId())->getNextBillDate();
// 		//$this->assertEquals('2015-16-16 00:00:00',Mage::getModel('palorus/subscription')->load($subscription->getId())->getNextBillDate());
// 	}
	
// 	public function testGetNumberOfIterations(){
// 		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
// 		$subscription = Mage::getModel('palorus/subscription')->setNumOfIterationsRan('2')->save();
// 		echo $subscription->getNumOfIterationsRan();
// 		$subView->setSubscriptionId($subscription->getId());
// 		echo $subView->getNumOfIterationsRan();
// 		//$this->assertEquals('3',Mage::getModel('palorus/subscription')->load($subscription->getId())->getRunNextIteration());
// 	}

	
	
//	public function testSuspendSubscription()
//	{
		//Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
//		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
//// 		Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
// 		$element = $subView->suspendSubscription(5);
		
// 		echo $element;
	//}
	
// 	public function testSuspendSubscription2()
// 	{
// 		//Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
// 		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
// 		Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
// 		$element = $subView->suspendSubscription(5);
	
// 		echo $element;
// 	}
//  	public function testShowResumeButton(){
// 		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
// 		$subscription = Mage::getModel('palorus/subscription')->setRunNextIteration('1')->save();
// 		$subView->setSubscriptionId($subscription->getId());
// 		$this->assertEquals('3',Mage::getModel('palorus/subscription')->load($subscription->getId())->getRunNextIteration());
    	
// 		$run = $this->getSubscriptionData('run_next_iteration');
//     	$status = $this->getRecyclingData('status');
//     	return (!$run && ($status === 'cancelled' || $status === Null));
//     }
    
	public function testDoNextIteration(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setRunNextIteration('1')->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->setRunNext('1');
	}
	
	
}
