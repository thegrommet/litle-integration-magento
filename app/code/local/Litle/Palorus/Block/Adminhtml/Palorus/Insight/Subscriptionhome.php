<?php
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome extends Mage_Adminhtml_Block_Widget_Form
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('payment/form/subscriptionsviewer.phtml');
	}

	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	private function getSubcription(){
		$subscriptionId = $this->getSubscriptionId();
		return Mage::getModel('palorus/subscription')->getCollection();
	}

	public function getSubscriptionTable()
	{
		$collection = $this->getSubcription();
		$index=0;
		foreach ($collection as $order){
			$row = $order->getData();
			$table[$index] = $row;
			$index = $index+1;
		}
		return $table;
	}

	public function getProductName($subscriptionRow)
	{
		$productName = $subscriptionRow['product_id'];
		$product = Mage::getModel('catalog/product')->load($productName);
		return $product->getName();
	}
	
	public function getRowUrl($row)
	{
		$subscriptionId = $row['subscription_id'];
		return $this->getUrl('palorus/adminhtml_myform/subscriptionview/', array('subscription_id' => $subscriptionId));
	}

}
