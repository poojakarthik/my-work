<?php

/**
 * Version 95 of database update.
 * This version: -
 *	1:	Adds the FLEX_MODULE_KNOWLEDGE_BASE module into the flex_module table (defaults to being turned off)
 *	2:	Updates Employee privileges if they have the SuperAdmin privilege, because The SuperAdmin Privilege has been changed from 0x3FF to 0x7FFFFFFF
 */

class Flex_Rollout_Version_000095 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the FLEX_MODULE_KNOWLEDGE_BASE module into the flex_module table (defaults to being turned off)
		$strSQL = "	INSERT INTO flex_module (id, name, description, const_name, active)
					VALUES
					(4, 'Knowledge Base', 'Knowledge Base Module', 'FLEX_MODULE_KNOWLEDGE_BASE', 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to insert the FLEX_MODULE_KNOWLEDGE_BASE record into the flex_module table (id: 4). ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM flex_module WHERE const_name = 'FLEX_MODULE_KNOWLEDGE_BASE';";
		
		// 1:	Update Employee privileges if they have the SuperAdmin privilege, because The SuperAdmin Privilege has been changed from 0x3FF to 0x7FFFFFFF
		// First record the current Previleges of all Super Admins, as it is not all encompassing
		$strCurrentSuperAdminPerm	= '0x3FF';
		$strNewSuperAdminPerm		= '0x7FFFFFFF';
		$strQuery = "	SELECT Id, Privileges
						FROM Employee
						WHERE Privileges & $strCurrentSuperAdminPerm = $strCurrentSuperAdminPerm;";
		
		$result = $dbAdmin->queryAll($strQuery, NULL, MDB2_FETCHMODE_ASSOC);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve SuperAdmin users. ' . $result->getMessage());
		}
		
		$arrSuperAdmins = array();
		foreach ($result as $arrRecord)
		{
			$arrSuperAdmins[] = $arrRecord;
		}
		
		$strSQL = "	UPDATE Employee
					SET Privileges = Privileges | $strNewSuperAdminPerm
					WHERE Privileges & $strCurrentSuperAdminPerm = $strCurrentSuperAdminPerm;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update SuperAdmin employees so that they have the new Super Admin permission. ' . $result->getMessage());
		}
		
		foreach ($arrSuperAdmins as $arrSuperAdmin)
		{
			$this->rollbackSQL[] = "UPDATE Employee
									SET Privileges = {$arrSuperAdmin['Privileges']}
									WHERE Id = {$arrSuperAdmin['Id']};";
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
