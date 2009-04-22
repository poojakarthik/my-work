<?php

/**
 * Version 171 of database update.
 * This version: -
 *	
 *	1:	Add the calendar_event Table
 *
 */

class Flex_Rollout_Version_000171 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the calendar_event Table
		$strSQL = "	CREATE TABLE	calendar_event
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						name					VARCHAR(256)				NOT NULL								COMMENT 'Name of the Event',
						description				VARCHAR(1024)				NOT NULL								COMMENT 'Description of the Event',
						department_responsible	VARCHAR(256)				NULL									COMMENT 'Department responsible for this Event',
						start_timestamp			TIMESTAMP					NOT NULL	DEFAULT NULL				COMMENT 'Event Start Timestamp',
						end_timestamp			TIMESTAMP					NULL									COMMENT 'Event End Timestamp',
						created_employee_id		BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Employee who created/scheduled the Event',
						created_on				TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT 'Creation Timestamp',
						status_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Status',
						
						CONSTRAINT	pk_calendar_event_id					PRIMARY KEY (id),
						CONSTRAINT	fk_calendar_event_created_employee_id	FOREIGN KEY	(created_employee_id)	REFERENCES Employee(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_calendar_event_status_id				FOREIGN KEY	(status_id)				REFERENCES status(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the calendar_event Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	calendar_event;";
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