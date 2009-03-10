<?php

/**
 * Version 155 of database update.
 * This version: -
 *
 *	1:	Add the carrier_translation_context Table
 *	2:	Populate the carrier_translation_context Table
 */

class Flex_Rollout_Version_000155 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the carrier_translation_context Table
		$strSQL =	"CREATE TABLE carrier_translation_context " .
					"(" .
					"	id				BIGINT(20)		UNSIGNED	NOT NULL					COMMENT 'Unique Identifier', " .
					"	name			VARCHAR(255)				NOT NULL					COMMENT 'Name of the Carrier Translation Context', " .
					"	description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Carrier Translation Context', " .
					"	const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Name for this Carrier Translation Context', " .
					"	" .
					"	CONSTRAINT	pk_carrier_translation_context_id	PRIMARY KEY (id)" .
					") ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the carrier_translation_context Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE carrier_translation_context;";

		// 2:	Populate the carrier_translation_context Table
		$strSQL =	"INSERT INTO carrier_translation_context (id, name, description, const_name) VALUES " .
					"(100	, 'EPID'						, 'Eligible Party ID'			, 'CARRIER_TRANSLATION_CONTEXT_EPID'), " .
					"(101	, 'ACIF Reject'					, 'ACIF Reject Codes'			, 'CARRIER_TRANSLATION_CONTEXT_REJECT'), " .
					"(102	, 'Optus PPR Loss'				, 'Optus PPR Loss Codes'		, 'CARRIER_TRANSLATION_CONTEXT_PPR_LOSS'), " .
					"(103	, 'Unitel Reject'				, 'Unitel Reject Codes'			, 'CARRIER_TRANSLATION_CONTEXT_UNITEL_REJECT'), " .
					"(104	, 'Unitel Preselection Status'	, 'Unitel Preselection Status'	, 'CARRIER_TRANSLATION_CONTEXT_UNITEL_PRESELECTION_STATUS')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the carrier_translation_context Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
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