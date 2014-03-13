<?php

class Litle_Palorus_Model_Mysql4_Vault extends Mage_Core_Model_Mysql4_Abstract
{

	protected function _construct()
	{
		$this->_init('palorus/vault', 'vault_id');
	}

	/**
	 * Sets the created and modified date attributes.
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return Litle_Palorus_Model_Mysql4_Vault
	 */
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if (! $object->getId()) {
			$object->setCreated(now());
		}
		$object->setUpdated(now());

		return parent::_beforeSave($object);
	}

    /**
     * Load object only if owned by the provided customer.
     *
     * @param Litle_Palorus_Model_Vault $object
     * @param int $vaultId
     * @param type $customerId
     * @return \Litle_Palorus_Model_Mysql4_Vault
     */
    public function loadByCustomerId (Litle_Palorus_Model_Vault $object, $vaultId, $customerId)
    {
        $read = $this->_getReadAdapter();
		$select = $read->select()
			->from($this->getMainTable())
			->where($this->getIdFieldName() . ' = ?', $vaultId)
			->where('customer_id = ?', $customerId);

		$data = $read->fetchRow($select);
		if ($data) {
			$object->setData($data);
		}
		$this->unserializeFields($object);
		$this->_afterLoad($object);

		return $this;
    }
}
