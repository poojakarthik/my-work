<?php

/**
 * Version 25 of database update.
 * This version: -
 *	1:	creates the ticketing_status_type table
 *	2:	populates the ticketing_status_type table
 *	3:	adds the Foreign Key field "status_type_id" to the ticketing_status table
 *	4:	updates all the ticketing_status records so that an appropriate value is set for the status_type_id field
 *	5:	adds the "TICKETING_STATUS_WITH_INTERNAL" record to the ticketing_status table
 *	6:	fixes a spelling mistake in the ticketing_correspondance_delivery_status table
 */

class Flex_Rollout_Version_000025 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Create the ticketing_status_type table
		$strSQL = " CREATE TABLE `ticketing_status_type` (
					`id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
					`name` VARCHAR( 255 ) NOT NULL COMMENT 'Name of the status type',
					`description` VARCHAR( 255 ) NOT NULL COMMENT 'Description of the status type',
					`const_name` VARCHAR( 255 ) NOT NULL COMMENT 'the constant name',
					PRIMARY KEY ( `id` )
					) ENGINE = InnoDB COMMENT = 'Types of ticket statuses' ";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create the ticketing_status_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS ticketing_status_type;";

		// 2:	Populate the ticketing_status_type table
		$strSQL = " INSERT INTO ticketing_status_type
					VALUES
					(1, 'Pending', 'Pending Opening', 'TICKETING_STATUS_TYPE_PENDING'),
					(2, 'Open', 'Open', 'TICKETING_STATUS_TYPE_OPEN'),
					(3, 'Closed', 'Closed', 'TICKETING_STATUS_TYPE_CLOSED');
					";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add records to the ticketing_status_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ticketing_status_type;";
		
		// 3:	Add the Foreign Key field "status_type_id" to the ticketing_status table
		$strSQL = "ALTER TABLE ticketing_status ADD status_type_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into ticketing_status_type table' AFTER const_name";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add status_type_id field to the ticketing_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE ticketing_status DROP status_type_id";
		
		// 4:	Updates all the ticketing_status records so that an appropriate value is set for the status_type_id field
		$arrUpdateData = array();
		$arrUpdateData[] = array("SetClause" => "status_type_id = 1", "WhereClause" => "const_name IN (\"TICKETING_STATUS_UNASSIGNED\", \"TICKETING_STATUS_ASSIGNED\")");
		$arrUpdateData[] = array("SetClause" => "status_type_id = 2", "WhereClause" => "const_name IN (\"TICKETING_STATUS_WITH_CUSTOMER\", \"TICKETING_STATUS_WITH_CARRIER\", \"TICKETING_STATUS_IN_PROGRESS\")");
		$arrUpdateData[] = array("SetClause" => "status_type_id = 3", "WhereClause" => "const_name IN (\"TICKETING_STATUS_COMPLETED\", \"TICKETING_STATUS_DELETED\")");
		foreach ($arrUpdateData as $arrClauses)
		{
			$strSQL = "	UPDATE ticketing_status
						SET {$arrClauses['SetClause']}
						WHERE {$arrClauses['WhereClause']};
						";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception_Database(__CLASS__ . ' Failed to update the status_type_id field for the records currently in the ticketing_status table. ' . $qryQuery->Error());
			}
		}
		$this->rollbackSQL[] = "UPDATE ticketing_status SET status_type_id = 0";
		
		// 5:	Add the "TICKETING_STATUS_WITH_INTERNAL" record to the ticketing_status table
		$strSQL = " INSERT INTO ticketing_status
					VALUES (8, \"With Internal\", \"Awaiting response from internal source\", \"TICKETING_STATUS_WITH_INTERNAL\", 2);
					";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add TICKETING_STATUS_WITH_CUSTOMER record to the ticketing_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ticketing_status WHERE id = 8 AND const_name = \"TICKETING_STATUS_WITH_INTERNAL\"";
		
		// 6:	Fix spelling mistake in the ticketing_correspondance_delivery_status table
		$strSQL = "UPDATE ticketing_correspondance_delivery_status SET description = 'Received' WHERE description = 'Recieved';";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to fix spelling mistake in ticketing_correspondance_delivery_status.description field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "UPDATE ticketing_correspondance_delivery_status SET description = 'Recieved' WHERE description = 'Received';";
		
		
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
