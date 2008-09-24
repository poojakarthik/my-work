<?php

/**
 * Version 47 (Forty-Seven) of database update.
 * This version: -
 *	1:	Removes the existing flex_module table and recreates it
 *	2:	Populates it
 */

class Flex_Rollout_Version_000047 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Create the flex_module table
		if (!$qryQuery->Execute("DROP TABLE IF EXISTS flex_module;"))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the flex_module table (if exists). ' . $qryQuery->Error());
		}
		$strSQL = " CREATE TABLE flex_module
					(
						id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY COMMENT 'Unique id for the module',
						name VARCHAR(1024) NOT NULL COMMENT 'Unique name for the module',
						description VARCHAR(1024) NOT NULL COMMENT 'description',
						const_name VARCHAR(1024) NOT NULL COMMENT 'constant name',
						active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'boolean value (0 = module is turned off, 1 = module is turned on)'
					) ENGINE = innodb COMMENT = 'Defines flex modules and whether they are to be used by flex';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create the flex_module table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS flex_module";
		
		// 2:	Populate flex_module table
		$strSQL = "INSERT INTO flex_module (id, name, description, const_name, active)
					VALUES
					(1, 'Online Credit Card Payments', 'Online Credit Card Payments Module', 'FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS', 1),
					(2, 'Customer Status', 'Customer Status', 'FLEX_MODULE_CUSTOMER_STATUS', 1);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate flex_module table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM flex_module WHERE id IN(1,2)";
		
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
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
