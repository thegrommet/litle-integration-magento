<?php

class Litle_Palorus_Model_SubscriptionCronHistory extends Mage_Core_Model_Abstract
{
	protected $_model = NULL;

	protected function _construct()
	{
		$this->_model = 'palorus/subscriptionCronHistory';
		$this->_init($this->_model);
	}

}