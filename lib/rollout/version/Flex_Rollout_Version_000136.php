<?php

/**
 * Version 136 of database update.
 * This version: -
 *	1:	Make the document_content.file_type_id Field NULLable
 */

class Flex_Rollout_Version_000136 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Make the document_content.file_type_id Field NULLable
		$strSQL =	"ALTER TABLE document_content " .
					"MODIFY file_type_id BIGINT(20) UNSIGNED NULL COMMENT '(FK) The Document\'s File Type';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to make the document_content.file_type_id Field NULLable. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE document_content " .
								"MODIFY file_type_id  BIGINT(20) UNSIGNED NOT NULL COMMENT '(FK) The Document\'s File Type';";
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