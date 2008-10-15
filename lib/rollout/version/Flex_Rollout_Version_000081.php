<?php

/**
 * Version 81 of database update.
 * This version: -
 *	1:	Create day table
 *	2:	Populate day table
 *	3:	Create automated_invoice_run_process_config table
 *	4:	Create automated_invoice_run_process table
 *	//5:	Create automated_invoice_run_process_account table
 *	//6:	Create automated_invoice_run_message_delivery_outcome table
 *	//7:	Populate automated_invoice_run_message_delivery_outcome table
 */

class Flex_Rollout_Version_000081 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Create day table
		$strSQL = "
			CREATE TABLE day (
			  id bigint(20) NOT NULL auto_increment,
			  name varchar(255) NOT NULL COMMENT 'Name of the day',
			  description varchar(512) NOT NULL COMMENT 'Description of the day',
			  const_name varchar(255) NOT NULL COMMENT 'Constant name for this day',
			  PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Days of the week: ISO-8601 numeric representation' AUTO_INCREMENT=1;
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to create day table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE day;";

		// 2:	Populate day table
		$strSQL = "
			INSERT INTO day (id, name, description, const_name) VALUES 
				(1, 'Mon', 'Monday', 'DAY_MONDAY'),
				(2, 'Tues', 'Tuesday', 'DAY_TUESDAY'),
				(3, 'Wed', 'Wednesday', 'DAY_WEDNESDAY'),
				(4, 'Thu', 'Thursday', 'DAY_THURSDAY'),
				(5, 'Fri', 'Friday', 'DAY_FRIDAY'),
				(6, 'Sat', 'Saturday', 'DAY_SATURDAY'),
				(7, 'Sun', 'Sunday', 'DAY_SUNDAY');
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to populate day table. ' . $result->getMessage());
		}

		// 3:	Create automated_invoice_run_process_config table
		$strSQL = "
			CREATE TABLE automated_invoice_run_process_config
			(
				id bigint(20) UNSIGNED NOT NULL auto_increment,
				customer_group_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to CustomerGroup table',
				enabled tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Whether or not this feature is enabled',
				days_from_invoice_normal tinyint(1) UNSIGNED NOT NULL DEFAULT 21 COMMENT 'Default number of days from invoicing to start process for normal accs',
				days_from_invoice_first tinyint(1) UNSIGNED NOT NULL DEFAULT 24 COMMENT 'Default number of days from invoicing to start process for fist timers',
				days_from_invoice_vip tinyint(1) UNSIGNED NOT NULL DEFAULT 26 COMMENT 'Default number of days from invoicing to start process for VIP accs',
				listing_time_of_day TIME DEFAULT '00:00:00' COMMENT 'Time of day to list service',
				barring_time_of_day TIME DEFAULT '00:00:00' COMMENT 'Time of day to bar services',
				barring_days VARCHAR(20) DEFAULT '' COMMENT 'csv list of day.ids for days on which to bar services',
				max_barrings_per_day int unsigned NOT NULL COMMENT 'Maximum number of accounts to bar on each day of barring',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='TEMPORARY config table for staggered barring' AUTO_INCREMENT=0;
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to create automated_invoice_run_process_config table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE automated_invoice_run_process_config;";

		// 4:	Create automated_invoice_run_process table
		$strSQL = "
			CREATE TABLE automated_invoice_run_process
			(
				id bigint(20) NOT NULL auto_increment,
				invoice_run_id bigint(20) unsigned NOT NULL COMMENT 'FK to InvoiceRun table',
				commencement_date_normal DATE NOT NULL COMMENT 'Date on which to commence process (normal customers)',
				commencement_date_first DATE NOT NULL COMMENT 'Date on which to commence process (first timers)',
				commencement_date_vip DATE NOT NULL COMMENT 'Date on which to commence process (VIP customers)',
				last_processed_date DATE DEFAULT NULL COMMENT 'Date on which last processed',
				last_listing_date DATE DEFAULT NULL COMMENT 'Date on which last listed',
				completed_date DATE DEFAULT NULL COMMENT 'Date on which process completed',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='TEMPORARY table for staggered barring' AUTO_INCREMENT=0;
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to create automated_invoice_run_process table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE automated_invoice_run_process;";
/*
		// 5:	Create automated_invoice_run_process_account table
		$strSQL = "
			CREATE TABLE automated_invoice_run_process_account
			(
				id bigint(20) NOT NULL auto_increment,
				automated_invoice_run_process_id bigint(20) unsigned NOT NULL COMMENT 'FK to automated_invoice_run_process table',
				account_id bigint(20) unsigned NOT NULL COMMENT 'FK to Account table',
				barring_scheduled_date DATE NOT NULL COMMENT 'Date on which barring is set to be applied',
				barring_actioned_datetime DATETIME NOT NULL COMMENT 'Date and time that barring was applied',
				barring_delivery_outcome_id bigint(20) unsigned NOT NULL COMMENT 'Result of sending message when account was barred',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='TEMPORARY table for staggered barring accounts' AUTO_INCREMENT=0;
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to create automated_invoice_run_process_account table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE automated_invoice_run_process_account;";

		// 6:	Create automated_invoice_run_message_delivery_outcome table
		$strSQL = "
			CREATE TABLE automated_invoice_run_message_delivery_outcome
			(
				id bigint(20) NOT NULL auto_increment,
				name VARCHAR(255) NOT NULL COMMENT 'Name of the message delivery outcome',
				description VARCHAR(512) NOT NULL COMMENT 'Description of this message delivery outcome',
				const_name VARCHAR(255) NOT NULL COMMENT 'Constant name for this message delivery outcome',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='TEMPORARY table message delivery outcomes' AUTO_INCREMENT=0;
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to create automated_invoice_run_message_delivery_outcome table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE automated_invoice_run_message_delivery_outcome;";

		// 7:	Populate automated_invoice_run_message_delivery_outcome table
		$strSQL = "
			INSERT INTO automated_invoice_run_message_delivery_outcome (name, description, const_name) VALUES 
				('Unsent', 'Sunday', 'DAY_SUNDAY'),
				('Failed', 'Sunday', 'DAY_SUNDAY'),
				('Rejected', 'Sunday', 'DAY_SUNDAY'),
				('Retrying', 'Sunday', 'DAY_SUNDAY'),
				('Accepted', 'Sunday', 'DAY_SUNDAY'),
				('Sun', 'Sunday', 'DAY_SUNDAY'),
				('Sun', 'Sunday', 'DAY_SUNDAY');
		";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' failed to populate automated_invoice_run_message_delivery_outcome table. ' . $result->getMessage());
		}
*/
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>
