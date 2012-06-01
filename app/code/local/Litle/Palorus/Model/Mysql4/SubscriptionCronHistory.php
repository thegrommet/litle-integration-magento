<?php

  class Litle_Palorus_Model_Mysql4_SubscriptionCronHistory extends Mage_Core_Model_Mysql4_Abstract
  {
      protected function _construct()
      {
          $this->_init('palorus/subscription_cron_history', 'cron_history_id');
      }
  }