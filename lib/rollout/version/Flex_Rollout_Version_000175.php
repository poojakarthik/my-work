<?php

/**
 * Version 175 of database update.
 * This version: -
 *	
 *	1:	Set the FirstName, LastName and UserName of the system Employee record
 *	2:	Set the first_name, last_name and username of the system dealer record
 *
 */

class Flex_Rollout_Version_000175 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		$intSystemUserEmployeeId	= Employee::SYSTEM_EMPLOYEE_ID;
		$intSystemUserDealerId		= Dealer::SYSTEM_DEALER_ID;
		
		//	1:	Set the FirstName, LastName and UserName of the system Employee
		$strSQL = "UPDATE Employee SET FirstName = 'System', LastName = 'User', UserName = 'System' WHERE Id = {$intSystemUserEmployeeId};";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update the FirstName, LastName and UserName of the system employee. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No Explicit Rollback is necessary
		
		//	2:	Set the first_name, last_name and username of the system dealer record
		$strSQL = "UPDATE dealer SET first_name = 'System', last_name = 'User', username = 'System' WHERE id = {$intSystemUserDealerId};";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update the first_name, last_name and username of the system dealer. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No Explicit Rollback is necessary
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