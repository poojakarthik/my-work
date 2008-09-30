<?php

/**
 * Version 71 (Seventy One) of database update.
 * This version: -
 *	1:	Updates for Customer faq
 *	2:	Updates for Survey
 */

class Flex_Rollout_Version_000071 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	alter customer_faq
		$strSQL = "ALTER TABLE `customer_faq` CHANGE `time_added` `time_added` TIMESTAMP NULL DEFAULT NULL COMMENT 'time faq was added',
		CHANGE `time_updated` `time_updated` TIMESTAMP NULL DEFAULT NULL COMMENT 'time faq was updated';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to ALTER table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE `customer_faq` CHANGE `time_added` `time_added` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT 'time faq was added',
		CHANGE `time_updated` `time_updated` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT 'time faq was updated';";


		// 2:	rename table
		$strSQL = "RENAME TABLE `survey_questions`  TO `survey_question`;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "RENAME TABLE `survey_question`  TO `survey_questions`;";


		// 3:	rename table
		$strSQL = "RENAME TABLE `survey_questions_options`  TO `survey_question_option`;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "RENAME TABLE `survey_question_option`  TO `survey_questions_options` ;";
		
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
