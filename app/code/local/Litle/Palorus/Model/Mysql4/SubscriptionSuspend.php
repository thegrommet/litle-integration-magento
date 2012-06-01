<?php

  class Litle_Palorus_Model_Mysql4_SubscriptionSuspend extends Mage_Core_Model_Mysql4_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/subscription_suspend', 'suspend_id');
      }
  }