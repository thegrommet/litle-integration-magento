<?php
/**
* Litle Subscription Module
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
* @package    Litle_Subscription
* @copyright  Copyright (c) 2012 Litle & Co.
* @license    http://www.opensource.org/licenses/mit-license.php
* @author     Litle & Co <sdksupport@litle.com> www.litle.com/developers
*/
class Litle_Subscription_Block_Product_View extends Mage_Catalog_Block_Product_View
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getTierPriceHtml($product = null)
	{
		$parentRet = parent::getTierPriceHtml($product);
		$product1 = $this->getProduct();
		return Litle_Subscription_Block_Product_View::_getTierPriceHtml($product1, $parentRet);
	}
	
	static function _getTierPriceHtml($product, $parentRet) {
		$litle_subscription = $product->getAttributeText("litle_subscription");
		if($litle_subscription === "Yes")
		{
			$litle_subs_amount_per_itr = $product->getLitleSubsAmountPerItr();
			$litle_subs_num_of_itrs = $product->getLitleSubsNumOfItrs();
			$litle_subs_itr_len = $product->getAttributeText("litle_subs_itr_len");
			$iterationUnit = "";
			if($litle_subs_itr_len === "Daily")
			{
				$iterationUnit = "$litle_subs_num_of_itrs days";
			}else if($litle_subs_itr_len === "Weekly")
			{
				$iterationUnit = "$litle_subs_num_of_itrs weeks";
			}else if($litle_subs_itr_len === "Bi-Weekly")
			{
				$iterationUnit = $litle_subs_num_of_itrs*2 . " weeks";
			}else if($litle_subs_itr_len === "Semi-Monthly")
			{
				$iterationUnit = $litle_subs_num_of_itrs/2 . " months";
			}else if($litle_subs_itr_len === "Monthly")
			{
				$iterationUnit = "$litle_subs_num_of_itrs months";
			}else if($litle_subs_itr_len === "Semi-Annually")
			{
				$iterationUnit = $litle_subs_num_of_itrs/2 . " years";
			}else if($litle_subs_itr_len === "Annually"){
				$iterationUnit = "$litle_subs_num_of_itrs years";
			}else
			{
				$iterationUnit = $litle_subs_num_of_itrs;
			}
				
			$litleAdditions = "Subscription amount: $" . round($litle_subs_amount_per_itr,2). " " . $litle_subs_itr_len . " for " . $iterationUnit;
			return $litleAdditions . $parentRet;
		}
		return $parentRet;
	}
}
