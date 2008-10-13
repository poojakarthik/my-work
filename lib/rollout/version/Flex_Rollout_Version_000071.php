<?php

/**
 * Version 71 (Seventy One) of database update.
 * This version: -
 *	1:	Updates for Customer faq
 *	2:	Create survey tables
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


		$strSQL = "CREATE TABLE IF NOT EXISTS `survey` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'id field for all surveys',
		  `creation_date` timestamp NULL default NULL COMMENT 'date survey was created',
		  `start_date` timestamp NULL default NULL COMMENT 'date survey starts displaying',
		  `end_date` timestamp NULL default NULL COMMENT 'date survey ends',
		  `created_by` mediumint(9) default NULL COMMENT 'person who created the survey',
		  `title` varchar(255) default NULL COMMENT 'title or description of survey',
		  `conditions` longtext COMMENT 'survey conditions',
		  `customer_group` smallint(6) default NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey table';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey;";


		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_completed` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'id field',
		  `date_completed` timestamp NULL default NULL COMMENT 'date completed',
		  `account_id` bigint(20) default NULL COMMENT 'account id',
		  `survey_id` mediumint(11) default NULL COMMENT 'survey id',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='surveys completed';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_completed`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_completed;";

		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_option_response_type` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'id',
		  `name` varchar(255) default NULL COMMENT 'name',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey questions response type';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_option_response_type`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_option_response_type;";

		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_question` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'question Id',
		  `survey_id` mediumint(11) default NULL COMMENT 'this field tells us which survey the question belongs to',
		  `question` varchar(32767) default NULL COMMENT 'the question',
		  `response_required` tinyint(1) default NULL COMMENT 'response required',
		  `response_type` varchar(255) default NULL COMMENT 'response type',
		  `question_num` mediumint(11) default NULL COMMENT 'question number, e.g. 10',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey questions';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_question`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_question;";

		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_questions_response_type` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'id',
		  `name` varchar(255) default NULL COMMENT 'name',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey questions response type';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_questions_response_type`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_questions_response_type;";

		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_question_option` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'id',
		  `survey_id` mediumint(11) default NULL COMMENT 'survey id',
		  `question_id` mediumint(11) default NULL COMMENT 'question id',
		  `option_name` varchar(255) default NULL COMMENT 'option name',
		  `option_type` smallint(255) default NULL COMMENT 'option type',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey questions options';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_question_option`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_question_option;";

		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_response` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'Id',
		  `survey_id` mediumint(11) default NULL COMMENT 'survey id',
		  `question_id` mediumint(11) default NULL COMMENT 'question id',
		  `response_text` longtext COMMENT 'response text',
		  `account_id` bigint(20) default NULL,
		  `response_id` mediumint(11) default NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey response';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_response`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_response;";

		$strSQL = "CREATE TABLE IF NOT EXISTS `survey_response_options` (
		  `id` mediumint(11) NOT NULL auto_increment COMMENT 'id',
		  `response_id` mediumint(11) default NULL COMMENT 'response id',
		  `option_id` mediumint(11) default NULL COMMENT 'option id',
		  `option_text` longtext COMMENT 'option text',
		  `account_id` bigint(20) default NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB COMMENT='survey response options';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create tables. `survey_response_options`' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS survey_response_options;";
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
