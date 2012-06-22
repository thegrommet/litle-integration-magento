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
extends Mage_Adminhtml_Block_Widget_Grid{

	/**
	 * Set the template for the block
	 *
	 */
	public function _construct()
	{
		parent::_construct();
		$this->_headerText = Mage::helper('palorus')->__('Litle Subscription Home');
		
		echo ($this->_headerText);
		//$this->setTitle('Litle Subscription Home');
		
		$this->setDefaultSort('subscription_id', 'desc');
		$this->setUseAjax(true);
		$this->setFilterVisibility(false);
		//$this->getHeaderText();
	}
	

	
	protected function _prepareCollection()
	{

		$collection = Mage::getModel('palorus/subscription')
			->getCollection();
		foreach ($collection as $order){
			$productId = $order->getData();
			$productName = $productId['product_id'];
			$product = Mage::getModel('catalog/product')->load($productName);
			$name = $product->getName();
			$order->setData('name', $name);
		}
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('customer_id', array(
				'header'    => 'Customer ID',
				'width'     => '100',
				'index'     => 'customer_id',
				'sortable'		=> false,
		));
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
		$this->addColumn('name', array(
		               'header'    => 'Product Name',
		               'width'     => '100',
		               'index'     => 'name',
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
		return $this->getUrl('palorus/adminhtml_myform/subscriptionview/', array('subscription_id' => $row->getSubscriptionId()));
	}
	
}