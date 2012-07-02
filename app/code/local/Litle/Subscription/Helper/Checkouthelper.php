<?php

class Litle_Subscription_Helper_Checkouthelper extends Mage_Checkout_Helper_Data
{
    public function getRequiredAgreementIds()
    {
    	if(Mage::getStoreConfig('payment/Subscription/active') == 0 || 
    	   (Mage::getStoreConfig('payment/Subscription/active') == 1 && Mage::getStoreConfig('payment/Subscription/disablesubsagreement') == 1)
    	  )
    	{
    		return parent::getRequiredAgreementIds();
    	}
    	else{
    	   	if (is_null($this->_agreements)) {
         	   if (!Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
         	   		$this->_agreements = array();
            	} else {
                	$this->_agreements = Mage::getModel('checkout/agreement')->getCollection()
                    	->addStoreFilter(Mage::app()->getStore()->getId())
                    	->addFieldToFilter('is_active', 1)
                    	->getAllIds();
            	}
        	}
        	$this->_agreements = array_merge($this->_agreements, array("1001"));
	        return $this->_agreements;
    	}
    	

    }
}
