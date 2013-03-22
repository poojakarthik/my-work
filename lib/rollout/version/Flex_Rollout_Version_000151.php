<?php

/**
 * Version 151 of database update.
 * This version: -
 *
 *	1:	Alters CustomerGroup.ExternalName to CustomerGroup.external_name
 *  2:  Alters CustomerGroup.InternalName to CustomerGroup.internal_name
 *
 */

class Flex_Rollout_Version_000151 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the RatePlan.locked and cdr_required Fields
		$strSQL =	"ALTER TABLE CustomerGroup " .
					"CHANGE ExternalName	external_name	VARCHAR(255)	NOT NULL	COMMENT 'Name of the Customer Group as the customers know it to be', " .
                    "CHANGE InternalName	internal_name	VARCHAR(255)	NOT NULL	COMMENT 'Name of the Customer Group, as used within the Telco', ".
					"CHANGE OutboundEmail	outbound_email	VARCHAR(255)	NOT NULL	COMMENT 'Email Address for outgoing email correspondance from this CustomerGroup';";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename the ExternalName and InternalName Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE CustomerGroup " .
					"CHANGE external_name	ExternalName	VARCHAR(255)	NOT NULL	COMMENT 'Name of the Customer Group as the customers know it to be', " .
                    "CHANGE internal_name	InternalName	VARCHAR(255)	NOT NULL	COMMENT 'Name of the Customer Group, as used within the Telco', ".
					"CHANGE outbound_email	OutboundEmail	VARCHAR(255)	NOT NULL	COMMENT 'Email Address for outgoing email correspondance from this CustomerGroup';";
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