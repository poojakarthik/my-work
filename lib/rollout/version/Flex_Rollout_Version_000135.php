<?php

/**
 * Version 135 of database update.
 * This version: -
 *	1:	Make the document_content.content Field NULLable
 */

class Flex_Rollout_Version_000135 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the RatePlan.brochure_document_id and auth_script_document_id Fields
		$strSQL =	"ALTER TABLE document_content " .
					"MODIFY content MEDIUMBLOB NULL COMMENT 'Binary content of the Document';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to make the document_content.content Field NULLable. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE document_content " .
								"MODIFY content MEDIUMBLOB NOT NULL COMMENT 'Binary content of the Document';";
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