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
class Litle_CreditCard_Model_Validatehttp extends Mage_Core_Model_Config_Data
{
	public function getFieldsetDataValue($key)
	{
		$data = $this->_getData('fieldset_data');
		return (is_array($data) && isset($data[$key])) ? $data[$key] : null;
	}
	
	public function getEcheckConfigData($fieldToLookFor, $store = NULL)
	{
		$returnFromThisModel = Mage::getStoreConfig('payment/LEcheck/' . $fieldToLookFor);
		if( $returnFromThisModel == NULL )
			$returnFromThisModel = parent::getConfigData($fieldToLookFor, $store);
	
		return $returnFromThisModel;
	}
	
	public function getSubscriptionConfigData($fieldToLookFor, $store = NULL)
	{
		$returnFromThisModel = Mage::getStoreConfig('payment/Subscription/' . $fieldToLookFor);
		if( $returnFromThisModel == NULL )
			$returnFromThisModel = parent::getConfigData($fieldToLookFor, $store);
	
		return $returnFromThisModel;
	}
	
	function save(){
		if ($this->getFieldsetDataValue('active') || $this->getEcheckConfigData('active') || $this->getSubscriptionConfigData('active'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_PROXY, $this->getFieldsetDataValue('proxy'));
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'Test Connectivity');
			curl_setopt($ch, CURLOPT_URL, $this->getFieldsetDataValue('url'));
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
			curl_setopt($ch,CURLOPT_TIMEOUT,'5');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($ch);

			if (! $output){
				Mage::throwException('Error connecting to Litle. Make sure your HTTP configuration settings are correct.');
			}
			else
			{
				curl_close($ch);
			}
			
			return parent::save();
		}
	}
}
