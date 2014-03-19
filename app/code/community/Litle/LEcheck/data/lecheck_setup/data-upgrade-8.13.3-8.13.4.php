<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

$configs = Mage::getResourceModel('core/config_data_collection');
/* @var $configs Mage_Core_Model_Resource_Config_Data_Collection */
$configs->addPathFilter('payment/lecheck');
foreach ($configs as $config) {
    $config->setPath(strtolower($config->getPath()))
        ->save();
}
