<?php

class Litle_Subscription_Block_Agreements extends Mage_Checkout_Block_Agreements
{
    public function getAgreements()
    {
    	if(Mage::getStoreConfig('payment/Subscription/active') == 0 || 
    	   (Mage::getStoreConfig('payment/Subscription/active') == 1 && Mage::getStoreConfig('payment/Subscription/disablesubsagreement') == 1)
    	  )
    	{
    		return parent::getAgreements();
    	}
    	else{
    		$litleAgreement = new Varien_Object();
	    	$litleAgreement->setAgreementId(1001);
	    	$litleAgreement->setId(1001);
	    	$litleAgreement->setName("Subscription Agreement");
	    	$litleAgreement->setContent("bleep blop tip top");
	    	$litleAgreement->setContentHeight(NULL);
	    	$litleAgreement->setCheckboxText("I agree");
	    	$litleAgreement->setIsActive(1);
	    	$litleAgreement->setIsHtml(0);
	    	
			if (!$this->hasAgreements()) {
	        	if (!Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
	        		$agreements = new Varien_Data_Collection();
	            } else {
	            	$agreements = Mage::getModel('checkout/agreement')->getCollection()
	                    ->addStoreFilter(Mage::app()->getStore()->getId())
	                    ->addFieldToFilter('is_active', 1);
	            }
	           	$agreements->addItem($litleAgreement);
	            $this->setAgreements($agreements);
	        }
	        return $this->getData('agreements');
    	}
    }
}
