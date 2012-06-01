<?php
     
    $installer = $this;
     
    $installer->startSetup();
    
    #Add litle_subscription table
    $installer->run("
            DROP TABLE IF EXISTS {$installer->getTable('palorus/subscription')};
        ");
    $installer->run("
    CREATE TABLE {$installer->getTable('palorus/subscription')} (
    subscription_id integer(10) unsigned NOT NULL auto_increment,
    order_id integer(10) unsigned NOT NULL default 0,
    customer_id integer(10) unsigned NOT NULL default 0,
    product_id integer(10) NOT NULL default 0,
    amount integer(12) NULL,
    PRIMARY KEY (subscription_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle Subscription Order Info';
        ");

    #Add litle_subscription attribute, available to all products
	$installer->run("INSERT INTO `eav_attribute` 
		(`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`) 
		VALUES (4, 'litle_subscription', NULL, 'int', 'boolean', 'Litle Subscription', 'eav/entity_attribute_source_boolean', 0, 1, 0, 0)");
    $installer->run("INSERT INTO `catalog_eav_attribute` 
    	(`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `is_configurable`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`) 
    	VALUES ((select attribute_id from eav_attribute where attribute_code = 'litle_subscription'), 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, NULL, 0, 0)");
    $installer->run("INSERT INTO `eav_entity_attribute` 
    	(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`) 
    	VALUES (4, 4, 7, (select attribute_id from eav_attribute where attribute_code = 'litle_subscription'), 31)");
     
    $installer->endSetup();