<?php

/**
 * Version 192 of database update.
 * This version: -
 *	
 *	1:	Declares all Foreign Keys for the ticketing tables, as well as other indexes where appropriate, and does some data type alteration to facilitate the foreign key declarations
 *
 */

class Flex_Rollout_Version_000192 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		$arrCommands = array();
		
		
		/********************************************* START - Modifications to the ticketing_ticket table - START ****************************************************************/ 
		// Declaration of FOREIGN KEY for ticketing_ticket.group_ticket_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.group_ticket_id -> ticketing_ticket.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_group_ticket_id_ticketing_ticket_id FOREIGN KEY (group_ticket_id) REFERENCES ticketing_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_group_ticket_id_ticketing_ticket_id, DROP INDEX fk_ticketing_ticket_group_ticket_id_ticketing_ticket_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket AS tt_c LEFT JOIN ticketing_ticket AS tt_p ON tt_c.group_ticket_id = tt_p.id WHERE tt_c.group_ticket_id IS NOT NULL AND tt_p.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.priority_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.priority_id -> ticketing_priority.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_priority_id_ticketing_priority_id FOREIGN KEY (priority_id) REFERENCES ticketing_priority(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_priority_id_ticketing_priority_id, DROP INDEX fk_ticketing_ticket_priority_id_ticketing_priority_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN ticketing_priority ON ticketing_ticket.priority_id = ticketing_priority.id WHERE ticketing_ticket.priority_id IS NOT NULL AND ticketing_priority.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.owner_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.owner_id -> ticketing_user.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_owner_id_ticketing_user_id FOREIGN KEY (owner_id) REFERENCES ticketing_user(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_owner_id_ticketing_user_id, DROP INDEX fk_ticketing_ticket_owner_id_ticketing_user_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN ticketing_user ON ticketing_ticket.owner_id = ticketing_user.id WHERE ticketing_ticket.owner_id IS NOT NULL AND ticketing_user.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.contact_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.contact_id -> ticketing_contact.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_contact_id_ticketing_contact_id FOREIGN KEY (contact_id) REFERENCES ticketing_contact(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_contact_id_ticketing_contact_id, DROP INDEX fk_ticketing_ticket_contact_id_ticketing_contact_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN ticketing_contact ON ticketing_ticket.contact_id = ticketing_contact.id WHERE ticketing_ticket.contact_id IS NOT NULL AND ticketing_contact.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.status_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.status_id -> ticketing_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_status_id_ticketing_status_id FOREIGN KEY (status_id) REFERENCES ticketing_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_status_id_ticketing_status_id, DROP INDEX fk_ticketing_ticket_status_id_ticketing_status_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN ticketing_status ON ticketing_ticket.status_id = ticketing_status.id WHERE ticketing_ticket.status_id IS NOT NULL AND ticketing_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Modify ticketing_ticket.customer_group_id to be a signed BIGINT so we can make it a foreign key with CustomerGroup.Id (which is unsigned)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Modifying ticketing_ticket.customer_group_id so it is the exact same data type as CustomerGroup.Id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket CHANGE customer_group_id customer_group_id BIGINT(20) NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket CHANGE customer_group_id customer_group_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table';";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.customer_group_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.customer_group_id -> CustomerGroup.Id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_customer_group_id_customer_group_id FOREIGN KEY (customer_group_id) REFERENCES CustomerGroup(Id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_customer_group_id_customer_group_id, DROP INDEX fk_ticketing_ticket_customer_group_id_customer_group_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN CustomerGroup ON ticketing_ticket.customer_group_id = CustomerGroup.Id WHERE ticketing_ticket.customer_group_id IS NOT NULL AND CustomerGroup.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.account_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.account_id -> Account.Id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_account_id_account_id FOREIGN KEY (account_id) REFERENCES Account(Id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_account_id_account_id, DROP INDEX fk_ticketing_ticket_account_id_account_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN Account ON ticketing_ticket.account_id = Account.Id WHERE ticketing_ticket.account_id IS NOT NULL AND Account.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket.category_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket.category_id -> ticketing_category.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD CONSTRAINT fk_ticketing_ticket_category_id_ticketing_category_id FOREIGN KEY (category_id) REFERENCES ticketing_category(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP FOREIGN KEY fk_ticketing_ticket_category_id_ticketing_category_id, DROP INDEX fk_ticketing_ticket_category_id_ticketing_category_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket LEFT JOIN ticketing_category ON ticketing_ticket.category_id = ticketing_category.id WHERE ticketing_ticket.category_id IS NOT NULL AND ticketing_category.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of INDEX for ticketing_ticket.creation_datetime
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Index for ticketing_ticket.creation_datetime';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD INDEX in_ticketing_ticket_creation_datetime (creation_datetime);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP INDEX in_ticketing_ticket_creation_datetime;";
		$arrCommands[] = $arrCommand;

		// Declaration of INDEX for ticketing_ticket.modified_datetime
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Index for ticketing_ticket.modified_datetime';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket ADD INDEX in_ticketing_ticket_modified_datetime (modified_datetime);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket DROP INDEX in_ticketing_ticket_modified_datetime;";
		$arrCommands[] = $arrCommand;

		/*********************************************** END - Modifications to the ticketing_ticket table - END ******************************************************************/

		/***************************************** START - Modifications to the ticketing_ticket_history table - START ************************************************************/
		// Declaration of FOREIGN KEY for ticketing_ticket_history.ticket_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.ticket_id -> ticketing_ticket.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_ticket_id_ticketing_ticket_id FOREIGN KEY (ticket_id) REFERENCES ticketing_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_ticket_id_ticketing_ticket_id, DROP INDEX fk_ticketing_ticket_history_ticket_id_ticketing_ticket_id;"; 
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_ticket ON ticketing_ticket_history.ticket_id = ticketing_ticket.id WHERE ticketing_ticket_history.ticket_id IS NOT NULL AND ticketing_ticket.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.group_ticket_id
		// NOTE: I don't think we should even have group_ticket_id in the ticketing_ticket_history table.  ticketing_ticket.group_ticket_id should never change once it has been set
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.group_ticket_id -> ticketing_ticket.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_group_ticket_id_ticketing_ticket_id FOREIGN KEY (group_ticket_id) REFERENCES ticketing_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_group_ticket_id_ticketing_ticket_id, DROP INDEX fk_ticketing_ticket_history_group_ticket_id_ticketing_ticket_id;"; 
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_ticket ON ticketing_ticket_history.group_ticket_id = ticketing_ticket.id WHERE ticketing_ticket_history.group_ticket_id IS NOT NULL AND ticketing_ticket.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.priority_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.priority_id -> ticketing_priority.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_priority_id_ticketing_priority_id FOREIGN KEY (priority_id) REFERENCES ticketing_priority(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_priority_id_ticketing_priority_id, DROP INDEX fk_ticketing_ticket_history_priority_id_ticketing_priority_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_priority ON ticketing_ticket_history.priority_id = ticketing_priority.id WHERE ticketing_ticket_history.priority_id IS NOT NULL AND ticketing_priority.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.owner_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.owner_id -> ticketing_user.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_owner_id_ticketing_user_id FOREIGN KEY (owner_id) REFERENCES ticketing_user(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_owner_id_ticketing_user_id, DROP INDEX fk_ticketing_ticket_history_owner_id_ticketing_user_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_user ON ticketing_ticket_history.owner_id = ticketing_user.id WHERE ticketing_ticket_history.owner_id IS NOT NULL AND ticketing_user.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.contact_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.contact_id -> ticketing_contact.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_contact_id_ticketing_contact_id FOREIGN KEY (contact_id) REFERENCES ticketing_contact(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_contact_id_ticketing_contact_id, DROP INDEX fk_ticketing_ticket_history_contact_id_ticketing_contact_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_contact ON ticketing_ticket_history.contact_id = ticketing_contact.id WHERE ticketing_ticket_history.contact_id IS NOT NULL AND ticketing_contact.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.status_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.status_id -> ticketing_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_status_id_ticketing_status_id FOREIGN KEY (status_id) REFERENCES ticketing_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_status_id_ticketing_status_id, DROP INDEX fk_ticketing_ticket_history_status_id_ticketing_status_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_status ON ticketing_ticket_history.status_id = ticketing_status.id WHERE ticketing_ticket_history.status_id IS NOT NULL AND ticketing_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Modify ticketing_ticket_history.customer_group_id to be a signed BIGINT so we can make it a foreign key with CustomerGroup.Id (which is unsigned)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Modifying ticketing_ticket_history.customer_group_id so it is the exact same data type as CustomerGroup.Id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history CHANGE customer_group_id customer_group_id BIGINT(20) NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history CHANGE customer_group_id customer_group_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table';";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.customer_group_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.customer_group_id -> CustomerGroup.Id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_customer_group_id_customer_group_id FOREIGN KEY (customer_group_id) REFERENCES CustomerGroup(Id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_customer_group_id_customer_group_id, DROP INDEX fk_ticketing_ticket_history_customer_group_id_customer_group_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN CustomerGroup ON ticketing_ticket_history.customer_group_id = CustomerGroup.Id WHERE ticketing_ticket_history.customer_group_id IS NOT NULL AND CustomerGroup.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.account_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.account_id -> Account.Id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_account_id_account_id FOREIGN KEY (account_id) REFERENCES Account(Id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_account_id_account_id, DROP INDEX fk_ticketing_ticket_history_account_id_account_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN Account ON ticketing_ticket_history.account_id = Account.Id WHERE ticketing_ticket_history.account_id IS NOT NULL AND Account.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_history.category_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_history.category_id -> ticketing_category.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD CONSTRAINT fk_ticketing_ticket_history_category_id_ticketing_category_id FOREIGN KEY (category_id) REFERENCES ticketing_category(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP FOREIGN KEY fk_ticketing_ticket_history_category_id_ticketing_category_id, DROP INDEX fk_ticketing_ticket_history_category_id_ticketing_category_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_history LEFT JOIN ticketing_category ON ticketing_ticket_history.category_id = ticketing_category.id WHERE ticketing_ticket_history.category_id IS NOT NULL AND ticketing_category.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of INDEX for ticketing_ticket_history.creation_datetime
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Index for ticketing_ticket_history.creation_datetime';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD INDEX in_ticketing_ticket_history_creation_datetime (creation_datetime);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP INDEX in_ticketing_ticket_history_creation_datetime;";
		$arrCommands[] = $arrCommand;

		// Declaration of INDEX for ticketing_ticket_history.modified_datetime
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Index for ticketing_ticket_history.modified_datetime';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_history ADD INDEX in_ticketing_ticket_history_modified_datetime (modified_datetime);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_history DROP INDEX in_ticketing_ticket_history_modified_datetime;";
		$arrCommands[] = $arrCommand;

		/******************************************* END - Modifications to the ticketing_ticket_history table - END **************************************************************/

		/***************************************** START - Modifications to the ticketing_attachment_type table - START ***********************************************************/
		// Change ticketing_attachment_type.blacklist_status_id so its data type matches that of ticketing_attachment_blacklist_status.id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Change ticketing_attachment_type.blacklist_status_id so its data type matches that of ticketing_attachment_blacklist_status.id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_attachment_type CHANGE blacklist_status_id blacklist_status_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to the ticketing_attachment_blacklist_status table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_attachment_type CHANGE blacklist_status_id blacklist_status_id SMALLINT(5) UNSIGNED NOT NULL COMMENT 'FK to the ticketing_attachment_blacklist_status table';"; 
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_attachment_type.blacklist_status_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_attachment_type.blacklist_status_id -> ticketing_attachment_blacklist_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_attachment_type ADD CONSTRAINT fk_ticketing_attachment_type_blacklist_status_id FOREIGN KEY (blacklist_status_id) REFERENCES ticketing_attachment_blacklist_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_attachment_type DROP FOREIGN KEY fk_ticketing_attachment_type_blacklist_status_id, DROP INDEX fk_ticketing_attachment_type_blacklist_status_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_attachment_type LEFT JOIN ticketing_attachment_blacklist_status ON ticketing_attachment_type.blacklist_status_id = ticketing_attachment_blacklist_status.id WHERE ticketing_attachment_type.blacklist_status_id IS NOT NULL AND ticketing_attachment_blacklist_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;
		
		/******************************************* END - Modifications to the ticketing_attachment_type table - END *************************************************************/

		/********************************************* START - Modifications to the ticketing_category table - START **************************************************************/
		// Drop the const_name UNIQUE constraint
		$arrCommand = array();
		$arrCommand['step_name']	= 'Drop the ticketing_category.const_name UNIQUE constraint';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_category DROP INDEX const_name;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_category ADD UNIQUE const_name (css_name);"; 
		$arrCommands[] = $arrCommand;

		// Declare UNIQUE constraint on ticketing_category.name
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on ticketing_category.name';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_category ADD UNIQUE un_ticketing_category_name (name);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_category DROP INDEX un_ticketing_category_name;"; 
		$arrCommand['check_sql']	= "SELECT name, COUNT(*) FROM ticketing_category GROUP BY name HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declare UNIQUE constraint on ticketing_category.const_name
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on ticketing_category.const_name';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_category ADD UNIQUE un_ticketing_category_const_name (const_name);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_category DROP INDEX un_ticketing_category_const_name;"; 
		$arrCommand['check_sql']	= "SELECT const_name, COUNT(*) FROM ticketing_category GROUP BY const_name HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declare UNIQUE constraint on ticketing_category.css_name
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on ticketing_category.css_name';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_category ADD UNIQUE un_ticketing_category_css_name (css_name);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_category DROP INDEX un_ticketing_category_css_name;";
		$arrCommand['check_sql']	= "SELECT css_name, COUNT(*) FROM ticketing_category GROUP BY css_name HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/*********************************************** END - Modifications to the ticketing_category table - END ****************************************************************/

		/********************************************* START - Modifications to the ticketing_contact table - START ***************************************************************/
		// Drop the ticketing_contact.status INDEX
		$arrCommand = array();
		$arrCommand['step_name']	= 'Drop the ticketing_contact.status INDEX';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact DROP INDEX status;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact ADD INDEX status (status);"; 
		$arrCommands[] = $arrCommand;

		// Change ticketing_contact.status so its data type matches that of active_status.id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Change ticketing_contact.status so its data type matches that of active_status.id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact CHANGE status status SMALLINT(5) UNSIGNED NOT NULL COMMENT 'FK to active_status table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact CHANGE status status TINYINT(1) NOT NULL COMMENT 'FK to active_status table';"; 
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_contact.status
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_contact.status -> active_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact ADD CONSTRAINT fk_ticketing_contact_status_active_status_id FOREIGN KEY (status) REFERENCES active_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact DROP FOREIGN KEY fk_ticketing_contact_status_active_status_id, DROP INDEX fk_ticketing_contact_status_active_status_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_contact LEFT JOIN active_status ON ticketing_contact.status = active_status.id WHERE ticketing_contact.status IS NOT NULL AND active_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Change ticketing_contact.auto_reply so its data type matches that of active_status.id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Change ticketing_contact.auto_reply so its data type matches that of active_status.id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact CHANGE auto_reply auto_reply SMALLINT(5) UNSIGNED NOT NULL COMMENT 'FK to active_status table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact CHANGE auto_reply auto_reply TINYINT(1) NOT NULL COMMENT 'FK to active_status table';"; 
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_contact.auto_reply
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_contact.auto_reply -> active_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact ADD CONSTRAINT fk_ticketing_contact_auto_reply_active_status_id FOREIGN KEY (auto_reply) REFERENCES active_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact DROP FOREIGN KEY fk_ticketing_contact_auto_reply_active_status_id, DROP INDEX fk_ticketing_contact_auto_reply_active_status_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_contact LEFT JOIN active_status ON ticketing_contact.auto_reply = active_status.id WHERE ticketing_contact.auto_reply IS NOT NULL AND active_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/*********************************************** END - Modifications to the ticketing_contact table - END *****************************************************************/

		/***************************************** START - Modifications to the ticketing_contact_account table - START ***********************************************************/
		// Change ticketing_contact_account.ticketing_contact_id & .account_id so they have the same data types as ticketing_contact.id & Account.Id respectively
		$arrCommand = array();
		$arrCommand['step_name']	= 'Change ticketing_contact_account.ticketing_contact_id & .account_id so they have the same data types as ticketing_contact.id & Account.Id respectively';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact_account 
										CHANGE ticketing_contact_id ticketing_contact_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to ticketing_contact table',
										CHANGE account_id account_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to Account table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact_account 
										CHANGE ticketing_contact_id ticketing_contact_id BIGINT(20) NOT NULL COMMENT 'FK to ticketing_contact table',
										CHANGE account_id account_id BIGINT(20) NOT NULL COMMENT 'FK to Account table';"; 
		$arrCommands[] = $arrCommand;
		
		// Declare UNIQUE constraint on (ticketing_contact_account.ticketing_contact_id, ticketing_contact_account.account_id)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on (ticketing_contact_account.ticketing_contact_id, ticketing_contact_account.account_id)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact_account ADD UNIQUE un_ticketing_contact_account_ticketing_contact_id_account_id (ticketing_contact_id, account_id);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact_account DROP INDEX un_ticketing_contact_account_ticketing_contact_id_account_id;"; 
		$arrCommand['check_sql']	= "SELECT ticketing_contact_id, account_id, COUNT(*) FROM ticketing_contact_account GROUP BY ticketing_contact_id, account_id HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;
		
		// Declaration of FOREIGN KEY for ticketing_contact_account.ticketing_contact_id
		// Note that no specific index is implicitly created for this foreign key, because it can use the one created by un_ticketing_contact_account_ticketing_contact_id_account_id,
		// 		So we don't have to drop an index called fk_ticketing_contact_account_ticketing_contact_id_t_contact_id when rolling back the step
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_contact_account.ticketing_contact_id -> ticketing_contact.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact_account ADD CONSTRAINT fk_ticketing_contact_account_ticketing_contact_id_t_contact_id FOREIGN KEY (ticketing_contact_id) REFERENCES ticketing_contact(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact_account DROP FOREIGN KEY fk_ticketing_contact_account_ticketing_contact_id_t_contact_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_contact_account LEFT JOIN ticketing_contact ON ticketing_contact_account.ticketing_contact_id = ticketing_contact.id WHERE ticketing_contact_account.ticketing_contact_id IS NOT NULL AND ticketing_contact.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;
		
		// Declaration of FOREIGN KEY for ticketing_contact_account.account_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_contact_account.account_id -> Account.Id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_contact_account ADD CONSTRAINT fk_ticketing_contact_account_account_id_account_id FOREIGN KEY (account_id) REFERENCES Account(Id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_contact_account DROP FOREIGN KEY fk_ticketing_contact_account_account_id_account_id, DROP INDEX fk_ticketing_contact_account_account_id_account_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_contact_account LEFT JOIN Account ON ticketing_contact_account.account_id = Account.Id WHERE ticketing_contact_account.account_id IS NOT NULL AND Account.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/******************************************* END - Modifications to the ticketing_contact_account table - END *************************************************************/

		/***************************************** START - Modifications to the ticketing_correspondance table - START ************************************************************/
		// Declaration of FOREIGN KEY for ticketing_correspondance.ticket_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_correspondance.ticket_id -> ticketing_ticket.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD CONSTRAINT fk_ticketing_correspondance_ticket_id_ticketing_ticket_id FOREIGN KEY (ticket_id) REFERENCES ticketing_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP FOREIGN KEY fk_ticketing_correspondance_ticket_id_ticketing_ticket_id, DROP INDEX fk_ticketing_correspondance_ticket_id_ticketing_ticket_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_correspondance LEFT JOIN ticketing_ticket ON ticketing_correspondance.ticket_id = ticketing_ticket.id WHERE ticketing_correspondance.ticket_id IS NOT NULL AND ticketing_ticket.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_correspondance.user_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_correspondance.user_id -> ticketing_user.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD CONSTRAINT fk_ticketing_correspondance_user_id_ticketing_user_id FOREIGN KEY (user_id) REFERENCES ticketing_user(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP FOREIGN KEY fk_ticketing_correspondance_user_id_ticketing_user_id, DROP INDEX fk_ticketing_correspondance_user_id_ticketing_user_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_correspondance LEFT JOIN ticketing_user ON ticketing_correspondance.user_id = ticketing_user.id WHERE ticketing_correspondance.user_id IS NOT NULL AND ticketing_user.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_correspondance.contact_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_correspondance.contact_id -> ticketing_contact.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD CONSTRAINT fk_ticketing_correspondance_contact_id_ticketing_contact_id FOREIGN KEY (contact_id) REFERENCES ticketing_contact(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP FOREIGN KEY fk_ticketing_correspondance_contact_id_ticketing_contact_id, DROP INDEX fk_ticketing_correspondance_contact_id_ticketing_contact_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_correspondance LEFT JOIN ticketing_contact ON ticketing_correspondance.contact_id = ticketing_contact.id WHERE ticketing_correspondance.contact_id IS NOT NULL AND ticketing_contact.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_correspondance.customer_group_email_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_correspondance.customer_group_email_id -> ticketing_customer_group_email.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD CONSTRAINT fk_ticketing_correspondance_customer_group_email_id_tcge_id FOREIGN KEY (customer_group_email_id) REFERENCES ticketing_customer_group_email(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP FOREIGN KEY fk_ticketing_correspondance_customer_group_email_id_tcge_id, DROP INDEX fk_ticketing_correspondance_customer_group_email_id_tcge_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_correspondance LEFT JOIN ticketing_customer_group_email ON ticketing_correspondance.customer_group_email_id = ticketing_customer_group_email.id WHERE ticketing_correspondance.customer_group_email_id IS NOT NULL AND ticketing_customer_group_email.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_correspondance.source_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_correspondance.source_id -> ticketing_correspondance_source.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD CONSTRAINT fk_ticketing_correspondance_source_id_tc_source_id FOREIGN KEY (source_id) REFERENCES ticketing_correspondance_source(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP FOREIGN KEY fk_ticketing_correspondance_source_id_tc_source_id, DROP INDEX fk_ticketing_correspondance_source_id_tc_source_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_correspondance LEFT JOIN ticketing_correspondance_source ON ticketing_correspondance.source_id = ticketing_correspondance_source.id WHERE ticketing_correspondance.source_id IS NOT NULL AND ticketing_correspondance_source.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_correspondance.delivery_status_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_correspondance.delivery_status_id -> ticketing_correspondance_delivery_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD CONSTRAINT fk_ticketing_correspondance_delivery_status_id_tcds_id FOREIGN KEY (delivery_status_id) REFERENCES ticketing_correspondance_delivery_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP FOREIGN KEY fk_ticketing_correspondance_delivery_status_id_tcds_id, DROP INDEX fk_ticketing_correspondance_delivery_status_id_tcds_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_correspondance LEFT JOIN ticketing_correspondance_delivery_status ON ticketing_correspondance.delivery_status_id = ticketing_correspondance_delivery_status.id WHERE ticketing_correspondance.delivery_status_id IS NOT NULL AND ticketing_correspondance_delivery_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declare INDEX on ticketing_correspondance.creation_datetime
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of INDEX on ticketing_correspondance.creation_datetime';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD INDEX in_ticketing_correspondance_creation_datetime (creation_datetime);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP INDEX in_ticketing_correspondance_creation_datetime;"; 
		$arrCommands[] = $arrCommand;

		// Declare INDEX on ticketing_correspondance.delivery_datetime
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of INDEX on ticketing_correspondance.delivery_datetime';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_correspondance ADD INDEX in_ticketing_correspondance_delivery_datetime (delivery_datetime);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_correspondance DROP INDEX in_ticketing_correspondance_delivery_datetime;"; 
		$arrCommands[] = $arrCommand;

		/******************************************* END - Modifications to the ticketing_correspondance table - END **************************************************************/

		/************************************* START - Modifications to the ticketing_customer_group_config table - START *********************************************************/
		// Change ticketing_customer_group_config.customer_group_id so its data type matches that of CustomerGroup.Id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Change ticketing_customer_group_config.customer_group_id so its data type matches that of CustomerGroup.Id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_config CHANGE customer_group_id customer_group_id BIGINT(20) NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_config CHANGE customer_group_id customer_group_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table';"; 
		$arrCommands[] = $arrCommand;

		// Declare UNIQUE constraint on ticketing_customer_group_config.customer_group_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on ticketing_customer_group_config.customer_group_id';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_config ADD UNIQUE un_ticketing_customer_group_config_customer_group_id (customer_group_id);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_config DROP INDEX un_ticketing_customer_group_config_customer_group_id;"; 
		$arrCommand['check_sql']	= "SELECT customer_group_id, COUNT(*) FROM ticketing_customer_group_config GROUP BY customer_group_id HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_customer_group_config.customer_group_id
		// Note that an implicit index named fk_ticketing_customer_group_config_customer_group_id_cg_id isn't created because the foreign key can use the index created 
		//		by the UNIQUE constraint un_ticketing_customer_group_config_customer_group_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_customer_group_config.customer_group_id -> CustomerGroup.Id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_config ADD CONSTRAINT fk_ticketing_customer_group_config_customer_group_id_cg_id FOREIGN KEY (customer_group_id) REFERENCES CustomerGroup(Id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_config DROP FOREIGN KEY fk_ticketing_customer_group_config_customer_group_id_cg_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_customer_group_config LEFT JOIN CustomerGroup ON ticketing_customer_group_config.customer_group_id = CustomerGroup.Id WHERE ticketing_customer_group_config.customer_group_id IS NOT NULL AND CustomerGroup.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_customer_group_config.acknowledge_email_receipts
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_customer_group_config.acknowledge_email_receipts -> active_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_config ADD CONSTRAINT fk_ticketing_customer_group_config_acknowledge_email_receipts FOREIGN KEY (acknowledge_email_receipts) REFERENCES active_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_config DROP FOREIGN KEY fk_ticketing_customer_group_config_acknowledge_email_receipts, DROP INDEX fk_ticketing_customer_group_config_acknowledge_email_receipts;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_customer_group_config LEFT JOIN active_status ON ticketing_customer_group_config.acknowledge_email_receipts = active_status.id WHERE ticketing_customer_group_config.acknowledge_email_receipts IS NOT NULL AND active_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_customer_group_config.default_email_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_customer_group_config.default_email_id -> ticketing_customer_group_email.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_config ADD CONSTRAINT fk_ticketing_customer_group_config_default_email_id_tcge_id FOREIGN KEY (default_email_id) REFERENCES ticketing_customer_group_email(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_config DROP FOREIGN KEY fk_ticketing_customer_group_config_default_email_id_tcge_id, DROP INDEX fk_ticketing_customer_group_config_default_email_id_tcge_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_customer_group_config LEFT JOIN ticketing_customer_group_email ON ticketing_customer_group_config.default_email_id = ticketing_customer_group_email.id WHERE ticketing_customer_group_config.default_email_id IS NOT NULL AND ticketing_customer_group_email.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/*************************************** END - Modifications to the ticketing_customer_group_config table - END ***********************************************************/

		/************************************** START - Modifications to the ticketing_customer_group_email table - START *********************************************************/
		// Change ticketing_customer_group_email.customer_group_id & .auto_reply so their data types matche that of CustomerGroup.Id & active_status_id respectively
		$arrCommand = array();
		$arrCommand['step_name']	= 'Change ticketing_customer_group_email.customer_group_id & .auto_reply so their data types match that of CustomerGroup.Id & active_status.id respectively';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_email
										CHANGE customer_group_id customer_group_id BIGINT(20) NOT NULL COMMENT 'FK to the CustomerGroup table',
										CHANGE auto_reply auto_reply SMALLINT(5) UNSIGNED NOT NULL COMMENT 'FK to active_status table';";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_email
										CHANGE customer_group_id customer_group_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to the CustomerGroup table',
										CHANGE auto_reply auto_reply TINYINT(1) NOT NULL COMMENT 'FK to active_status table';"; 
		$arrCommands[] = $arrCommand;
		
		// Declaration of FOREIGN KEY for ticketing_customer_group_email.customer_group_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_customer_group_email.customer_group_id -> CustomerGroup.Id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_email ADD CONSTRAINT fk_ticketing_customer_group_email_customer_group_id_cg_id FOREIGN KEY (customer_group_id) REFERENCES CustomerGroup(Id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_email DROP FOREIGN KEY fk_ticketing_customer_group_email_customer_group_id_cg_id, DROP INDEX fk_ticketing_customer_group_email_customer_group_id_cg_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_customer_group_email LEFT JOIN CustomerGroup ON ticketing_customer_group_email.customer_group_id = CustomerGroup.Id WHERE ticketing_customer_group_email.customer_group_id IS NOT NULL AND CustomerGroup.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;
		
		// Declaration of FOREIGN KEY for ticketing_customer_group_email.auto_reply
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_customer_group_email.auto_reply -> active_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_customer_group_email ADD CONSTRAINT fk_ticketing_customer_group_email_auto_reply_active_status_id FOREIGN KEY (auto_reply) REFERENCES active_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_customer_group_email DROP FOREIGN KEY fk_ticketing_customer_group_email_auto_reply_active_status_id, DROP INDEX fk_ticketing_customer_group_email_auto_reply_active_status_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_customer_group_email LEFT JOIN active_status ON ticketing_customer_group_email.auto_reply = active_status.id WHERE ticketing_customer_group_email.auto_reply IS NOT NULL AND active_status.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/**************************************** END - Modifications to the ticketing_customer_group_email table - END ***********************************************************/

		/********************************************* START - Modifications to the ticketing_status table - START ****************************************************************/
		// Declaration of FOREIGN KEY for ticketing_status.status_type_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_status.status_type_id -> ticketing_status_type.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_status ADD CONSTRAINT fk_ticketing_status_status_type_id_ticketing_status_type_id FOREIGN KEY (status_type_id) REFERENCES ticketing_status_type(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_status DROP FOREIGN KEY fk_ticketing_status_status_type_id_ticketing_status_type_id, DROP INDEX fk_ticketing_status_status_type_id_ticketing_status_type_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_status LEFT JOIN ticketing_status_type ON ticketing_status.status_type_id = ticketing_status_type.id WHERE ticketing_status.status_type_id IS NOT NULL AND ticketing_status_type.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/*********************************************** END - Modifications to the ticketing_status table - END ******************************************************************/

		/***************************************** START - Modifications to the ticketing_ticket_contact table - START ************************************************************/
		// Declare UNIQUE constraint on (ticketing_ticket_contact.ticket_id, ticketing_ticket_contact.contact_id)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on (ticketing_ticket_contact.ticket_id, ticketing_ticket_contact.contact_id)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_contact ADD UNIQUE un_ticketing_ticket_contact_ticket_id_contact_id (ticket_id, contact_id);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_contact DROP INDEX un_ticketing_ticket_contact_ticket_id_contact_id;";
		$arrCommand['check_sql']	= "SELECT ticket_id, contact_id, COUNT(*) FROM ticketing_ticket_contact GROUP BY ticket_id, contact_id HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_contact.ticket_id
		// Note: No implicit index named fk_ticketing_ticket_contact_ticket_id_ticketing_ticket_id was made because of the UNIQUE index un_ticketing_ticket_contact_ticket_id_contact_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_contact.ticket_id -> ticketing_ticket.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_contact ADD CONSTRAINT fk_ticketing_ticket_contact_ticket_id_ticketing_ticket_id FOREIGN KEY (ticket_id) REFERENCES ticketing_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_contact DROP FOREIGN KEY fk_ticketing_ticket_contact_ticket_id_ticketing_ticket_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_contact LEFT JOIN ticketing_ticket ON ticketing_ticket_contact.ticket_id = ticketing_ticket.id WHERE ticketing_ticket_contact.ticket_id IS NOT NULL AND ticketing_ticket.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_contact.contact_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_contact.contact_id -> ticketing_contact.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_contact ADD CONSTRAINT fk_ticketing_ticket_contact_contact_id_ticketing_contact_id FOREIGN KEY (contact_id) REFERENCES ticketing_contact(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_contact DROP FOREIGN KEY fk_ticketing_ticket_contact_contact_id_ticketing_contact_id, DROP INDEX fk_ticketing_ticket_contact_contact_id_ticketing_contact_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_contact LEFT JOIN ticketing_contact ON ticketing_ticket_contact.contact_id = ticketing_contact.id WHERE ticketing_ticket_contact.contact_id IS NOT NULL AND ticketing_contact.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/******************************************* END - Modifications to the ticketing_ticket_contact table - END **************************************************************/

		/***************************************** START - Modifications to the ticketing_ticket_service table - START ************************************************************/
		// Drop UNIQUE constraint ticket_service (ticketing_ticket_service.ticket_id, ticketing_ticket_service.service_id)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Drop UNIQUE constraint ticket_service (ticketing_ticket_service.ticket_id, ticketing_ticket_service.service_id)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_service DROP INDEX ticket_service;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_service ADD UNIQUE ticket_service (ticket_id, service_id);";
		$arrCommands[] = $arrCommand;

		// Declare UNIQUE constraint on (ticketing_ticket_service.ticket_id, ticketing_ticket_service.service_id)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of UNIQUE constraint on (ticketing_ticket_service.ticket_id, ticketing_ticket_service.service_id)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_service ADD UNIQUE un_ticketing_ticket_service_ticket_id_service_id (ticket_id, service_id);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_service DROP INDEX un_ticketing_ticket_service_ticket_id_service_id;";
		$arrCommand['check_sql']	= "SELECT ticket_id, service_id, COUNT(*) FROM ticketing_ticket_service GROUP BY ticket_id, service_id HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;
		
		// Declaration of FOREIGN KEY for ticketing_ticket_service.ticket_id
		// Note that an INDEX named fk_ticketing_ticket_service_ticket_id_ticketing_ticket_id does not get implicitly created because of un_ticketing_ticket_service_ticket_id_service_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_service.ticket_id -> ticketing_ticket.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_service ADD CONSTRAINT fk_ticketing_ticket_service_ticket_id_ticketing_ticket_id FOREIGN KEY (ticket_id) REFERENCES ticketing_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_service DROP FOREIGN KEY fk_ticketing_ticket_service_ticket_id_ticketing_ticket_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_service LEFT JOIN ticketing_ticket ON ticketing_ticket_service.ticket_id = ticketing_ticket.id WHERE ticketing_ticket_service.ticket_id IS NOT NULL AND ticketing_ticket.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_ticket_service.service_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_ticket_service.service_id -> Service.Id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_ticket_service ADD CONSTRAINT fk_ticketing_ticket_service_service_id_service_id FOREIGN KEY (service_id) REFERENCES Service(Id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_ticket_service DROP FOREIGN KEY fk_ticketing_ticket_service_service_id_service_id, DROP INDEX fk_ticketing_ticket_service_service_id_service_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_ticket_service LEFT JOIN Service ON ticketing_ticket_service.service_id = Service.Id WHERE ticketing_ticket_service.service_id IS NOT NULL AND Service.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/******************************************* END - Modifications to the ticketing_ticket_service table - END **************************************************************/

		/********************************************** START - Modifications to the ticketing_user table - START *****************************************************************/
		// Drop UNIQUE constraint employee_id (ticketing_user.employee_id)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Drop UNIQUE constraint employee_id (ticketing_user.employee_id)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_user DROP INDEX employee_id;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_user ADD UNIQUE employee_id (employee_id);";
		$arrCommands[] = $arrCommand;

		// Declare UNIQUE constraint on (ticketing_user.employee_id)
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declare UNIQUE constraint on (ticketing_user.employee_id)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_user ADD UNIQUE un_ticketing_user_employee_id (employee_id);";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_user DROP INDEX un_ticketing_user_employee_id;";
		$arrCommand['check_sql']	= "SELECT employee_id, COUNT(*) FROM ticketing_user GROUP BY employee_id HAVING COUNT(*) > 1 LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_user.employee_id
		// Note that an INDEX named fk_ticketing_user_employee_id_employee_id does not get implicitly created because of un_ticketing_user_employee_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_user.employee_id -> Employee.Id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_user ADD CONSTRAINT fk_ticketing_user_employee_id_employee_id FOREIGN KEY (employee_id) REFERENCES Employee(Id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_user DROP FOREIGN KEY fk_ticketing_user_employee_id_employee_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_user LEFT JOIN Employee ON ticketing_user.employee_id = Employee.Id WHERE ticketing_user.employee_id IS NOT NULL AND Employee.Id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_user.permission_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_user.permission_id -> ticketing_user_permission.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_user ADD CONSTRAINT fk_ticketing_user_permission_id_ticketing_user_permission_id FOREIGN KEY (permission_id) REFERENCES ticketing_user_permission(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_user DROP FOREIGN KEY fk_ticketing_user_permission_id_ticketing_user_permission_id, DROP INDEX fk_ticketing_user_permission_id_ticketing_user_permission_id;";
		$arrCommand['check_sql']	= "SELECT * FROM ticketing_user LEFT JOIN ticketing_user_permission ON ticketing_user.permission_id = ticketing_user_permission.id WHERE ticketing_user.permission_id IS NOT NULL AND ticketing_user_permission.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/************************************************ END - Modifications to the ticketing_user table - END *******************************************************************/

		// Run each Check SQL statement (these check that the step should be doable)
		$intStep = 0;

		foreach ($arrCommands as $i=>$arrCommand)
		{
			$intStep = $i + 1;
			$this->outputMessage("\nChecking Step {$intStep} - {$arrCommand['step_name']}... ");

			if (array_key_exists('check_sql', $arrCommand))
			{
				$fltStartTime	= microtime(true);
				$result			= $dbAdmin->query($arrCommand['check_sql']);
				$strTimeTaken	= number_format(microtime(true) - $fltStartTime, 3, '.', '');
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . " Failed Check for Step {$intStep} " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
				}
				if ($result->numRows() > 0)
				{
					// At least 1 row was returned, signifying there were problems
					$this->outputMessage("FAIL!!!\nThe following query returned records signifying that this step will fail if actually executed.  Please rectify the issue before running this rollout script again.\n\n{$arrCommand['check_sql']}\n\n");
					throw new Exception("Preliminary check failed");
				}
				else
				{
					// No Rows were returned signifying that there aren't any problems
					$this->outputMessage("PASS ({$strTimeTaken} sec)\n");
				}
			}
			else
			{
				// There is no check for this step
				$this->outputMessage("No Check Required\n");
			}
		}
		
		// Now run each command
		$intStep = 0;
		foreach ($arrCommands as $i=>$arrCommand)
		{
			$intStep = $i + 1;
			$this->outputMessage("\nStep {$intStep} - {$arrCommand['step_name']}\n");

			$result = $dbAdmin->query($arrCommand['rollout_sql']);
			if (MDB2::isError($result))
			{
				throw new Exception(__CLASS__ . " Failed Step {$intStep} " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			$this->rollbackSQL[] = $arrCommand;
		}

		$this->outputMessage("\nAll Done.\n");
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			$this->outputMessage("\nRollback required!!!\n");
			
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				// Display which step is being rolled back
				$intStep = $l + 1;
				$this->outputMessage("\nUndoing Step {$intStep}\t- ". $this->rollbackSQL[$l]['step_name'] ."\n");
				
				$result = $dbAdmin->query($this->rollbackSQL[$l]['rollback_sql']);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l]['rollback_sql'] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>