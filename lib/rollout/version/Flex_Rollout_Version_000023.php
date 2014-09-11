<?php

/**
 * Version 23 (twenty-three) of database update.
 * This version: -
 *	1:	Alter the ticketing history table to allow null account ids
 *	2:	Alter the customer group table to allow null details in recently added columns
 */

class Flex_Rollout_Version_000023 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Alter the ticketing history table to allow null account ids
		$strSQL = "ALTER TABLE ticketing_ticket_history MODIFY account_id bigint(20) unsigned DEFAULT NULL COMMENT 'FK to the Account table'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to modify ticketing_ticket_history to make account_id nullable. ' . $qryQuery->Error());
		}

		// 2:	Alter the customer group table to allow null details in recently added columns
		$strSQL = "ALTER TABLE CustomerGroup MODIFY flex_url varchar(255) DEFAULT NULL COMMENT 'The base URL for the Flex web interface for this customer group',
											 MODIFY email_domain varchar(255) DEFAULT NULL COMMENT 'The domain part of email addresses sent to to this customer group' ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to modify CustomerGroup to make flex_url and email_domain nullable. ' . $qryQuery->Error());
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
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
