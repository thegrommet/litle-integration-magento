<?php

class Litle_Subscription_Model_Product extends Mage_Catalog_Model_Product
{
	// over-riding base class getStatus method.
	// If Subscription Payment Method is disabled, then get the litle_subscription attribute value.
	// If the value is "yes", then return false, if not, let the regular course take place.
    public function getStatus()
    {
        if (is_null($this->_getData('status'))) {
            $this->setData('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        }
        
        //Mage::log("Subscription Payment Model: " . Mage::getStoreConfig('payment/Subscription/active'));
        if(Mage::getStoreConfig('payment/Subscription/active') == 0)
        {
			$product = Mage::helper("catalog/product")->getProduct($this->getId(), null);
			$attributeValue = $product->getAttributeText('litle_subscription');        	
	        //Mage::log("Product ID: " . $this->getId());
			//Mage::log("litle subscription value: " . $attributeValue);
			if( $attributeValue === "Yes" )
				return false;
        }
        
        return $this->_getData('status');
    }
}
