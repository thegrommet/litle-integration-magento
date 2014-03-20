<?php

class Litle_Palorus_Model_Resource_Failedtransactions extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_writeConnection = null;

    protected function _construct ()
    {
        $this->_init('palorus/failedtransactions', 'failed_transactions_id');
    }

    /**
     * @param Varien_Db_Adapter_Interface $connection
     * @return Litle_Palorus_Model_Resource_Failedtransactions
     */
    public function setWriteConnection (Varien_Db_Adapter_Interface $connection)
    {
        $this->_writeConnection = $connection;
        return $this;
    }

    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        if ($this->_writeConnection) {
            return $this->_writeConnection;
        }
        return $this->_getConnection('write');
    }
}