<?php

/**
 * Version 132 of database update.
 * This version: -
 *	1:	Add the flex_config Table
 */

class Flex_Rollout_Version_000132 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the flex_config Table
		$strSQL =	"CREATE TABLE IF NOT EXISTS flex_config 
					(
						id							BIGINT(20)	UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						created_by					BIGINT(20)	UNSIGNED	NOT NULL				COMMENT '(FK) Employee who created this version of the Flex Config',
						created_on					DATETIME				NOT NULL				COMMENT 'When this version of the Flex Config was created',
						internal_contact_list_html	VARCHAR(32767)			NULL					COMMENT 'Internal Contact List in HTML form',
						
						CONSTRAINT pk_flex_config_id PRIMARY KEY (id),
						CONSTRAINT fk_flex_config_created_by FOREIGN KEY (created_by) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add flex_config Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE flex_config;";
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