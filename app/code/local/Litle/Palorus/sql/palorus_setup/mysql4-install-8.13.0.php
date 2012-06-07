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
    $installer = $this;
     
    $installer->startSetup();

    $installer->run("
    DROP TABLE IF EXISTS {$installer->getTable('palorus/insight')};
    ");
    
    $installer->run("
CREATE TABLE {$installer->getTable('palorus/insight')} (
customer_insight_id integer(10) unsigned NOT NULL auto_increment,
customer_id integer(10) unsigned NOT NULL default 0,
order_number integer(10) unsigned NOT NULL default 0,
order_id integer(10) unsigned NOT NULL default 0,
last varchar(20) NULL,
order_amount varchar(20) NULL,
affluence varchar(15) NULL,
issuing_country varchar(20) NULL,
prepaid_card_type varchar(20) NULL,
funding_source varchar(20) NULL,
available_balance varchar(20) NULL,
reloadable varchar(20) NULL,
PRIMARY KEY (customer_insight_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle customer insight for an account';
");
    
    $installer->run("
        DROP TABLE IF EXISTS {$installer->getTable('palorus/vault')};
    ");
    
    $installer->run("
CREATE TABLE {$installer->getTable('palorus/vault')} (
vault_id integer(10) unsigned NOT NULL auto_increment,
order_id integer(10) unsigned NOT NULL default 0,
customer_id integer(10) unsigned NOT NULL default 0,
last4 varchar(4) NULL,
token varchar(25) NULL,
type varchar(2) NULL,
bin varchar(6) NULL,
PRIMARY KEY (vault_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Litle vaulted credit cards for an account';
    ");
    
     
    $installer->endSetup();