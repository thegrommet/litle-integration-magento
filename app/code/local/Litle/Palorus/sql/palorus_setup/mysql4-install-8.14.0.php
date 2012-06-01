<?php
     
    $installer = $this;
     
    $installer->startSetup();
    
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
    
    amount integer(12) NOT NULL COMMENT 'amount to be charged per period in pennies',

    initial_fees integer(12) NOT NULL default 0 COMMENT 'amount in pennies for inital fee',
    num_of_iterations integer(5) NOT NULL default 0 COMMENT 'how many billing cycles total',
    iteration_length integer(3) NOT NULL default 0 COMMENT 'fk to litle_iteration_length',
    num_of_iterations_ran integer(5) NOT NULL default 0 COMMENT 'how many iterations have happened so far',
    
    active boolean NOT NULL default true COMMENT 'whether it is active or not - used by suspend and trial period',
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
            order_id integer(10) unsigned NOT NULL default 0 COMMENT 'fk to order for this bill',
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
    
    #Add the values for iteration length ref
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (1,'Daily')");
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (2,'Weekly')");
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (3,'BiWeekly')");
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (4,'Monthly')");
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (5,'SemiMonthly')");
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (6,'Annually')");
    $installer->run("INSERT INTO litle_subscription_iteration_ref values (7,'SemiAnnually')");
    
    #Add litle_subscription attribute, available to all products
	$installer->run("INSERT INTO `eav_attribute` 
		(`entity_type_id`, `attribute_code`, `backend_model`, `backend_type`, `frontend_input`, `frontend_label`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`) 
		VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), 'litle_subscription', NULL, 'int', 'boolean', 'Litle Subscription', 'eav/entity_attribute_source_boolean', 0, 1, 0, 0)");
    $installer->run("INSERT INTO `catalog_eav_attribute` 
    	(`attribute_id`, `is_global`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `is_configurable`, `apply_to`, `is_visible_in_advanced_search`, `is_used_for_promo_rules`) 
    	VALUES ((select attribute_id from eav_attribute where attribute_code = 'litle_subscription'), 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, NULL, 0, 0)");

    $installer->run("INSERT INTO `eav_entity_attribute` 
    	(`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`) 
    	VALUES ((SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')), (SELECT attribute_set_id from eav_attribute_set where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default'), (SELECT attribute_group_id from eav_attribute_group where attribute_set_id = (SELECT attribute_set_id from eav_attribute_set  where entity_type_id = (SELECT entity_type_id FROM `eav_entity_type` WHERE (`eav_entity_type`.`entity_type_code`='catalog_product')) AND attribute_set_name = 'Default') and default_id = 1), (select attribute_id from eav_attribute where attribute_code = 'litle_subscription'), 1)");
     
    $installer->endSetup();