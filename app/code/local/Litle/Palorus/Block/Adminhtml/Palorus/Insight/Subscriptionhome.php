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
extends Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionparent{

	/**
	 * Set the template for the block
	 *
	 */
	public function _construct()
	{
		parent::_construct();
//		$this->_headerText = Mage::helper('palorus')->__('Litle Subscription Home');
//		echo ($this->_headerText);
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
			$amount = money_format('%i', $productId['amount']/100);
			$order->setData('price', '$'.$amount);
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
		return parent::_prepareColumns();
	}

	
}