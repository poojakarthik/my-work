<?php

/**
 * Version 6 (six) of database update.
 * This version: -
 *	1:	Creates account_status table and populates with default data
 *	2:	Creates credit_control_status table and populates with default data
 *	3:	Alters account table by adding credit_control_status column and index
 *	4:	Alters and updates payment_terms table to allow tracking of changes
 *	5:	Creates automatic_invoice_action table and populates with default data
 *	6:	Creates automatic_barring_status table and populates with default data
 *	7:	Alters account table to reference previous 2 tables
 *  8:	Creates automatic_invoice_action_history table
 *  9:	Creates automatic_barring_status_history table
 * 10:	Creates credit_control_status_history table
 * 11:	Creates account_status_history table
 */

class Flex_Rollout_Version_000006 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "
			CREATE TABLE account_status (
			  id bigint(20) unsigned NOT NULL COMMENT 'Id for the status',
			  name varchar(50) NOT NULL COMMENT 'Name of the status',
			  can_bar tinyint(1) unsigned NOT NULL default '1' COMMENT 'Whether or not the account can be barred',
			  send_late_notice tinyint(1) unsigned NOT NULL default '1' COMMENT 'Whether or not to send late notices for account',
			  description varchar(255) NOT NULL COMMENT 'Description of the status',
			  PRIMARY KEY  (id),
			  UNIQUE KEY name (name)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Credit Control Status for accounts' ;
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to account_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE account_status;';

		$strSQL = "
			INSERT INTO account_status (id, name, can_bar, send_late_notice, description) VALUES
			(0, 'Active', 1, 1, 'The account is active.'),
			(1, 'Archived', 0, 0, 'The account has been archived.'),
			(2, 'Closed', 1, 1, 'The account has been closed.'),
			(3, 'Debt Collection', 0, 0, 'The account is with debt collection.'),
			(4, 'Suspended', 0, 0, 'The account has been suspended.')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate account_status table. ' . $qryQuery->Error());
		}

		$strSQL = "
			CREATE TABLE credit_control_status (
			  id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the status',
			  name varchar(50) NOT NULL COMMENT 'Name of the status',
			  can_bar tinyint(1) unsigned NOT NULL default '1' COMMENT 'Whether or not the account can be barred',
			  send_late_notice tinyint(1) unsigned NOT NULL default '1' COMMENT 'Whether or not to send late notices for account',
			  description varchar(255) NOT NULL COMMENT 'Description of the status',
			  PRIMARY KEY  (id),
			  UNIQUE KEY name (name)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Credit Control Status for accounts' AUTO_INCREMENT=5 ;
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create credit_control_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE credit_control_status;';

		$strSQL = "
			INSERT INTO credit_control_status (id, name, can_bar, send_late_notice, description) VALUES
			(1, 'Up to date', 1, 1, 'Can be barred.'),
			(2, 'Extension', 0, 1, 'Do not bar.'),
			(3, 'Sending to Austral', 0, 0, 'No late notices or barring.'),
			(4, 'With Austral', 0, 0, 'No late notices or barring.')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate credit_control_status table. ' . $qryQuery->Error());
		}

		$strSQL = "ALTER TABLE Account ADD credit_control_status BIGINT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'FK to credit_control_status.id' AFTER Archived";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter Account table (2). ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='ALTER TABLE Account DROP credit_control_status ;';

		$strSQL = "ALTER TABLE Account ADD INDEX ( credit_control_status )";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add index to Account table. ' . $qryQuery->Error());
		}

		$strSQL = " ALTER TABLE payment_terms 
					DROP automatic_barring_days, 
					ADD minimum_balance_to_pursue decimal(4,2) unsigned NOT NULL DEFAULT 27 COMMENT 'The minimum balance required for automatic notice generation to be applied',
					ADD employee bigint(20) unsigned NULL DEFAULT NULL COMMENT 'Employee who effected the change',
					ADD created DATETIME NULL DEFAULT NULL COMMENT 'Date/Time at which the payment terms were created'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter "payment_terms" table (1). ' . $qryQuery->Error());
		}
		// Don't need to worry about restoring the automatic_barring_days column as it has never been used!
		$this->rollbackSQL[] ='ALTER TABLE payment_terms DROP minimum_balance_to_pursue, DROP employee, DROP created, ADD automatic_barring_days SMALLINT UNSIGNED NOT NULL COMMENT \'Number of days after invoicing when the account should be automatically barred\';';

		$minimum_balance_to_pursue = $this->getUserResponseDecimal("What is the minimum balance to send late notices for (e.g. 27.01)?");
		$strSQL = "UPDATE payment_terms SET minimum_balance_to_pursue = $minimum_balance_to_pursue, created = NOW()";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to update "payment_terms" table. ' . $qryQuery->Error());
		}

		$strSQL = " ALTER TABLE payment_terms 
					CHANGE created created DATETIME NOT NULL COMMENT 'Date/Time at which the payment terms were created'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter "payment_terms" table (2). ' . $qryQuery->Error());
		}

		$strSQL = "
			CREATE TABLE automatic_invoice_action (
				id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the action',
				name varchar(50) NOT NULL COMMENT 'Name of the action',
				description varchar(255) NOT NULL COMMENT 'Description of the action',
				PRIMARY KEY  (id),
				UNIQUE KEY name (name)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Automatic invoice actions' AUTO_INCREMENT=5
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create automatic_invoice_action table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE automatic_invoice_action;';

		$strSQL = "
			INSERT INTO automatic_invoice_action (id, name, description) VALUES
			(1, 'None', 'None'),
			(2, 'Overdue Notice', 'Overdue Notice sent'),
			(3, 'Suspension Notice', 'Suspension Notice sent'),
			(4, 'Final Demand', 'Final Demand sent')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to insert into automatic_invoice_action table. ' . $qryQuery->Error());
		}

		$strSQL = "
			CREATE TABLE automatic_barring_status (
				id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the status',
				name varchar(50) NOT NULL COMMENT 'Name of the status',
				description varchar(255) NOT NULL COMMENT 'Description of the status',
				PRIMARY KEY	(id),
				UNIQUE KEY name (name)
			) ENGINE=InnoDB	DEFAULT CHARSET=utf8 COMMENT='Automatic barring statuses' AUTO_INCREMENT=4
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create automatic_barring_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE automatic_barring_status;';

		$strSQL = "
			INSERT INTO automatic_barring_status (id, name, description) VALUES
			(1, 'None', 'None'),
			(2, 'Barred', 'Barred'),
			(3, 'Unbarred', 'Unbarred')
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to insert into automatic_barring_status table. ' . $qryQuery->Error());
		}

		$strSQL = "
			ALTER TABLE Account
			ADD last_automatic_invoice_action BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Last automatic invoice action. FK to automatic_invoice_action.id',
			ADD last_automatic_invoice_action_datetime DATETIME NULL DEFAULT NULL COMMENT 'Time of last automatic invoice action',
			ADD automatic_barring_status BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Automatic barring status. FK to automatic_barring_status.id',
			ADD automatic_barring_datetime DATETIME NULL DEFAULT NULL COMMENT 'Time of last automatic barring status change'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter Account table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='ALTER TABLE Account DROP last_automatic_invoice_action, DROP last_automatic_invoice_action_datetime, DROP automatic_barring_status, DROP automatic_barring_datetime;';

		$strSQL = "
			CREATE TABLE automatic_invoice_action_history (
				id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique identifier for the historic event',
				account bigint(20) unsigned NOT NULL COMMENT 'Affected account',
				from_action bigint(20) unsigned NOT NULL COMMENT 'The original automatic_invoice_action.id',
				to_action bigint(20) unsigned NOT NULL COMMENT 'The new automatic_invoice_action.id',
				reason VARCHAR(255) NOT NULL COMMENT 'Reason for change',
				change_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/Time of the change to this action',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Automatic invoice action change history'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create automatic_invoice_action_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE automatic_invoice_action_history;';

		$strSQL = "
			CREATE TABLE automatic_barring_status_history (
				id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique identifier for the historic event',
				account bigint(20) unsigned NOT NULL COMMENT 'Affected account',
				from_status bigint(20) unsigned NOT NULL COMMENT 'The original automatic_barring_status.id',
				to_status bigint(20) unsigned NOT NULL COMMENT 'The new automatic_barring_status.id',
				reason VARCHAR(255) NOT NULL COMMENT 'Reason for change',
				change_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/Time of the change to this status',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Automatic barring status change history'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create automatic_barring_status_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE automatic_barring_status_history;';

		$strSQL = "
			CREATE TABLE credit_control_status_history (
				id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique identifier for the historic event',
				account bigint(20) unsigned NOT NULL COMMENT 'Affected account',
				from_status bigint(20) unsigned NOT NULL COMMENT 'The original credit_control_status.id',
				to_status bigint(20) unsigned NOT NULL COMMENT 'The new credit_control_status.id',
				employee bigint(20) unsigned NOT NULL COMMENT 'Employee who effected the change',
				change_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/Time of the change to this status',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Credit control status change history'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create credit_control_status_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE credit_control_status_history;';

		$strSQL = "
			CREATE TABLE account_status_history (
				id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique identifier for the historic event',
				account bigint(20) unsigned NOT NULL COMMENT 'Affected account',
				from_status bigint(20) unsigned NOT NULL COMMENT 'The original account status (Account.Archived)',
				to_status bigint(20) unsigned NOT NULL COMMENT 'The new account status (Account.Archived)',
				employee bigint(20) unsigned NOT NULL COMMENT 'Employee who effected the change',
				change_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/Time of the change to this status',
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Account status change history'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create account_status_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='DROP TABLE account_status_history;';

		$strSQL = "
			ALTER TABLE InvoiceRun 
				ADD automatic_overdue_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic overdue notice run ran',
				ADD automatic_suspension_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic suspension notice run ran',
				ADD automatic_final_demand_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic final demand run ran',
				ADD scheduled_automatic_bar_datetime DATETIME NULL DEFAULT NULL COMMENT 'User configured (manually) date/time after which automatic barring should be applied',
				ADD automatic_bar_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic barring was applied'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter InvoiceRun table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] ='ALTER TABLE InvoiceRun DROP automatic_overdue_datetime, DROP automatic_suspension_datetime, DROP automatic_final_demand_datetime, DROP scheduled_automatic_bar_datetime, DROP automatic_bar_datetime;';

		// Find the last InvoiceRun.Id
		$strSQL = 'SELECT MAX(Id) FROM InvoiceRun';
		$result = $qryQuery->Execute($strSQL);
		if (!$result)
		{
			throw new Exception(__CLASS__ . ' Failed to update InvoiceRun table. ' . $qryQuery->Error());
		}
		$row = mysqli_fetch_row($result);
		mysqli_free_result($result);
		$maxId = $row[0];
		if ($maxId !== NULL)
		{
			// Apply default dates to all existing invoiceruns but the latest one to prevent late notices & barring being applied to them
			$strSQL = "
				UPDATE InvoiceRun 
				SET 
					automatic_overdue_datetime = '1970-01-01 00:00:00',
					automatic_suspension_datetime = '1970-01-01 00:00:00',
					automatic_final_demand_datetime = '1970-01-01 00:00:00',
					scheduled_automatic_bar_datetime = '1970-01-01 00:00:00',
					automatic_bar_datetime = '1970-01-01 00:00:00'
				WHERE
					Id < $maxId
			";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to update InvoiceRun table. ' . $qryQuery->Error());
			}
		}
	}

	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
