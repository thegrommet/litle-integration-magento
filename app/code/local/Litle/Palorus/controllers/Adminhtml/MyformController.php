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
class Litle_Palorus_Adminhtml_MyformController extends Mage_Adminhtml_Controller_Action
{

    public function activityAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/activity');
    }
    
    public function authorizationAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/authorization');
    }
    
    public function exchangeAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/exchange');
    }
    
    public function binlookupAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/binlookup');
    }
    
    public function sessionAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/operator/PresenterSessions.cgi?reportAction=LoadDefault');
    }
    
    public function settlementAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/settlement');
    }
    
    public function searchAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/transactions/search');
    }
    
    public function summaryAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/transactions/summary');
    }
    
    public function dashboardauthorizationAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/dashboards/authorization');
    }
    
    public function dashboardfrauddetectionAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/dashboards/fraudDetection');
    }
    
    public function dashboardpostdepositfraudimpactAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/dashboards/postDepositFraud');
    }
    
    public function chargebackSearchAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/chargebacks/search');
    }
    
    public function chargebackReportAction()
    {
    	$this->_redirectUrl(Mage::helper('palorus')->getBaseUrl() . '/ui/reports/chargebacks/compliance');
    }  

    public function subscriptionAction()
    {
    	Mage::getSingleton("palorus/subscription")->callFromCron();
    }
    
    public function litleSubscriptionAction()
    {
     	$this->loadLayout();
    	$block = $this->getLayout()->createBlock('palorus/adminhtml_palorus_insight_subscriptionhome');
		$this->getLayout()->getBlock('content')->append($block);
    	$this->renderLayout();
    }
    
    public function subscriptionviewAction()
    {
    	$this->loadLayout();
    	$block = $this->getLayout()->createBlock('palorus/adminhtml_palorus_insight_subscriptionview');
    	$var = $this->getRequest()->getParam('active');
    	$this->getLayout()->getBlock('content')->append($block);
    	$block->setActive($var);
    	$this->renderLayout();
    }
}