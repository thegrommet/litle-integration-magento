<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer addresses forms
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('payment/form/subscription.phtml');
    }


    public function getSubscriptionData(string $field)
    {
    	$subscriptionId = $this->getSubscriptionId();
    	$collection = Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id',$subscriptionId);
    	foreach ($collection as $order){
    		$row = $order->getData();
    		return $row[$field];
    	}
    }
    
    public function getSubscriptionName()
    {
    	$subscriptionId = $this->getSubscriptionId();
    	$collection = Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id',$subscriptionId);
    	foreach ($collection as $order){
    		$row = $order->getData();
    		$productName = $row['product_id'];
    		$product = Mage::getModel('catalog/product')->load($productName);
    		return $product->getName();
    	}
    }
    
    
     public function getSubscriptionId(){
     	$url = $this->helper("core/url")->getCurrentUrl();
     	$stringAfterSubscriptionId = explode('subscription_id/', $url);
     	$stringBeforeKey = explode('/key', $stringAfterSubscriptionId[1]);
     	return $stringBeforeKey[0];
     }
     
     public function getRecyclingData(string $field){
     	$subscriptionId = $this->getSubscriptionId();
    	$collection = Mage::getModel('palorus/recycling')->getCollection()->addFieldToFilter('subscription_id',$subscriptionId);
    	foreach ($collection as $order){
    		$row = $order->getData();
    		return $row[$field];
    	}
     }
     
     public function getIsRecycling(){
     	$runNextIteration = $this->getSubscriptionData('run_next_iteration');
     	$active = $this->getSubscriptionData('active');
     	if(!$runNextIteration && $active){
     		return "Yes";
     	}else{
     		return "No";
     	}
     }

    /**
     * Check block is readonly.
     *
     * @return boolean
     */
    public function isReadonly()
    {
    	return false;
    }

}
