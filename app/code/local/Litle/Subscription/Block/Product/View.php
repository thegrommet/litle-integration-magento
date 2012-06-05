<?php
class Litle_Subscription_Block_Product_View extends Mage_Catalog_Block_Product_View
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getTierPriceHtml($product = null)
	{
		$parentRet = parent::getTierPriceHtml($product);
		$litleAdditions = "Litle Rocks!";
		//$product1 = Mage::helper("catalog/product")->getProduct($this->getProductId(), null);
		//$product1 = $this->getProduct();
		//$attributeValue = $product->getAttributeText($attributeName);
		$product1 = parent::getProduct();
		$litle_subscription = $product1->getAttributeText("litle_subscription");
		$litle_subs_amount_per_itr = $product1->getAttributeText("litle_subs_amount_per_itr");
		$litle_subs_num_of_itrs = $product1->getAttributeText("litle_subs_num_of_itrs");
		$litle_subs_itr_len = $product1->getAttributeText("litle_subs_itr_len");
		$litleAdditions = "Subscription amount: " . $litle_subs_amount_per_itr . " per: " . $litle_subs_itr_len . " for " . $litle_subs_num_of_itrs;
		$allAttributes = $product1->getAttributes();
		//echo $allAttributes['litle_subscription'];
		foreach($allAttributes as $attribute)
 			echo get_class($attribute);
		
 		//echo $attribute;
		//$_helper = Mage::helper('catalog/output')->getProductAttribute();
		return "productId: " . $product1->getId() . $litleAdditions . $parentRet;
	}
}
