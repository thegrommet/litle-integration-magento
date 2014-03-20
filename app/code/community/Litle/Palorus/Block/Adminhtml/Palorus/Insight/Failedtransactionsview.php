<?php
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Failedtransactionsview extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @var Litle_Palorus_Model_Failedtransactions
     */
    protected $_transaction;
    
    protected function _construct ()
    {
        $this->setTemplate('litle/form/failedtransactions.phtml');
    }

    /**
     * @return Litle_Palorus_Model_Failedtransactions
     */
    public function getFailedTransaction ()
    {
        if ($this->_transaction === null) {
            $this->_transaction = Mage::getModel('palorus/failedtransactions')->load($this->getFailedTransactionsId());
        }
        return $this->_transaction;
    }

    public function updateFailedTransaction ()
    {
        if ($this->getFailedTransaction()->getActive()) {
            $this->setActive(false);
        }
    }

    /**
     * @return int
     */
    public function getFailedTransactionsId ()
    {
        if ($this->hasFailedTransactionsId()) {
            return $this->getData('failed_transactions_id');
        }
        return $this->getRequest()->getParam('failed_transactions_id');
    }

    /**
     * @return string
     */
    public function getFullXml ()
    {
        $orig = $this->getFailedTransaction()->getFullXml();
        $converted = htmlentities($orig);
        $newLinesBecomeBreaks = str_replace("\n", "<br/>", $converted);
        return $newLinesBecomeBreaks;
    }

    /**
     * @return string
     */
    public function getCustomerUrl ()
    {
        $customer = $this->getFailedTransaction()->getCustomer();
        if (!$customer->getId()) {
            return 'Customer was not logged in';
        }
        $url = $this->getUrl('adminhtml/customer/edit', array('id' => $customer->getId()));
        return '<a href=' . $url . '>' . $customer->getId() . '</a>';
    }

    /**
     * @return string
     */
    public function getOrderUrl ()
    {
        $order = $this->getFailedTransaction()->getOrder();
        if (!$order->getId()) {
            return 'No order information available';
        }
        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        return '<a href=' . $url . '>' . $order->getIncrementId() . '</a>';
    }
}