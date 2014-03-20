<?php

class Litle_Palorus_Model_Failedtransactions extends Mage_Core_Model_Abstract
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    protected function _construct ()
    {
        $this->_init('palorus/failedtransactions');
    }
    
    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder ()
    {
        if ($this->_order === null) {
            $this->_order = Mage::getModel('sales/order');
            if ($this->getOrderId()) {
                $this->_order->load($this->getOrderId());
            }
        }
        return $this->_order;
    }
    
    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer ()
    {
        if ($this->_customer === null) {
            $this->_customer = Mage::getModel('customer/customer');
            if ($this->getCustomerId()) {
                $this->_customer->load($this->getCustomerId());
            }
        }
        return $this->_customer;
    }
}