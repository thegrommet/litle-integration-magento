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
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview
extends Mage_Adminhtml_Block_Widget_Form{

	public function __construct()
	{
		parent::__construct();
		$this->initForm();
	}

	public function initForm()
	{
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('_account');
		$form->setFieldNameSuffix('account');

		//        $customer = Mage::registry('current_customer');
		$subscriptionId = Mage::registry('current_subscription');

		/* @var $subscriptionForm Mage_Customer_Model_Form */
		$subscriptionForm = Mage::getModel('palorus/subscription');
		$subscriptionForm->setEntity($subscriptionId)
		->setFormCode('adminhtml_subscription');
		
		$customerForm = Mage::getModel('customer/form');
		//var_dump($customerForm);

		$fieldset = $form->addFieldset('base_fieldset',
		array('legend'=>Mage::helper('customer')->__('Subscription Information'))
		);

		$collection = Mage::getModel('palorus/subscription')
		->getCollection()->addFieldToFilter('subscription_id', 1);

		         $attributes = $subscriptionForm->getAttributes();
		         //var_dump($attributes);
		//         foreach ($attributes as $attribute) {
		//             $attribute->unsIsVisible();
		//         }
		//         $this->_setFieldset($collection, $fieldset);

		//         if ($customer->getId()) {
		//             $form->getElement('website_id')->setDisabled('disabled');
		//             $form->getElement('created_in')->setDisabled('disabled');
		//         } else {
		//             $fieldset->removeField('created_in');
		//         }

		//         $customerStoreId = null;
		//         if ($customer->getId()) {
		//             $customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
		//         }

		//         $prefixElement = $form->getElement('prefix');
		//         if ($prefixElement) {
		//             $prefixOptions = $this->helper('customer')->getNamePrefixOptions($customerStoreId);
		//             if (!empty($prefixOptions)) {
		//                 $fieldset->removeField($prefixElement->getId());
		//                 $prefixField = $fieldset->addField($prefixElement->getId(),
		//                     'select',
		//                     $prefixElement->getData(),
		//                     $form->getElement('group_id')->getId()
		//                 );
		//                 $prefixField->setValues($prefixOptions);
		//                 if ($customer->getId()) {
		//                     $prefixField->addElementValues($customer->getPrefix());
		//                 }

		//             }
		//         }

		//         $suffixElement = $form->getElement('suffix');
		//         if ($suffixElement) {
		//             $suffixOptions = $this->helper('customer')->getNameSuffixOptions($customerStoreId);
		//             if (!empty($suffixOptions)) {
		//                 $fieldset->removeField($suffixElement->getId());
		//                 $suffixField = $fieldset->addField($suffixElement->getId(),
		//                     'select',
		//                     $suffixElement->getData(),
		//                     $form->getElement('lastname')->getId()
		//                 );
		//                 $suffixField->setValues($suffixOptions);
		//                 if ($customer->getId()) {
		//                     $suffixField->addElementValues($customer->getSuffix());
		//                 }
		//             }
		//         }

		//         if ($customer->getId()) {
		//             if (!$customer->isReadonly()) {
		//                 // add password management fieldset
		//                 $newFieldset = $form->addFieldset(
		//                     'password_fieldset',
		//                     array('legend'=>Mage::helper('customer')->__('Password Management'))
		//                 );
		//                 // New customer password
		//                 $field = $newFieldset->addField('new_password', 'text',
		//                     array(
		//                         'label' => Mage::helper('customer')->__('New Password'),
		//                         'name'  => 'new_password',
		//                         'class' => 'validate-new-password'
		//                     )
		//                 );
		//                 $field->setRenderer($this->getLayout()->createBlock('adminhtml/customer_edit_renderer_newpass'));

		//                 // prepare customer confirmation control (only for existing customers)
		//                 $confirmationKey = $customer->getConfirmation();
		//                 if ($confirmationKey || $customer->isConfirmationRequired()) {
		//                     $confirmationAttribute = $customer->getAttribute('confirmation');
		//                     if (!$confirmationKey) {
		//                         $confirmationKey = $customer->getRandomConfirmationKey();
		//                     }
		//                     $element = $fieldset->addField('confirmation', 'select', array(
		//                         'name'  => 'confirmation',
		//                         'label' => Mage::helper('customer')->__($confirmationAttribute->getFrontendLabel()),
		//                     ))->setEntityAttribute($confirmationAttribute)
		//                         ->setValues(array('' => 'Confirmed', $confirmationKey => 'Not confirmed'));

		//                     // prepare send welcome email checkbox, if customer is not confirmed
		//                     // no need to add it, if website id is empty
		//                     if ($customer->getConfirmation() && $customer->getWebsiteId()) {
		//                         $fieldset->addField('sendemail', 'checkbox', array(
		//                             'name'  => 'sendemail',
		//                             'label' => Mage::helper('customer')->__('Send Welcome Email after Confirmation')
		//                         ));
		//                         $customer->setData('sendemail', '1');
		//                     }
		//                 }
		//             }
		//         } else {
		//             $newFieldset = $form->addFieldset(
		//                 'password_fieldset',
		//                 array('legend'=>Mage::helper('customer')->__('Password Management'))
		//             );
		//             $field = $newFieldset->addField('password', 'text',
		//                 array(
		//                     'label' => Mage::helper('customer')->__('Password'),
		//                     'class' => 'input-text required-entry validate-password',
		//                     'name'  => 'password',
		//                     'required' => true
		//                 )
		//             );
		//             $field->setRenderer($this->getLayout()->createBlock('adminhtml/customer_edit_renderer_newpass'));

		//             // prepare send welcome email checkbox
		//             $fieldset->addField('sendemail', 'checkbox', array(
		//                 'label' => Mage::helper('customer')->__('Send Welcome Email'),
		//                 'name'  => 'sendemail',
		//                 'id'    => 'sendemail',
		//             ));
		//             $customer->setData('sendemail', '1');
		//             if (!Mage::app()->isSingleStoreMode()) {
		//                 $fieldset->addField('sendemail_store_id', 'select', array(
		//                     'label' => $this->helper('customer')->__('Send From'),
		//                     'name' => 'sendemail_store_id',
		//                     'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
		//                 ));
		//             }
		//         }

		//         // make sendemail and sendmail_store_id disabled, if website_id has empty value
		//         $isSingleMode = Mage::app()->isSingleStoreMode();
		//         $sendEmailId = $isSingleMode ? 'sendemail' : 'sendemail_store_id';
		//         $sendEmail = $form->getElement($sendEmailId);

		//         $prefix = $form->getHtmlIdPrefix();
		//         if ($sendEmail) {
		//             $_disableStoreField = '';
		//             if (!$isSingleMode) {
		//                 $_disableStoreField = "$('{$prefix}sendemail_store_id').disabled=(''==this.value || '0'==this.value);";
		//             }
		//             $sendEmail->setAfterElementHtml(
		//                 '<script type="text/javascript">'
		//                 . "
		//                 $('{$prefix}website_id').disableSendemail = function() {
		//                     $('{$prefix}sendemail').disabled = ('' == this.value || '0' == this.value);".
		//                     $_disableStoreField
		//                 ."}.bind($('{$prefix}website_id'));
		//                 Event.observe('{$prefix}website_id', 'change', $('{$prefix}website_id').disableSendemail);
		//                 $('{$prefix}website_id').disableSendemail();
		//                 "
		//                 . '</script>'
		//             );
		//         }

		//         if ($customer->isReadonly()) {
		//             foreach ($customer->getAttributes() as $attribute) {
		//                 $element = $form->getElement($attribute->getAttributeCode());
		//                 if ($element) {
		//                     $element->setReadonly(true, true);
		//                 }
		//             }
		//         }

		//         $form->setValues($customer->getData());
		$this->setForm($form);
		return $this;
		}
	}