<?php
/**
* Litle CreditCard Module
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
* @package    Litle_CreditCard
* @copyright  Copyright (c) 2012 Litle & Co.
* @license    http://www.opensource.org/licenses/mit-license.php
* @author     Litle & Co <sdksupport@litle.com> www.litle.com/developers
*/
class Litle_CreditCard_Model_ValidateMerchantId extends Mage_Core_Model_Config_Data
{
	public function getFieldsetDataValue($key)
	{
		$data = $this->_getData('fieldset_data');
		return (is_array($data) && isset($data[$key])) ? $data[$key] : null;
	}

	function save(){
		if ($this->getFieldsetDataValue('active'))
		{
			$merchantId = $this->getFieldsetDataValue("merchant_id");
			Litle_CreditCard_Model_ValidateMerchantId::validate($merchantId);
		}
		return parent::save();
	}
	
	public static function validate($merchantId) {
		$string2Eval = 'return array' . $merchantId . ';';
		$currency = "USD";//assumed that the base currency is USD
		@$merchant_map = eval($string2Eval);
		
		if(!is_array($merchant_map)){
			Mage::throwException('Merchant ID must be of the form ("Currency" => "Code"), '. PHP_EOL . 'i.e. ("USD" => "101","GBP" => "102")');
		}
		if(empty($merchant_map[$currency])){
			Mage::throwException('Please Make sure that the Base Currency: ' . $currency . ' is in the Merchant ID Array');
		}
	}
}
