<?php

/**
 * Version 15 (fifteen) of database update.
 * This version: -
 *	1:	Add automatic_invoice_action_dependency table
 *	2:	Add automatic_invoice_run_event table
 *	3:	Adds days_from_invoice and can_schedule columns to automatic_invoice_action table
 *	4:	Inserts new records into the automatic_invoice_action table
 *	5:	Populates the new can_schedule column of the automatic_invoice_action table
 *	6:	Moves data from payment_terms table to new column in automatic_invoice_action table
 *	7:	Corrects the descriptions in the automatic_barring_status table (mistake in rollout script 6)
 *	8:	Copies data from InvoiceRun to automatic_invoice_run_event
 *	9:	Drops now unused columns from InvoiceRun table
 *	10:	Drops now unused columns from payment_terms table
 */

class Flex_Rollout_Version_000015 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Create email_notification Table
		$strSQL = " CREATE TABLE IF NOT EXISTS automatic_invoice_action_dependency (
					  id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the automatic invoice action dependency',
					  dependent_automatic_invoice_action_id bigint(20) unsigned NOT NULL COMMENT 'Id of the dependent automatic invoice action',
					  prerequisite_automatic_invoice_action_id bigint(20) unsigned NOT NULL COMMENT 'Id of the prerequisite automatic invoice action',
					  PRIMARY KEY (id),
					  UNIQUE KEY invoice_action_dependency (dependent_automatic_invoice_action_id, prerequisite_automatic_invoice_action_id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Automatic invoice action dependencies';
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create automatic_invoice_action_dependency table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE automatic_invoice_action_dependency";

		// Create automatic_invoice_run_event Table
		$strSQL = " CREATE TABLE IF NOT EXISTS automatic_invoice_run_event (
					  id bigint(20) unsigned NOT NULL auto_increment COMMENT 'Id for the automatic invoice run event',
					  automatic_invoice_action_id bigint(20) unsigned NOT NULL COMMENT 'Id of the automatic invoice action for the event',
					  invoice_run_id bigint(20) unsigned NOT NULL COMMENT 'Id of the invoice run for the event',
					  scheduled_datetime datetime default NULL COMMENT 'Date/Time at which the action can be taken',
					  actioned_datetime datetime default NULL COMMENT 'Date/Time at which the action was taken',
					  update_user_id bigint(20) unsigned default NULL COMMENT 'The user who scheduled the event',
					  update_datetime datetime default NULL COMMENT 'Date/Time at which the user scheduled the event',
					  PRIMARY KEY  (id),
					  UNIQUE KEY invoice_run_action (automatic_invoice_action_id, invoice_run_id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email address usage';
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create automatic_invoice_run_event table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE automatic_invoice_run_event";

		$strSQL = " ALTER TABLE automatic_invoice_action_history ADD invoice_run_id bigint(20) unsigned NOT NULL COMMENT 'Id of the invoice run for the event'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter automatic_invoice_action_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE automatic_invoice_action_history DROP invoice_run_id";

		$strSQL = " ALTER TABLE automatic_invoice_action ADD days_from_invoice SMALLINT(5) DEFAULT 0,
					ADD can_schedule tinyint(3) default 0 COMMENT 'Whether or not this action can be scheduled',
					ADD response_days smallint(5) default '7' COMMENT 'Number of days from event that an external response must be made in'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter automatic_invoice_action table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE automatic_invoice_action DROP days_from_invoice, DROP can_schedule, DROP response_days";

		// Add the friendly reminder invoice action
		$strSQL = " INSERT INTO automatic_invoice_action (name, description, const_name, days_from_invoice, response_days)
					VALUES
					('Overdue Notice List'		, 'Overdue Notice list sent'	, 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST'	, 21, 7),
					('Suspension Notice List'	, 'Suspension Notice list sent'	, 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST'	, 28, 7),
					('Final Demand List'		, 'Final Demand list sent'		, 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST'		, 35, 7),
					('Friendly Reminder List'	, 'Friendly Reminder list sent'	, 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST'	, 16, 3),
					('Friendly Reminder'		, 'Friendly Reminder sent'		, 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER'		, 16, 3),
					('Late Fees List'			, 'Late Fees list sent'			, 'AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST'			, 17, 7),
					('Late Fees'				, 'Late Fees applied'			, 'AUTOMATIC_INVOICE_ACTION_LATE_FEES'				, 17, 7),
					('Automatic Barring List'	, 'Automatic Barring list sent'	, 'AUTOMATIC_INVOICE_ACTION_BARRING_LIST'			, 28, 7),
					('Automatic Barring'		, 'Automatic Barring applied'	, 'AUTOMATIC_INVOICE_ACTION_BARRING'				, 28, 7),
					('Automatic Unbarring'		, 'Automatic Unbarring applied'	, 'AUTOMATIC_INVOICE_ACTION_UNBARRING'				, 0, 0)
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to insert automatic invoice actions. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM automatic_invoice_action WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST',
								'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST'	, 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST', 
								'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST'	, 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER', 
								'AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST'			, 'AUTOMATIC_INVOICE_ACTION_LATE_FEES', 
								'AUTOMATIC_INVOICE_ACTION_BARRING_LIST'				, 'AUTOMATIC_INVOICE_ACTION_BARRING',
								'AUTOMATIC_INVOICE_ACTION_UNBARRING')";

		// Update the can_schedule field (all 1 except for AUTOMATIC_INVOICE_ACTION_UNBARRING and AUTOMATIC_INVOICE_ACTION_NONE)
		$strSQL = " UPDATE automatic_invoice_action SET can_schedule = 1 WHERE const_name NOT IN ('AUTOMATIC_INVOICE_ACTION_NONE', 'AUTOMATIC_INVOICE_ACTION_UNBARRING') ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to copy overdue_notice_days value from payment_terms table to automatic_invoice_action table. ' . $qryQuery->Error());
		}

		// Need to move the data over from payment_terms table
		$strSQL = " UPDATE automatic_invoice_action SET days_from_invoice = (SELECT overdue_notice_days FROM payment_terms ORDER BY id DESC LIMIT 0,1) WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE', 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST') ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to copy overdue_notice_days value from payment_terms table to automatic_invoice_action table. ' . $qryQuery->Error());
		}
		$strSQL = " UPDATE automatic_invoice_action SET days_from_invoice = (SELECT suspension_notice_days FROM payment_terms ORDER BY id DESC LIMIT 0,1) WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE', 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST', 'AUTOMATIC_INVOICE_ACTION_BARRING_LIST', 'AUTOMATIC_INVOICE_ACTION_BARRING') ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to copy suspension_notice_days value from payment_terms table to automatic_invoice_action table. ' . $qryQuery->Error());
		}
		$strSQL = " UPDATE automatic_invoice_action SET days_from_invoice = (SELECT final_demand_notice_days FROM payment_terms ORDER BY id DESC LIMIT 0,1) WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND', 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to copy final_demand_notice_days value from payment_terms table to automatic_invoice_action table. ' . $qryQuery->Error());
		}
		$strSQL = " UPDATE automatic_invoice_action SET days_from_invoice = (SELECT (overdue_notice_days - 2) FROM payment_terms ORDER BY id DESC LIMIT 0,1) WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_LATE_FEES', 'AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST') ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to copy overdue_notice_days value from payment_terms table to automatic_invoice_action table for late fees. ' . $qryQuery->Error());
		}
		$strSQL = " UPDATE automatic_invoice_action SET days_from_invoice = (SELECT (overdue_notice_days - 5) FROM payment_terms ORDER BY id DESC LIMIT 0,1) WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER', 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST') ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to copy overdue_notice_days value from payment_terms table to automatic_invoice_action table for friendly notices. ' . $qryQuery->Error());
		}

		// Need to populate the automatic_invoice_action_dependency table
		$strSQL = "INSERT INTO automatic_invoice_action_dependency (dependent_automatic_invoice_action_id, prerequisite_automatic_invoice_action_id)
						VALUES
						 ((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER'), 	(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE'), 		(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'), 	(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND'), 		(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_LATE_FEES'), 			(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_BARRING'), 				(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_BARRING_LIST'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE'), 		(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'), 	(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND'), 		(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_BARRING'), 				(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'))
						,((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_LATE_FEES'), 			(SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER'))
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate automatic_invoice_action_dependency table. ' . $qryQuery->Error());
		}

		// Fix descriptions in automatic_barring_status table
		$strSQL = "UPDATE automatic_barring_status SET description = name";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to update automatic_barring_status table. ' . $qryQuery->Error());
		}

		// Move the data currently recorded in the InvoiceRun table automatic_xxx fields to the new automatic_invoice_run_event table
		$strSQL = "
				SELECT Id, automatic_overdue_datetime, automatic_suspension_datetime, automatic_final_demand_datetime, scheduled_automatic_bar_datetime, automatic_bar_datetime
				FROM InvoiceRun 
				WHERE LENGTH(InvoiceRun) = 14
				AND automatic_overdue_datetime IS NOT NULL
		";
		$fetch = new QueryFetch($strSQL, 100);
		if (($invoiceRun = $fetch->Execute($strSQL)) === FALSE)
		{
			throw new Exception_Database(__CLASS__ . ' Failed to select existing automatic invoice action data from InvoiceRun table. ' . $fetch->Error());
		}
		$strSQLStart = "INSERT INTO automatic_invoice_run_event (automatic_invoice_action_id, invoice_run_id, scheduled_datetime, actioned_datetime) VALUES ";
		if ($invoiceRun)
		{
			do
			{
				$invoiceRunId = $invoiceRun['Id'];
				if ($invoiceRun['automatic_overdue_datetime'])
				{
					$date = $invoiceRun['automatic_overdue_datetime'];
					$strSQL = $strSQLStart . "((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE'), $invoiceRunId, '$date', '$date')";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to transfer automatic_overdue_datetime from InvoiceRun $invoiceRunId to automatic_invoice_run_event table. " . $qryQuery->Error());
					}
				}
				if ($invoiceRun['automatic_suspension_datetime'])
				{
					$date = $invoiceRun['automatic_suspension_datetime'];
					$strSQL = $strSQLStart . "((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'), $invoiceRunId, '$date', '$date')";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to transfer automatic_suspension_datetime from InvoiceRun $invoiceRunId to automatic_invoice_run_event table. " . $qryQuery->Error());
					}
				}
				else 
				{
					$strSQL = "INSERT INTO automatic_invoice_run_event (automatic_invoice_action_id, invoice_run_id) VALUES ((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE'), $invoiceRunId)";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to create suspension notice automatic_invoice_run_event record for InvoiceRun $invoiceRunId. " . $qryQuery->Error());
					}
				}
				if ($invoiceRun['automatic_final_demand_datetime'])
				{
					$date = $invoiceRun['automatic_final_demand_datetime'];
					$strSQL = $strSQLStart . "((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND'), $invoiceRunId, '$date', '$date')";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to transfer automatic_final_demand_datetime from InvoiceRun $invoiceRunId to automatic_invoice_run_event table. " . $qryQuery->Error());
					}
				}
				else 
				{
					$strSQL = "INSERT INTO automatic_invoice_run_event (automatic_invoice_action_id, invoice_run_id) VALUES ((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND'), $invoiceRunId)";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to create final demand automatic_invoice_run_event record for InvoiceRun $invoiceRunId. " . $qryQuery->Error());
					}
				}
				if ($invoiceRun['scheduled_automatic_bar_datetime'])
				{
					$date = $invoiceRun['automatic_bar_datetime'] ? "'".$invoiceRun['automatic_bar_datetime']."'" : "NULL";
					$scheduledDate = $invoiceRun['scheduled_automatic_bar_datetime'];
					$strSQL = $strSQLStart . "((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_BARRING'), $invoiceRunId, '$scheduledDate', $date)";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to transfer scheduled_automatic_bar_datetime from InvoiceRun $invoiceRunId to automatic_invoice_run_event table. " . $qryQuery->Error());
					}
				}
				else 
				{
					$strSQL = "INSERT INTO automatic_invoice_run_event (automatic_invoice_action_id, invoice_run_id) VALUES ((SELECT id FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_BARRING'), $invoiceRunId)";
					if (!$qryQuery->Execute($strSQL))
					{
						throw new Exception_Database(__CLASS__ . " Failed to create barring automatic_invoice_run_event record for InvoiceRun $invoiceRunId. " . $qryQuery->Error());
					}
				}
			}
			while($invoiceRun = $fetch->FetchNext($strSQL));
		}

		// We can now drop the tables from the InvoiceRun table
		$strSQL = "ALTER TABLE InvoiceRun DROP automatic_overdue_datetime, DROP automatic_suspension_datetime, DROP automatic_final_demand_datetime, DROP scheduled_automatic_bar_datetime, DROP automatic_bar_datetime";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . " Failed to drop disused columns from InvoiceRun table. " . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "
			ALTER TABLE InvoiceRun 
				ADD automatic_overdue_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic overdue notice run ran',
				ADD automatic_suspension_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic suspension notice run ran',
				ADD automatic_final_demand_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic final demand run ran',
				ADD scheduled_automatic_bar_datetime DATETIME NULL DEFAULT NULL COMMENT 'User configured (manually) date/time after which automatic barring should be applied',
				ADD automatic_bar_datetime DATETIME NULL DEFAULT NULL COMMENT 'Date/time at which automatic barring was applied'
		";

		// This is inadequate! It does not repopulate the dropped columns in rollback!
		$strSQL = " ALTER TABLE payment_terms DROP overdue_notice_days, DROP suspension_notice_days, DROP final_demand_notice_days";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to drop columns from payment_terms table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE payment_terms 
			ADD overdue_notice_days smallint(5) unsigned default 21 NOT NULL COMMENT 'Number of days after invoicing when an overdue notice should be sent',
			ADD suspension_notice_days smallint(5) unsigned default 28 NOT NULL COMMENT 'Number of days after invoicing when a suspension notice should be sent',
			ADD final_demand_notice_days smallint(5) unsigned default 35 NOT NULL COMMENT 'Number of days after invoicing when a final demand notice should be sent'
		";

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
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>