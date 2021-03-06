-- +--------------------------------------------------------------------+
-- | CiviCRM version 5                                                  |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2019                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--


-- +--------------------------------------------------------------------+
-- | CiviCRM version 5                                                  |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2019                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+
--
-- Generated from drop.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the exisiting tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_data_processor_source`;
DROP TABLE IF EXISTS `civicrm_data_processor_output`;
DROP TABLE IF EXISTS `civicrm_data_processor_filter`;
DROP TABLE IF EXISTS `civicrm_data_processor_field`;
DROP TABLE IF EXISTS `civicrm_data_processor`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_data_processor
-- *
-- *******************************************************/
CREATE TABLE `civicrm_data_processor` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique DataProcessor ID',
     `name` varchar(255) NULL   ,
     `title` varchar(255) NOT NULL   ,
     `type` varchar(255) NOT NULL   ,
     `configuration` text NULL   ,
     `is_active` tinyint NOT NULL   ,
     `description` text NULL   ,
     `storage_type` varchar(255) NULL   ,
     `storage_configuration` text NULL   ,
     `status` int unsigned NULL  DEFAULT 0 ,
     `source_file` varchar(255) NULL    
,
        PRIMARY KEY (`id`)
 
 
 
)    ;

-- /*******************************************************
-- *
-- * civicrm_data_processor_field
-- *
-- *******************************************************/
CREATE TABLE `civicrm_data_processor_field` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique DataProcessorField ID',
     `data_processor_id` int unsigned NOT NULL   COMMENT 'FK to Data Processor',
     `weight` int NULL   ,
     `name` varchar(255) NULL   ,
     `title` varchar(255) NOT NULL   ,
     `type` varchar(255) NOT NULL   ,
     `configuration` text NULL    
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_data_processor_field_data_processor_id FOREIGN KEY (`data_processor_id`) REFERENCES `civicrm_data_processor`(`id`) ON DELETE CASCADE  
)    ;

-- /*******************************************************
-- *
-- * civicrm_data_processor_filter
-- *
-- *******************************************************/
CREATE TABLE `civicrm_data_processor_filter` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique DataProcessorFilter ID',
     `data_processor_id` int unsigned NOT NULL   COMMENT 'FK to Data Processor',
     `weight` int NULL   ,
     `name` varchar(255) NULL   ,
     `title` varchar(255) NOT NULL   ,
     `type` varchar(255) NOT NULL   ,
     `is_required` tinyint NULL   ,
     `is_exposed` tinyint NULL  DEFAULT 1 ,
     `configuration` text NULL   ,
     `filter_value` text NULL    
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_data_processor_filter_data_processor_id FOREIGN KEY (`data_processor_id`) REFERENCES `civicrm_data_processor`(`id`) ON DELETE CASCADE  
)    ;

-- /*******************************************************
-- *
-- * civicrm_data_processor_output
-- *
-- *******************************************************/
CREATE TABLE `civicrm_data_processor_output` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique DataProcessorOutput ID',
     `data_processor_id` int unsigned NOT NULL   COMMENT 'FK to Data Processor',
     `type` varchar(255) NOT NULL   ,
     `configuration` text NULL   ,
     `permission` varchar(255) NULL   ,
     `api_entity` varchar(255) NULL   ,
     `api_action` varchar(255) NULL   ,
     `api_count_action` varchar(255) NULL    
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_data_processor_output_data_processor_id FOREIGN KEY (`data_processor_id`) REFERENCES `civicrm_data_processor`(`id`) ON DELETE CASCADE  
) ENGINE=InnoDB   ; 

-- /*******************************************************
-- *
-- * civicrm_data_processor_source
-- *
-- *******************************************************/
CREATE TABLE `civicrm_data_processor_source` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique DataProcessorSource ID',
     `data_processor_id` int unsigned NOT NULL   COMMENT 'FK to Data Processor',
     `weight` int NULL   ,
     `name` varchar(255) NULL   ,
     `title` varchar(255) NOT NULL   ,
     `type` varchar(255) NOT NULL   ,
     `configuration` text NULL   ,
     `join_type` varchar(255) NULL   ,
     `join_configuration` text NULL    
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_data_processor_source_data_processor_id FOREIGN KEY (`data_processor_id`) REFERENCES `civicrm_data_processor`(`id`) ON DELETE CASCADE  
) ENGINE=InnoDB   ;

 
