<?php
$installer = $this;

$installer->getConnection()->changeColumn(
    $installer->getTable('palorus/failedtransactions'),
    'full_xml',
    'full_xml',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => '64k',
        'nullable' => true,
        'comment' => 'Full XML'
    )
);