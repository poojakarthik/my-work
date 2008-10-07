<?php

/**
 * Version 76 of database update. for the survey system.
 * Main changes:
 * tables names, two main forms: survey_completed*, survey_question*
 * int fields now all bigint
 * fields that reference other tables are named so its easy to understand what they reference.
 * more detailed comments on both table and field areas.
 */

class Flex_Rollout_Version_000076 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		/* 
		 * 1. The updates below change INT fields all to BIGINT (20 not specified as its the default)
		 * 2. The updates below contain new comments.
		 */
		 
		$strSQL = "ALTER TABLE survey 
		CHANGE created_by created_by BIGINT NULL DEFAULT NULL COMMENT 'person who created the survey, this should represent the staff members User Id not the alpha Username' ,
		CHANGE customer_group customer_group BIGINT NULL DEFAULT NULL COMMENT 'This field represents the CustomerGroup that is allowed to view the survey.' ,
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'this field is used to store the unique id of each survey name';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey 
		CHANGE created_by created_by MEDIUMINT( 11 ) NULL DEFAULT NULL COMMENT '' ,
		CHANGE customer_group customer_group MEDIUMINT( 11 ) NULL DEFAULT NULL COMMENT '' ,
		CHANGE id id MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_completed
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'the id of each survey which is completed',
		CHANGE date_completed date_completed TIMESTAMP NULL DEFAULT NULL COMMENT 'the date which the user completed the survey.',
		CHANGE account_id account_id BIGINT NULL DEFAULT NULL COMMENT 'the contact id of the user who completed the survey',
		CHANGE survey_id survey_id BIGINT NULL DEFAULT NULL COMMENT 'the id of the survey which was completed';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_completed' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed
		CHANGE id id MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT '',
		CHANGE date_completed date_completed TIMESTAMP NULL DEFAULT NULL COMMENT '',
		CHANGE account_id account_id MEDIUMINT( 11 ) NULL DEFAULT NULL COMMENT '',
		CHANGE survey_id survey_id MEDIUMINT( 11 ) NULL DEFAULT NULL COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_option_response_type
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'the id of the question response type.',
		CHANGE name name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'the name of the question response type. e.g. text, select, checkbox';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_option_response_type' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_option_response_type
		CHANGE id id MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT '',
		CHANGE name name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_question
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'the id of the question',
		CHANGE survey_id survey_id BIGINT NULL DEFAULT NULL COMMENT 'this field tells us which survey the question belongs to',
		CHANGE question question VARCHAR( 32767 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'the question text, e.g. how old are you',
		CHANGE response_required response_required TINYINT( 1 ) NULL DEFAULT NULL COMMENT 'response required forces the user to respond to the question if set to 1',
		CHANGE response_type response_type VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'response type represents how the question is displayed, e.g. select, text, checkbox',
		CHANGE question_num question_num BIGINT NULL DEFAULT NULL COMMENT 'if there are 10 questions in a survey and this is the 3rd question then this would be set to 3.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question
		CHANGE id id MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT '',
		CHANGE survey_id survey_id MEDIUMINT( 11 ) NULL DEFAULT NULL COMMENT '',
		CHANGE question question VARCHAR( 32767 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '',
		CHANGE response_required response_required TINYINT( 1 ) NULL DEFAULT NULL COMMENT '',
		CHANGE response_type response_type VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '',
		CHANGE question_num question_num MEDIUMINT( 11 ) NULL DEFAULT NULL COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_questions_response_type 
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'this field sets a unique identifer for each response type.',
		CHANGE name name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this field contains options for displaying a question, e.g. select, checkbox, radial.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_questions_response_type' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_questions_response_type 
		CHANGE id id MEDIUMINT( 11 ) NOT NULL AUTO_INCREMENT COMMENT '',
		CHANGE name name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_question_option 
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'the id of the option',
		CHANGE question_id question_id BIGINT NULL DEFAULT NULL COMMENT 'the question id the option belongs to, multiple options can belong to one question, e.g. for a select box.',
		CHANGE option_name option_name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'the option name, the is displayed next to the field(checkbox) or within the field (select)',
		CHANGE option_type option_type BIGINT NULL DEFAULT NULL COMMENT 'the option type id represents if there is an additional field available in this question, e.g. a select box and a text box next to it.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question_option CHANGE id id MEDIUMINT( 11 );";


		
		$strSQL = "ALTER TABLE survey_response 
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'the id of the response submitted by the user',
		CHANGE survey_id survey_id BIGINT NULL DEFAULT NULL COMMENT 'the survey id which this response is related to',
		CHANGE question_id question_id BIGINT NULL DEFAULT NULL COMMENT 'the question id which this response is for.',
		CHANGE response_text response_text LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this field contains the actual response',
		CHANGE account_id account_id BIGINT NULL DEFAULT NULL COMMENT 'the id of the customers account, so we can group all responses by customer/survey id.',
		CHANGE response_id response_id BIGINT NULL DEFAULT NULL COMMENT 'this id represents the survey question option.id that was selected.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_response CHANGE id id MEDIUMINT( 11 );";


		
		$strSQL = "ALTER TABLE survey_response_options 
		CHANGE id id BIGINT NOT NULL AUTO_INCREMENT COMMENT 'the id of this respone',
		CHANGE response_id response_id BIGINT NULL DEFAULT NULL COMMENT 'this field is the id of the survey response',
		CHANGE option_id option_id BIGINT NULL DEFAULT NULL COMMENT 'this field is the question id',
		CHANGE option_text option_text LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this is the text/response from the user',
		CHANGE account_id account_id BIGINT NULL DEFAULT NULL COMMENT 'this is the customers account id';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_response_options' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_response_options CHANGE id id MEDIUMINT( 11 );";


		/* 
		 * 1. Alter comments on table names
		 */

		
		$strSQL = "ALTER TABLE survey  COMMENT = 'this table contains surveys title, start and end times and the conditions';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_completed  COMMENT = 'this table stores a record of completed surveys';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_completed' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_option_response_type  COMMENT = 'this table stores options for questions, e.g. select, radial';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_option_response_type' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_option_response_type COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_question  COMMENT = 'This table contains the questions for the survey';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_questions_response_type  COMMENT = 'This table contains the questions for the survey';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_questions_response_type' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_questions_response_type COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_question_option  COMMENT = 'This table contains a list of options for each question.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question_option COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_response  COMMENT = 'This table contains all the responses for the survey.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_response COMMENT = '';";


		
		$strSQL = "ALTER TABLE survey_response_options  COMMENT = 'This table contains responses to options in the survey.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_response_options' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_response_options COMMENT = '';";


		/* 
		 * 1. Rename table names
		 */
		
		$strSQL = "RENAME TABLE survey_response  TO survey_completed_response ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename table survey_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "RENAME TABLE survey_completed_response  TO survey_response ;";


		
		$strSQL = "RENAME TABLE survey_response_options  TO survey_completed_response_option ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename table survey_response_options' . $result->getMessage());
		}
		$this->rollbackSQL[] = "RENAME TABLE survey_completed_response_option  TO survey_response_options ;";


		
		$strSQL = "RENAME TABLE survey_questions_response_type  TO survey_question_response_type ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename table survey_questions_response_type' . $result->getMessage());
		}
		$this->rollbackSQL[] = "RENAME TABLE survey_question_response_type  TO survey_questions_response_type ;";


		
		$strSQL = "RENAME TABLE survey_option_response_type  TO survey_question_option_response_type ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename table survey_option_response_type' . $result->getMessage());
		}
		$this->rollbackSQL[] = "RENAME TABLE survey_question_option_response_type  TO survey_option_response_type ;";



		/* 
		 * 1. Rename fields 
		 */
		
		$strSQL = "ALTER TABLE survey_completed CHANGE account_id contact_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the contact id of the user who completed the survey';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename field survey_completed' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed CHANGE contact_id account_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the contact id of the user who completed the survey';";


		
		$strSQL = "ALTER TABLE survey CHANGE customer_group customer_group_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'This field represents the CustomerGroup that is allowed to view the survey.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename field survey' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey CHANGE customer_group_id customer_group BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'This field represents the CustomerGroup that is allowed to view the survey.';";


		
		$strSQL = "ALTER TABLE survey_completed_response 
		CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT COMMENT 'the id of the response submitted by the user',
		CHANGE survey_id survey_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the survey id which this response is related to',
		CHANGE question_id survey_question_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the question id which this response is for.',
		CHANGE response_text response_text LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this field contains the actual response',
		CHANGE account_id contact_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the id of the customers account, so we can group all responses by customer/survey id.',
		CHANGE response_id survey_question_option_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this id represents the survey question option.id that was selected.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename field(s) survey_completed_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response 
		CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT COMMENT 'the id of the response submitted by the user',
		CHANGE survey_id survey_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the survey id which this response is related to',
		CHANGE survey_question_id question_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the question id which this response is for.',
		CHANGE response_text response_text LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this field contains the actual response',
		CHANGE contact_id account_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the id of the customers account, so we can group all responses by customer/survey id.',
		CHANGE survey_question_option_id response_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this id represents the survey question option.id that was selected.';";


		
		$strSQL = "ALTER TABLE survey_completed_response_option 
		CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT COMMENT 'the id of this respone',
		CHANGE response_id survey_completed_response_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this field is the id of the survey response',
		CHANGE option_id survey_question_option_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this field is the question id',
		CHANGE option_text option_text LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this is the text/response from the user',
		CHANGE account_id contact_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this is the customers account id';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename field(s) survey_completed_response_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response_option 
		CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT COMMENT 'the id of this respone',
		CHANGE survey_completed_response_id response_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this field is the id of the survey response',
		CHANGE survey_question_option_id option_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this field is the question id',
		CHANGE option_text option_text LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'this is the text/response from the user',
		CHANGE contact_id account_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'this is the customers account id';";

		$strSQL = "ALTER TABLE survey_question 
		CHANGE response_type survey_question_response_type_id VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'response type represents how the question is displayed, e.g. select, text, checkbox';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question 
		CHANGE response_type survey_question_response_type_id VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_question_option CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT COMMENT 'the id of the option',
		CHANGE question_id survey_question_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the question id the option belongs to, multiple options can belong to one question, e.g. for a select box.',
		CHANGE option_name option_name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'the option name, the is displayed next to the field(checkbox) or within the field (select)',
		CHANGE option_type survey_question_response_type_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the option type id represents if there is an additional field available in this question, e.g. a select box and a text box next to it.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question_option CHANGE id id BIGINT( 20 ) NOT NULL AUTO_INCREMENT COMMENT '',
		CHANGE question_id survey_question_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT '',
		CHANGE option_name option_name VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '',
		CHANGE option_type survey_question_response_type_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT '';";


		
		$strSQL = "ALTER TABLE survey_completed_response ADD survey_completed_id BIGINT NULL COMMENT 'the id of the completed survey which this references';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add field survey_completed_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response DROP survey_completed_id;";


		
		$strSQL = "ALTER TABLE survey_completed_response DROP survey_id  ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop field survey_completed_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response ADD survey_id;";


		
		$strSQL = "ALTER TABLE survey_completed_response DROP contact_id  ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop field survey_completed_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response ADD contact_id";


		
		$strSQL = "ALTER TABLE survey_completed_response_option DROP contact_id ; ";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop field survey_completed_response_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response_option ADD contact_id";



		
		$strSQL = "ALTER TABLE survey_completed_response DROP survey_question_option_id  ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop field survey_completed_response' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response ADD survey_question_option_id";



		
		$strSQL = "ALTER TABLE survey_completed_response_option DROP survey_question_option_id  ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop field survey_completed_response_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response_option ADD survey_question_option_id";


		
		$strSQL = "ALTER TABLE survey_question_option CHANGE survey_question_response_type_id survey_question_option_response_type_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the option type id represents if there is an additional field available in this question, e.g. a select box and a text box next to it.';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey_question_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_question_option CHANGE survey_question_option_response_type_id survey_question_response_type_id BIGINT( 20 ) NULL DEFAULT NULL COMMENT 'the option type id represents if there is an additional field available in this question, e.g. a select box and a text box next to it.';";



		
		$strSQL = "ALTER TABLE survey_completed_response_option ADD survey_question_option_id BIGINT NULL AFTER id ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter field survey_completed_response_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response_option DROP survey_question_option_id;";


		
		$strSQL = "ALTER TABLE survey_completed_response_option DROP survey_completed_response_id;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter field survey_completed_response_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response_option ADD survey_completed_response_id;";


		
		$strSQL = "ALTER TABLE survey_completed_response_option ADD survey_completed_response_id BIGINT NULL AFTER option_text ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter field survey_completed_response_option' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE survey_completed_response_option DROP survey_completed_response_id;";

		
		$strSQL = "UPDATE survey_completed,Contact 
		SET survey_completed.contact_id = Contact.Id 
		WHERE survey_completed.contact_id = Contact.Account;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update field survey_completed.contact_id' . $result->getMessage());
		}
		$this->rollbackSQL[] = "UPDATE survey_completed,Contact 
		SET survey_completed.contact_id = Contact.Account 
		WHERE survey_completed.contact_id = Contact.Id;";

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
