<?php
     
    $installer = $this;
     
    $installer->startSetup();
    
    Mage::app('default');
    $reader = Mage::getSingleton('core/resource')->getConnection('core_read');
    
    #Add litle_subscription table
    $installer->run("
DROP TABLE IF EXISTS {$installer->getTable('palorus/subscription')};
");
    $installer->run("
						CREATE TABLE {$installer->getTable('palorus/subscription')} (
						subscription_id integer(10) unsigned NOT NULL auto_increment COMMENT 'pk for table',
						product_id integer(10) NOT NULL default 0 COMMENT 'fk to product',
						customer_id integer(10) unsigned NOT NULL default 0 COMMENT 'fk to customer',
						initial_order_id integer(10) unsigned NOT NULL default 0 COMMENT 'fk to order for first order placed in subscription',
						amount integer(12) NOT NULL COMMENT 'amount to be charged per period in pennies - this is the litle_subscription_amount_per_iteration configured as an attribute',
						
						initial_fees integer(12) NOT NULL default 0 COMMENT 'amount in pennies for inital fee - this is the product price in pennies configured on the catalog screen',
						num_of_iterations integer(5) NOT NULL default 0 COMMENT 'how many billing cycles total',
						iteration_length integer(3) NOT NULL default 0 COMMENT 'fk to litle_iteration_length',
						num_of_iterations_ran integer(5) NOT NULL default 0 COMMENT 'how many iterations have happened so far',
						active boolean NOT NULL default false COMMENT 'whether it is active or not - used by suspend and trial period',
						created_date timestamp NOT NULL COMMENT 'date subscription created',
						start_date timestamp NOT NULL COMMENT 'when to start iteration 1',
						PRIMARY KEY (subscription_id)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle Subscription Order Info';
					");

    #Add litle_subscription_suspend table
    $installer->run("
						DROP TABLE IF EXISTS {$installer->getTable('palorus/subscription_suspend')};
					");
    
    $installer->run("
						CREATE TABLE {$installer->getTable('palorus/subscription_suspend')} (
						suspend_id integer(10) unsigned NOT NULL auto_increment COMMENT 'pk for table',
						subscription_id integer(10) NOT NULL default 0 COMMENT 'fk to litle_subscription',
						turn_on_date timestamp NOT NULL default current_timestamp COMMENT 'when to restart subscription',
						PRIMARY KEY (suspend_id)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle Subscription Order Info';
					");
    
    #Add litle_subscription_history table
    $installer->run("
						DROP TABLE IF EXISTS {$installer->getTable('palorus/subscription_history')};
					");
    $installer->run("
						CREATE TABLE {$installer->getTable('palorus/subscription_history')} (
						subscription_history_id integer(10) unsigned NOT NULL auto_increment COMMENT 'pk for table',
						subscription_id integer(10) NOT NULL default 0 COMMENT 'fk to litle_subscription',
						cron_id integer(10) NOT NULL default 0 COMMENT 'fk to litle_subscription_cron',
						order_id integer(10) unsigned NULL default 0 COMMENT 'fk to order for this bill',
						success boolean NOT NULL default true COMMENT 'Whether this attempt was successful or not',
						PRIMARY KEY (subscription_history_id)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle Subscription Order Info';
					");
    
    #Add litle_subscription_cron_history table
    $installer->run("
						DROP TABLE IF EXISTS {$installer->getTable('palorus/subscription_cron_history')};
					");
    $installer->run("
						CREATE TABLE {$installer->getTable('palorus/subscription_cron_history')} (
						cron_history_id integer(10) unsigned NOT NULL auto_increment COMMENT 'pk for table',
						time_ran timestamp NOT NULL default current_timestamp COMMENT 'when this cron ran',
						PRIMARY KEY (cron_history_id)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle Subscription Order Info';
					");
    
    #Add litle_subscription_iteration_ref table
    $installer->run("
						DROP TABLE IF EXISTS {$installer->getTable('palorus/subscription_iteration_ref')};
					");
    $installer->run("
						CREATE TABLE {$installer->getTable('palorus/subscription_iteration_ref')} (
						iteration_ref_id integer(10) unsigned NOT NULL COMMENT 'pk for table',
						value varchar(25) NOT NULL COMMENT 'plain text name',
						PRIMARY KEY (iteration_ref_id)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle Subscription Order Info';
					");
    
    #Alter vault table to add the expiration date field
    $installer->run("ALTER TABLE {$installer->getTable('palorus/vault')} ADD COLUMN expdate varchar(4) NULL COMMENT 'expiration date';");
    
    $max = $reader->query("select max(sort_order) from eav_entity_attribute")->fetchColumn();
    
    #Add litle_subscription attribute, available to all products
	$installer->run("INSERT INTO `eav_attribute`
				(`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`)
				VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), 'litle_subscription', NULL, 'int', 'boolean', 'Litle Subscription Allowed', 'eav/entity_attribute_source_boolean', 0, 1, 0, 0)");
    $installer->run("INSERT INTO `catalog_eav_attribute`
					(`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `is_configurable`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`)
					VALUES ((select attribute_id from eav_attribute where attribute_code = 'litle_subscription'), 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, NULL, 0, 0)");
					$max++;
    $installer->run("INSERT INTO `eav_entity_attribute`
					(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`)
					VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default'), (SELECT attribute_group_id from eav_attribute_group where attribute_set_id = (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default') and default_id = 1), (select attribute_id from eav_attribute where attribute_code = 'litle_subscription'), $max)");

    #Add litle_subs_amount_per_itr attribute
    $installer->run("INSERT INTO `eav_attribute`
(`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`)
VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), 'litle_subs_amount_per_itr', NULL, 'decimal', 'price', 'Litle Subscription Amount Per Iteration', 'catalog/product_attribute_source_msrp_type_price', 0, 1, 0, 0)");
    $installer->run("INSERT INTO `catalog_eav_attribute`
(`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `is_configurable`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`)
VALUES ((select attribute_id from eav_attribute where attribute_code = 'litle_subs_amount_per_itr'), 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, NULL, 0, 0)");
$max++;
    $installer->run("INSERT INTO `eav_entity_attribute`
(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`)
VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default'), (SELECT attribute_group_id from eav_attribute_group where attribute_set_id = (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default') and default_id = 1), (select attribute_id from eav_attribute where attribute_code = 'litle_subs_amount_per_itr'), $max)");
    
    #Add litle_subs_num_of_itrs attribute
    $installer->run("INSERT INTO `eav_attribute`
(`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`)
VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), 'litle_subs_num_of_itrs', NULL, 'int', 'text', 'Litle Subscription Number of Iterations', 'catalog/product_attribute_source_msrp_type_price', 0, 1, 0, 0)");
    $installer->run("INSERT INTO `catalog_eav_attribute`
(`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `is_configurable`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`)
VALUES ((select attribute_id from eav_attribute where attribute_code = 'litle_subs_num_of_itrs'), 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, NULL, 0, 0)");
$max++;
    $installer->run("INSERT INTO `eav_entity_attribute`
(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`)
VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default'), (SELECT attribute_group_id from eav_attribute_group where attribute_set_id = (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default') and default_id = 1), (select attribute_id from eav_attribute where attribute_code = 'litle_subs_num_of_itrs'), $max)");
    
    #Add litle_subs_itr_len attribute
    $installer->run("INSERT INTO `eav_attribute`
(`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`)
VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), 'litle_subs_itr_len', NULL, 'int', 'select', 'Litle Subscription Iteration Length', 'eav/entity_attribute_source_table', 0, 1, 3, 0)");
    $installer->run("INSERT INTO `catalog_eav_attribute`
(`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `is_configurable`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`)
VALUES ((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len'), 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, NULL, 0, 0)");
$max++;
    $installer->run("INSERT INTO `eav_entity_attribute`
(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`)
VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default'), (SELECT attribute_group_id from eav_attribute_group where attribute_set_id = (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default') and default_id = 1), (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len'), $max)");
        
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 0 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Daily')");
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 1)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 1 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Weekly')");
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 2)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 2 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Bi-Weekly')");
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 3)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 3 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Semi-Monthly')");
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 4)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 4 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Monthly')");
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 5)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 5 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Semi-Annually')");
    $installer->run("INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES (((select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 6)");
    $installer->run("INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ((select option_id from eav_attribute_option where sort_order = 6 and attribute_id = (select attribute_id from eav_attribute where attribute_code = 'litle_subs_itr_len')), 0, 'Annually')");
    
    $installer->endSetup();