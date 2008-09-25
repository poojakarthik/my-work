<?php

/**
 * Version 59 (Fifty Nine) of database update.
 * This version: -
 *	1:	changes field names and adds fulltext for match function
 */

class Flex_Rollout_Version_000059 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "DROP TABLE `customer_faq`;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to run query1. ' . $result->getMessage());
		}
		$strSQL = "CREATE TABLE `flex_dev`.`customer_faq` (
		`id` MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT 'Field to record faq ids',
		`title` VARCHAR( 255 ) NULL COMMENT 'field for faq title',
		`contents` TEXT NULL COMMENT 'field for faq contents',
		`time_added` VARCHAR( 20 ) NULL COMMENT 'time faq was added',
		`time_updated` VARCHAR( 20 ) NULL COMMENT 'time faq was updated',
		`download` BLOB NULL COMMENT 'faq related download',
		`customer_group_id` VARCHAR( 10 ) NULL COMMENT 'customer group for faq',
		`hits` MEDIUMINT( 11 ) NULL COMMENT 'hits or amount of times clicked',
		PRIMARY KEY ( `id` )
		) ENGINE = MyISAM COMMENT = 'Table for customer faqs';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to run query2. ' . $result->getMessage());
		}
		$strSQL = "ALTER TABLE customer_faq ADD FULLTEXT(title, contents);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to run query3. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "CREATE TABLE `flex_dev`.`customer_faq` (
		`id` MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT 'Field to record faq ids',
		`title` VARCHAR( 255 ) NULL COMMENT 'field for faq title',
		`contents` TEXT NULL COMMENT 'field for faq contents',
		`time_added` VARCHAR( 20 ) NULL COMMENT 'time faq was added',
		`time_updated` VARCHAR( 20 ) NULL COMMENT 'time faq was updated',
		`download` BLOB NULL COMMENT 'faq related download',
		`customer_group_id` VARCHAR( 10 ) NULL COMMENT 'customer group for faq',
		`hits` MEDIUMINT( 11 ) NULL COMMENT 'hits or amount of times clicked',
		PRIMARY KEY ( `id` )
		) ENGINE = MyISAM COMMENT = 'Table for customer faqs';";
		$this->rollbackSQL[] = "ALTER TABLE customer_faq ADD FULLTEXT(title, contents);";
		
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
