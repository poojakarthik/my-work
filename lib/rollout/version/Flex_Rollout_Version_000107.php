<?php

/**
 * Version 107 of database update.
 * This version: -
 *	1:	Alters the contents of the ticketing_category table
 *	2:	Alters the ticketing_category table
 */

class Flex_Rollout_Version_000107 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Update the existing data to reflect the actual css style name
		$strSQL = 'UPDATE ticketing_category SET const_name = replace(lower(const_name), "_", "-");'; 

		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to manipulate ticketing_category data. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = 'UPDATE ticketing_category SET const_name = replace(upper(const_name), "-", "_");';
		
		// 2: Alter the table (rename the column)
		$strSQL = " ALTER TABLE ticketing_category CHANGE const_name css_name VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The css class name' ";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed alter ticketing_category table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = " ALTER TABLE ticketing_category CHANGE css_name const_name VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'The constant name' ";
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

