<?php
/**
* Litle Palorus Module
*
* NOTICE OF LICENSE
*
* Copyright (c) 2012 Litle & Co.
*
* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following
* conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
* OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
* OTHER DEALINGS IN THE SOFTWARE.
*
* @category   Litle
* @package    Litle_Palorus
* @copyright  Copyright (c) 2012 Litle & Co.
* @license    http://www.opensource.org/licenses/mit-license.php
* @author     Litle & Co <sdksupport@litle.com> www.litle.com/developers
*/
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Tab
extends Mage_Adminhtml_Block_Widget_Grid
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

	/**
	 * Set the template for the block
	 *
	 */
	public function _construct()
	{
		parent::_construct();
		$this->setId('litle_customer_orders_grid');
		$this->setDefaultSort('order_number', 'desc');
		$this->setUseAjax(true);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection()
	{
		$customerId = Mage::registry('current_customer')->getId();
		$collection = Mage::getModel('palorus/insight')
			->getCollection()
			->addFieldToFilter('customer_id',$customerId);
			
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('order_number', array(
                'header'    => 'Order Number',
                'width'     => '100',
                'index'     => 'order_number',
                'sortable'		=> false,
		));
		$this->addColumn('last', array(
    	        'header'    => 'Last 4',
	            'width'     => '100',
                'index'     => 'last',
                'sortable'		=> false,
		));
		$this->addColumn('order_amount', array(
               'header'    => 'Order Amount',
               'width'     => '100',
               'index'     => 'order_amount',
               'sortable'		=> false,
		));
		$this->addColumn('affluence', array(
               'header'    => 'Affluence',
               'width'     => '100',
               'index'     => 'affluence',
               'sortable'		=> false,
		));
		$this->addColumn('issuing_country', array(
               'header'    => 'Issuing Country',
               'width'     => '100',
               'index'     => 'issuing_country',
               'sortable'		=> false,
		));
		$this->addColumn('prepaid_card_type', array(
               'header'    => 'Prepaid Card Type',
               'width'     => '100',
               'index'     => 'prepaid_card_type',
               'sortable'		=> false,
		));
		$this->addColumn('funding_source', array(
               'header'    => 'Funding Source',
               'width'     => '100',
               'index'     => 'funding_source',
               'sortable'		=> false,
		));
		$this->addColumn('available_balance', array(
               'header'    => 'Available Balance',
               'width'     => '100',
               'index'     => 'available_balance',
               'sortable'		=> false,
		));
		$this->addColumn('reloadable', array(
               'header'    => 'Reloadable',
               'width'     => '100',
               'index'     => 'reloadable',
               'sortable'		=> false,
		));
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/sales_order/view', array('order_id' => $row->getOrderId()));
	}
	
	public function getGridUrl()
	{
// 		Mage::log("Get grid url");
// 		return $this->getUrl('*/*/orders', array('_current' => true));
	}

	/**
	 * Retrieve the label used for the tab relating to this block
	 *
	 * @return string
	 */
	public function getTabLabel()
	{
		return $this->__('Litle & Co. Customer Insight');
	}

	/**
	 * Retrieve the title used by this tab
	 *
	 * @return string
	 */
	public function getTabTitle()
	{
		return $this->__('Click here to view Litle & Co. Customer Insight');
	}

	/**
	 * Determines whether to display the tab
	 * Add logic here to decide whether you want the tab to display
	 *
	 * @return bool
	 */
	public function canShowTab()
	{
		return true;
	}

	/**
	 * Stops the tab being hidden
	 *
	 * @return bool
	 */
	public function isHidden()
	{
		return false;
	}

	/**
	 * AJAX TAB's
	 * If you want to use an AJAX tab, uncomment the following functions
	 * Please note that you will need to setup a controller to recieve
	 * the tab content request
	 *
	 */
	/**
	 * Retrieve the class name of the tab
	 * Return 'ajax' here if you want the tab to be loaded via Ajax
	 *
	 * return string
	 */
// 	   public function getTabClass()
// 	   {
// 	       return 'ajax';
// 	   }

// 	/**
// 	 * Determine whether to generate content on load or via AJAX
// 	 * If true, the tab's content won't be loaded until the tab is clicked
// 	 * You will need to setup a controller to handle the tab request
// 	 *
// 	 * @return bool
// 	 */
// 	   public function getSkipGenerateContent()
// 	   {
// 	       //return true;
// 	       return false;
// 	   }

// 	/**
// 	 * Retrieve the URL used to load the tab content
// 	 * Return the URL here used to load the content by Ajax
// 	 * see self::getSkipGenerateContent & self::getTabClass
// 	 *
// 	 * @return string
// 	 */
// 	    public function getTabUrl()
// 	  {
// 	  	return null;
// 	  	//http://127.0.0.1/magento/index.php/palrous/adminhtml_myform/insight/
// 	  	//http://127.0.0.1/magento/index.php/palorus/adminhtml_myform/activity/
// 	  		//return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . 'palrous/adminhtml_myform/';
// 	  		//return 'http://127.0.0.1/magento/index.php/palorus/adminhtml_myform/activity/';
// 	   }

}