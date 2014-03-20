<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$table = $installer->getTable('palorus/failedtransactions');

$installer->getConnection()->dropColumn($table, 'order_num');
    
$installer->getConnection()->addColumn(
    $table,
    'quote_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => true,
        'comment' => 'Quote ID'
    )
);