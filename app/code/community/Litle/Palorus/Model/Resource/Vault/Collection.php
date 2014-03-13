<?php

class Litle_Palorus_Model_Resource_Vault_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('palorus/vault');
	}

	/**
	 * Get vault records filtered by customer object
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Litle_Palorus_Model_Resource_Vault_Collection
	 */
	public function addCustomerFilter(Mage_Customer_Model_Customer $customer)
	{
		$this->addFieldToFilter('customer_id', $customer->getId());
		return $this;
	}
}
