<?php

class Litle_Palorus_Model_Resource_Failedtransactions_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('palorus/failedtransactions');
	}

}