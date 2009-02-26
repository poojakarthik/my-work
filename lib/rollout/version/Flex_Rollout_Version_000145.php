<?php

/**
 * Version 145 of database update.
 * This version: -
 *	1:	Add a const_name column to the ticketing_category table
 *	2:	Auto-populate this field for the existing records of the table
 */

class Flex_Rollout_Version_000145 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add a const_name column to the ticketing_category table
		$strSQL = "ALTER TABLE ticketing_category ADD const_name VARCHAR(255) NOT NULL DEFAULT 'hello' COMMENT 'constant for this ticketing category' AFTER description";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed add const_name field to ticketing_category table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = " ALTER TABLE ticketing_category DROP const_name";
		
		// 2: Auto-populate this field for the existing records of the table
		$strSQL = "UPDATE ticketing_category SET const_name = upper(replace(css_name, '-', '_'))"; 

		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update the ticketing_category.const_name values to reflect the css_name field. ' . $result->getMessage());
		}
		// No rollback is necessary, as the column will be removed
		
		// Now that all records have had their const_name properly defined, alter the column so that there is no default value
		$strSQL = "ALTER TABLE ticketing_category CHANGE const_name const_name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'constant for this ticketing category'";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed alter ticketing_category.const_name field, to remove the default value. ' . $result->getMessage());
		}
		// No rollback is necessary
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

