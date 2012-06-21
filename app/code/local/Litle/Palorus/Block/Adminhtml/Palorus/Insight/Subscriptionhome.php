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
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionhome
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

		$collection = Mage::getModel('palorus/subscription')
			->getCollection();
			
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('subscription_id', array(
                'header'    => 'Subscription ID',
                'width'     => '100',
                'index'     => 'subscription_id',
                'sortable'		=> false,
		));
		$this->addColumn('product_id', array(
    	        'header'    => 'Product Id',
	            'width'     => '100',
                'index'     => 'product_id',
                'sortable'		=> false,
		));

		$this->addColumn('start_date', array(
				'header'    => 'Start Date',
				'width'     => '100',
				'index'     => 'start_date',
				'sortable'		=> false,
		));
		$this->addColumn('iteration_length', array(
               'header'    => 'Billing Cycle Period',
               'width'     => '100',
               'index'     => 'iteration_length',
               'sortable'		=> false,
		));
		$this->addColumn('next_bill_date', array(
               'header'    => 'Next Bill Date',
               'width'     => '100',
               'index'     => 'next_bill_date',
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
		return $this->__('Litle & Co. Subscription');
	}

	/**
	 * Retrieve the title used by this tab
	 *
	 * @return string
	 */
	public function getTabTitle()
	{
		return $this->__('Click here to view Litle & Co. Subscription');
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


}