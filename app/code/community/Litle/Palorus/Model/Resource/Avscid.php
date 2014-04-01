<?php

  class Litle_Palorus_Model_Resource_Avscid extends Mage_Core_Model_Resource_Db_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/avscid', 'avs_cid_id');
      }
  }