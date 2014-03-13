<?php

  class Litle_Palorus_Model_Resource_Failedtransactions extends Mage_Core_Model_Resource_Db_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/failedtransactions', 'failed_transactions_id');
      }
  }