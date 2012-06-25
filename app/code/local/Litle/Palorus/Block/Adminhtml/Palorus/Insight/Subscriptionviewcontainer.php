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
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionviewcontainer
extends Mage_Adminhtml_Block_Widget_Form_Container{

	public function __construct()
	{
		parent::__construct();
	}

	public function getHeaderText()
	{
		$collection = Mage::getModel('palorus/subscription')
			->getCollection()
			->addFieldToFilter('customer_id',$customerId);
		$collections = $collection->getData();
		$subscriptionId = $productId['subscription_Id'];
		return 'Subscription # ' . $subscriptionId;
	}

	public function getHeaderHtml()
	{
		return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>';
	}

}
