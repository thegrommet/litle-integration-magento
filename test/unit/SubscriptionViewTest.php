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
		$collection = Mage::getModel('palorus/subscription')
		->getCollection();
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
		
		$collection = Mage::getModel('palorus/recycling')
		->getCollection();
		foreach($collection as $productToDelete) {
			$productToDelete->delete();
		}
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
	
	public function testSetGetNextBillDate(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setNextBillDate('2015-12-12 00:00:00')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->setNextBillDate('2015-11-11 00:00:00');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('2015-11-11 00:00:00', $data['next_bill_date']);
		$this->assertEquals('2015-11-11 00:00:00', $subView->getNextBillDate());
	}
	
	public function testGetNumberOfIterationsRan(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setNumOfIterationsRan('2')->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('2',$subView->getNumOfIterationsRan());
	}

	public function testSetGetNumberOfIterations(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setNumOfIterations('11')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->setNumOfIterations('12');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('12', $data['num_of_iterations']);
		$this->assertEquals('12', $subView->getNumOfIterations());
	}
	
	public function testSetGetIterationLength(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setIterationsLength('Daily')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->setIterationLength('Weekly');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('Weekly', $data['iteration_length']);
		$this->assertEquals('Weekly', $subView->getIterationLength());
	}
	
	public function testGetStartDate(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setStartDate('2015-12-06 00:00:00')->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('2015-12-06 00:00:00',$subscription->getStartDate());
	}
	
	public function testSetGetSubcriptionAmount(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setAmount('25000')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->setSubscriptionAmount('50000');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('5000000', $data['amount']);
		$this->assertEquals('50000', $subView->getSubscriptionAmount());
	}
	
	public function testGetInitialFees(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setInitialFees('1200')->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('12',$subView->getInitialFees());
	}
	
	public function testGetNextRecycleAttemptNA(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(1)
		->setRunNextIteration(1)
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('N/A',$subView->getNextRecycleAttempt());
	}
	
	public function testGetNextRecycleAttemptDate(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(1)
		->setRunNextIteration(0)
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->setToRunDate('2015-12-06 00:00:00')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('2015-12-06 00:00:00',$subView->getNextRecycleAttempt());
	}

	public function testSetGetIsActive(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setActive('0')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->setActive('1');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('1', $data['active']);
		$this->assertEquals('1', $subView->getActive());
	}
	
	public function testGetIsRecyclingNO(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$recycling = Mage::getModel('palorus/recycling')->setStatus(0)->save();
		$subscription = Mage::getModel('palorus/subscription')->load($recycling->getId())->setActive(0)->save();
		$subscription->setRunNextIteration(1)->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('No',$subView->getIsRecycling());
	}
	
	public function testGetIsRecyclingYes(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(1)
		->setRunNextIteration(0)
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$this->assertEquals('Yes',$subView->getIsRecycling());
	}
	
	public function testUpdateSubscriptionAmount(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setAmount('25000')
		->setActive('1')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->updateSubscription('50000','','');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('5000000', $data['amount']);
		$this->assertEquals('50000', $subView->getSubscriptionAmount());
	}
	
	public function testUpdateSubscriptionIterationLengthAndAmount(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setIterationLength('Weekly')
		->setActive('1')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->updateSubscription('50000','Monthly','');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('Monthly', $data['iteration_length']);
		$this->assertEquals('Monthly', $subView->getIterationLength());
	}
	
	public function testUpdateSubscriptionIterationLengthAndAmountandNumIterations(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setNumOfIterations('12')
		->setActive('1')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->updateSubscription('50000','Monthly','15');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('15', $data['num_of_iterations']);
		$this->assertEquals('15', $subView->getNumOfIterations());
		$this->assertEquals('Monthly', $data['iteration_length']);
		$this->assertEquals('Monthly', $subView->getIterationLength());
	}
	
	public function testUpdateSubscriptionIterationLengthAndNotAmountandNumIterations(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setNumOfIterations('12')
		->setIterationLength('Weekly')
		->setAmount('25000')
		->setActive('1')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->updateSubscription('','Monthly','15');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('15', $data['num_of_iterations']);
		$this->assertEquals('15', $subView->getNumOfIterations());
		$this->assertEquals('Monthly', $data['iteration_length']);
		$this->assertEquals('Monthly', $subView->getIterationLength());
		$this->assertEquals('25000', $data['amount']);
		$this->assertEquals('250', $subView->getSubscriptionAmount());
	}
	
	public function testUpdateSubscriptionNotIterationLengthAndAmountAndNotNumIterations(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setNumOfIterations('12')
		->setIterationLength('Weekly')
		->setAmount('25000')
		->setActive('1')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->updateSubscription('5000','','');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('12', $data['num_of_iterations']);
		$this->assertEquals('12', $subView->getNumOfIterations());
 		$this->assertEquals('Weekly', $data['iteration_length']);
 		$this->assertEquals('Weekly', $subView->getIterationLength());
		$this->assertEquals('500000', $data['amount']);
		$this->assertEquals('5000', $subView->getSubscriptionAmount());
	}

	public function testSuspendSubscription(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setNextBillDate('2015-05-05')->save();
		$subscription->setIterationLength('Daily')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->suspendSubscription('5');
		$data = Mage::getModel('palorus/subscription')->load($subscription->getId())->getData();
		$this->assertEquals('2015-05-10 00:00:00', $data['next_bill_date']);
		$this->assertEquals('2015-05-10 00:00:00', $subView->getNextBillDate());
	}
    
	public function testDoNextIteration(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')->setRunNextIteration('0')->save();
		$subView->setSubscriptionId($subscription->getId());
		$subView->doNextIteration();
		$this->assertEquals('1',Mage::getModel('palorus/subscription')->load($subscription->getId())->getRunNextIteration());
	}
	
	public function testGetSubscriptionStatusMessageErrorRecyclingYes(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(1)
		->setRunNextIteration(0)
		->setNextBillDate('2015-06-06 00:00:00')
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->setToRunDate('2015-12-12 00:00:00')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$message = $subView->getSubscriptionStatusMessage();
		$this->assertEquals("error-msg",$message[0]);
		$this->assertEquals("Subscription is in bad condition.",$message[1]);
	}
	
	public function testGetSubscriptionStatusMessageWarning(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(1)
		->setRunNextIteration(0)
		->setNextBillDate('2015-12-12 00:00:00')
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->setToRunDate('2015-06-06 00:00:00')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$message = $subView->getSubscriptionStatusMessage();
		$this->assertEquals("warning-msg",$message[0]);
		$this->assertEquals("Subscription is in recycling",$message[1]);
	}
	
	public function testGetSubscriptionStatusMessageSuccess(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(1)
		->setRunNextIteration(1)
		->setNextBillDate('2015-12-12 00:00:00')
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->setToRunDate('2015-06-06 00:00:00')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$message = $subView->getSubscriptionStatusMessage();
		$this->assertEquals("success-msg",$message[0]);
		$this->assertEquals("Subscription is in good condition.",$message[1]);
	}
	
	public function testGetSubscriptionStatusMessageErrorRecyclingNo(){
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subView = new Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview();
		$subscription = Mage::getModel('palorus/subscription')
		->setActive(0)
		->setRunNextIteration(0)
		->setNextBillDate('2015-06-06 00:00:00')
		->save();
		$recycling = Mage::getModel('palorus/recycling')->setSubscriptionId($subscription->getId())
		->setStatus('waiting')
		->setToRunDate('2015-12-12 00:00:00')
		->save();
		$subView->setSubscriptionId($subscription->getId());
		$message = $subView->getSubscriptionStatusMessage();
		$this->assertEquals("error-msg",$message[0]);
		$this->assertEquals("Last Transaction Failed.",$message[1]);
	}

}
