<?php

  class Litle_Palorus_Model_Mysql4_SubscriptionSuspend extends Mage_Core_Model_Mysql4_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/subscriptionSuspend', 'suspend_id');
      }
  }