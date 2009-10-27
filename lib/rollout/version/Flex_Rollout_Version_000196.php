<?php

/**
 * Version 196 of database update.
 * This version: -
 *	
 *	1:	Add indexes to the telemarketing_fnn_proposed Table
 *	2:	Add indexes to the telemarketing_fnn_dialled Table
 *
 */

class Flex_Rollout_Version_000196 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		$sDatetime	= date("Y-m-d H:i:s");
		
		//	1:	Add indexes to the telemarketing_fnn_proposed Table
		$this->outputMessage("Creating indexes for telemarketing_fnn_proposed @ ".date("Y-m-d H:i:s"));
		$strSQL = "	ALTER TABLE	telemarketing_fnn_proposed
					ADD	INDEX	in_telemarketing_fnn_proposed_fnn				(fnn),
					ADD	INDEX	in_telemarketing_fnn_proposed_call_period_start	(call_period_start),
					ADD	INDEX	in_telemarketing_fnn_proposed_call_period_end	(call_period_end);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add indexes to the telemarketing_fnn_proposed Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE		telemarketing_fnn_proposed
									DROP	INDEX	in_telemarketing_fnn_proposed_fnn,
									DROP	INDEX	in_telemarketing_fnn_proposed_call_period_start,
									DROP	INDEX	in_telemarketing_fnn_proposed_call_period_end;";
		
		//	2:	Add indexes to the telemarketing_fnn_dialled Table
		$this->outputMessage("Creating indexes for telemarketing_fnn_dialled @ ".date("Y-m-d H:i:s"));
		$strSQL = "	ALTER TABLE	telemarketing_fnn_dialled
					ADD	INDEX	in_telemarketing_fnn_dialled_fnn		(fnn),
					ADD	INDEX	in_telemarketing_fnn_dialled_dialled_on	(dialled_on);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add indexes to the telemarketing_fnn_dialled Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE		telemarketing_fnn_dialled
									DROP	INDEX	in_telemarketing_fnn_dialled_fnn,
									DROP	INDEX	in_telemarketing_fnn_dialled_dialled_on;";
		
		$this->outputMessage("Completed indexes for telemarketing_fnn_* @ ".date("Y-m-d H:i:s"));
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