<?php

/**
 * Version 175 of database update.
 * This version: -
 *	
 *	1:	Give the System Employee a username, 'System'
 *
 */

class Flex_Rollout_Version_000175 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		// Assumed System Employee Id (this should be available in rollouts)
		$intSytemUserEmployeeId = Employee::SYSTEM_EMPLOYEE_ID;
		
		// 1: Give the System Employee a username, 'System'
		$strSQL = "UPDATE Employee SET UserName = 'System' WHERE Id = {$intSytemUserEmployeeId};";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update the system user employee username. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"UPDATE Employee SET UserName = '' WHERE Id = {$intSytemUserEmployeeId};";
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