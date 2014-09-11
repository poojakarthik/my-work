<?php

/**
 * Version 180 of database update.
 * This version: -
 *	
 *	1:	Create recurring_charge_status table
 *	2:	Populate recurring_charge_status table
 *	3:	Create charge_recurring_charge table
 *	4:	Populate charge_recurring_charge table
 *	5:	Set RecurringCharge.ApprovedBy to the System Employee Id for all records that are currently set to NULL
 *	6:	Add the recurring_charge_status_id column to the RecurringCharge table and set it appropriately
 *	7:	Add is_auto_approved column to RecurringChargeType table (defaults to 1 (true))
 */

class Flex_Rollout_Version_000180 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Create recurring_charge_status table
		$strSQL = "	CREATE TABLE recurring_charge_status
					(
						id			INTEGER	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)		NOT NULL					COMMENT 'Name',
						description	VARCHAR(256)		NOT NULL					COMMENT 'Description',
						system_name	VARCHAR(256)		NOT NULL					COMMENT 'System Name',
						const_name	VARCHAR(256)		NOT NULL					COMMENT 'Constant Alias',
					
						CONSTRAINT	pk_recurring_charge_status_id			PRIMARY KEY	(id),
						CONSTRAINT	un_recurring_charge_status_name			UNIQUE KEY	(name),
						CONSTRAINT	un_recurring_charge_status_system_name	UNIQUE KEY	(system_name),
						CONSTRAINT	un_recurring_charge_status_const_name	UNIQUE KEY	(const_name)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 1 - Create recurring_charge_status table. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS recurring_charge_status;";


		//	2:	Populate recurring_charge_status table
		$strSQL = "	INSERT INTO recurring_charge_status (name, description, system_name, const_name)
					VALUES
					('Awaiting Approval', 'Awaiting Approval', 'AWAITING_APPROVAL', 'RECURRING_CHARGE_STATUS_AWAITING_APPROVAL'),
					('Declined', 'Declined', 'DECLINED', 'RECURRING_CHARGE_STATUS_DECLINED'),
					('Cancelled', 'Cancelled', 'CANCELLED', 'RECURRING_CHARGE_STATUS_CANCELLED'),
					('Active', 'Active', 'ACTIVE', 'RECURRING_CHARGE_STATUS_ACTIVE'),
					('Completed', 'Completed', 'COMPLETED', 'RECURRING_CHARGE_STATUS_COMPLETED');";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 2 - Populate recurring_charge_status table. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No Rollback neccessary as table will be dropped


		//	3:	Create charge_recurring_charge table
		$strSQL = "	CREATE TABLE charge_recurring_charge
					(
						id					INTEGER UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						charge_id			BIGINT UNSIGNED		NOT NULL					COMMENT '(FK) Charge. (has uniqueness constraint)',
						recurring_charge_id	BIGINT UNSIGNED		NOT NULL					COMMENT '(FK) Recurring Charge',
					
						CONSTRAINT pk_charge_recurring_charge_id					PRIMARY KEY	(id),
						CONSTRAINT un_charge_recurring_charge_charge_id				UNIQUE KEY	(charge_id),
						CONSTRAINT fk_charge_recurring_charge_charge_id				FOREIGN KEY	(charge_id)				REFERENCES Charge(Id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_charge_recurring_charge_recurring_charge_id	FOREIGN KEY	(recurring_charge_id)	REFERENCES RecurringCharge(Id) ON UPDATE CASCADE ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 3 - Create charge_recurring_charge table. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS charge_recurring_charge;";


		//	4:	Populate charge_recurring_charge table
		$strSQL = "	INSERT INTO charge_recurring_charge (charge_id, recurring_charge_id)
					SELECT Charge.Id, RecurringCharge.Id
					FROM Charge INNER JOIN RecurringCharge ON Charge.LinkType IN (501, 502) AND Charge.LinkId = RecurringCharge.Id
					ORDER BY Charge.Id ASC;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 4 - Populate charge_recurring_charge table. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No Rollback neccessary as table will be dropped


		//	5:	Set RecurringCharge.ApprovedBy to the System Employee Id for all records that are currently set to NULL
		$intSystemEmployeeId = Employee::SYSTEM_EMPLOYEE_ID;
		$strSQL = "	UPDATE RecurringCharge
					SET ApprovedBy = $intSystemEmployeeId
					WHERE ApprovedBy IS NULL;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 5 - Set RecurringCharge.ApprovedBy to the System Employee Id for all records that are currently set to NULL. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No Rollback neccessary as this was always supposed to be the case


		//	6:	Add the recurring_charge_status_id column to the RecurringCharge table and set it appropriately
		//	6.1:	Add the recurring_charge_status_id column, but don't make it manditory yet
		$strSQL = "	ALTER TABLE RecurringCharge
					ADD recurring_charge_status_id INTEGER UNSIGNED NULL COMMENT '(FK) Recurring Charge Status' AFTER Archived,
					ADD CONSTRAINT fk_recurring_charge_recurring_charge_status_id FOREIGN KEY (recurring_charge_status_id) REFERENCES recurring_charge_status(id) ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 6.1 - Add the recurring_charge_status_id column, but don't make it manditory yet. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE RecurringCharge 
									DROP FOREIGN KEY fk_recurring_charge_recurring_charge_status_id,
									DROP COLUMN recurring_charge_status_id;";

		//	6.2:	Set RecurringCharge.recurring_charge_status_id to AWAITING APPROVAL where appropriate
		//			Eligible records must satisfy: Not Archived and not yet ApprovedBy anyone
		$strSQL = "	UPDATE RecurringCharge
					SET recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'AWAITING_APPROVAL')
					WHERE recurring_charge_status_id IS NULL AND Archived = 0 AND ApprovedBy IS NULL;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 6.2 - Set RecurringCharge.recurring_charge_status_id to AWAITING APPROVAL where appropriate. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is necessary as RecurringCharge.recurring_charge_status_id column will be removed

		//	6.3:	Set RecurringCharge.recurring_charge_status_id to CANCELLED where appropriate
		//			Eligible records must satisfy: Is Archived AND TotalCharged < MinCharge. The -0.5 is to accomodate any rounding issues
		$strSQL = "	UPDATE RecurringCharge
					SET recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'CANCELLED')
					WHERE recurring_charge_status_id IS NULL AND Archived = 1 AND TotalCharged < (MinCharge - 0.5);";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 6.3 - Set RecurringCharge.recurring_charge_status_id to CANCELLED where appropriate. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is necessary as RecurringCharge.recurring_charge_status_id column will be removed

		//	6.4:	Set RecurringCharge.recurring_charge_status_id to COMPLETED where appropriate
		//			Eligible records must satisfy: TotalCharged >= MinCharge AND (IS NOT Continuable OR (IS Continuable AND IS Archived)).  The -0.5 is to accomodate any rounding issues
		$strSQL = "	UPDATE RecurringCharge
					SET recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'COMPLETED')
					WHERE recurring_charge_status_id IS NULL AND TotalCharged >= (MinCharge - 0.5) AND (Continuable = 0 OR (Continuable = 1 AND Archived = 1));";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 6.4 - Set RecurringCharge.recurring_charge_status_id to COMPLETED where appropriate. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is necessary as RecurringCharge.recurring_charge_status_id column will be removed

		//	6.5:	Set RecurringCharge.recurring_charge_status_id to ACTIVE where appropriate
		//			Eligible records must satisfy: IS NOT Archived AND NOT currently AwaitingApproval or Completed
		$strSQL = "	UPDATE RecurringCharge
					SET recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'ACTIVE')
					WHERE recurring_charge_status_id IS NULL AND Archived = 0 
					AND NOT (TotalCharged >= (MinCharge - 0.5) AND (Continuable = 0 OR (Continuable = 1 AND Archived = 1)))
					AND NOT (Archived = 0 AND ApprovedBy IS NULL);";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 6.5 - Set RecurringCharge.recurring_charge_status_id to ACTIVE where appropriate. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is necessary as RecurringCharge.recurring_charge_status_id column will be removed


		//	6.6:	Make recurring_charge_status_id column manditory 
		$strSQL = "ALTER TABLE RecurringCharge CHANGE recurring_charge_status_id recurring_charge_status_id INTEGER UNSIGNED NOT NULL COMMENT '(FK) Recurring Charge Status';";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 6.6 - Make recurring_charge_status_id column manditory. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is necessary as RecurringCharge.recurring_charge_status_id column will be removed

		//	7:	Add is_auto_approved column to RecurringChargeType table (defaults to 1 (true))
		$strSQL = "	ALTER TABLE RecurringChargeType
					ADD approval_required TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1=RecCharges require approval; 0=don''t require approval' AFTER UniqueCharge;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 7 - Add is_auto_approved column to RecurringChargeType table. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE RecurringChargeType DROP approval_required;";

	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>