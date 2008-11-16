<?php

/**
 * Version 95 of database update.
 * This version: -
 *	1:	Adds the FLEX_MODULE_KNOWLEDGE_BASE module into the flex_module table (defaults to being turned off)
 *	2:	Updates Employee privileges if they have the SuperAdmin privilege, because The SuperAdmin Privilege has been changed from 0x3FF to 0x7FFFFFFF
 *	3:	Alter dealer.dealer_status_id so that it is manditory (For some reason I declared it in rollout script 92 as NULLable and defaults to NULL)
 *	4:	Builds a dealer record for each Employee who has the Sales privilege (0x08)
 *	5:	Updates Employee privileges if they have the old GOD mode value, because the GOD privilege has been changed from 0x7FFFFFFFFFFFFFFF to 0x7FFFFFFFFFFFFF to stop an overflow issue
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
		
		// 3: Alter dealer.dealer_status_id so that it is manditory (For some reason I declared it in rollout script 92 as NULLable and defaults to NULL)
		$strSQL = "	ALTER TABLE dealer
					CHANGE dealer_status_id dealer_status_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into the dealer_status table, defininng the current status of the dealer';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter dealer.dealer_status_id to set it to being manditory (not NULLable). ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE dealer
								CHANGE dealer_status_id dealer_status_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK into the dealer_status table, defininng the current status of the dealer';";
		
		// 4: Build a dealer record for each Employee who has the Sales privilege (0x08) and isn't already set up as a dealer
		// Only the "System" employee should already be set up as a dealer

		// First get the current highest id of dealers in the dealer table
		$intHighestId = $dbAdmin->queryOne('SELECT MAX(id) FROM dealer;', 'integer');
		
		if (PEAR::isError($intHighestId))
		{
			throw new Exception(__CLASS__ . ' Could not find the MAX(id) in dealer table'. $intHighestId->getMessage());
		}
		
		// Insert the records
		$strSQL = "	INSERT INTO dealer (username, password, can_verify, first_name, last_name, phone, mobile, email, dealer_status_id, created_on, employee_id)
					SELECT UserName, PassWord, 1, FirstName, LastName, Phone, Mobile, Email, 1, NOW(), Id
					FROM Employee
					WHERE (Archived = 0) AND (Privileges & 0x08 = 0x08) AND Id NOT IN (SELECT employee_id FROM dealer WHERE employee_id IS NOT NULL);";
		
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create dealer records for all employees that have the Sales privilege (0x08). ' . $result->getMessage());
		}
		$strWhereClause = ($intHighestId !== NULL)? "id > $intHighestId" : "TRUE";
		$this->rollbackSQL[] = "DELETE FROM dealer WHERE $strWhereClause;";
		
		// This will reset the auto_increment property back to what it was before we started to add the employee dealer records
		$this->rollbackSQL[] = "ALTER TABLE dealer AUTO_INCREMENT = 0;";
	
		// 5: Updates Employee privileges if they have the old GOD mode value, because the GOD privilege has been changed from 0x7FFFFFFFFFFFFFFF to 0x7FFFFFFFFFFFFF to stop an overflow issue
		$intNewGodPerm = 0x7FFFFFFFFFFFFF;
		$strSQL = "	UPDATE Employee
					SET Privileges = $intNewGodPerm
					WHERE Privileges > $intNewGodPerm;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update GOD employees so that they have the new GOD permission. ' . $result->getMessage());
		}
		// No Rollback is required

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
