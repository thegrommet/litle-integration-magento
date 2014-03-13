<?php

  class Litle_Palorus_Model_Resource_Insight extends Mage_Core_Model_Resource_Db_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/insight', 'customer_insight_id');
      }
  }