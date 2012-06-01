<?php

  class Litle_Palorus_Model_Mysql4_SubscriptionIterationRef extends Mage_Core_Model_Mysql4_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/subscription_iteration_refn', 'iteration_ref_id');
      }
  }