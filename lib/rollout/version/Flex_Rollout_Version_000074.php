<?php

/**
 * Version 74 (Seventy Four) of database update.
 * This version: -
 *	1:	alerts `survey_question_option` table.
 */

class Flex_Rollout_Version_000074 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	alter customer_faq
		$strSQL = "ALTER TABLE `survey_question_option` DROP `survey_id`;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to ALTER table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE `survey_question_option` ADD `survey_id` MEDIUMINT( 11 ) NOT NULL;";

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
