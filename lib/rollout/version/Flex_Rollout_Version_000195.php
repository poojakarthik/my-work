<?php

/**
 * Version 195 of database update.
 * This version: -
 *	
 *	1:	Add the telemarketing_fnn_dialled_result_category Table
 *	2:	Populate the telemarketing_fnn_dialled_result_category Table
 *	3:	Add the telemarketing_fnn_dialled_result.telemarketing_fnn_dialled_result_category_id Field and make the id non-AUTO_INCREMENT
 *	4:	Populate the telemarketing_fnn_dialled_result Table
 *
 */

class Flex_Rollout_Version_000195 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the telemarketing_fnn_dialled_result_category Table
		$strSQL = "	CREATE TABLE	telemarketing_fnn_dialled_result_category
					(
						id				INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name',
						description		VARCHAR(1024)				NULL						COMMENT 'Description',
						system_name		VARCHAR(256)				NOT NULL					COMMENT 'System Name',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias',
						
						CONSTRAINT	pk_telemarketing_fnn_dialled_result_category_id				PRIMARY KEY (id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the telemarketing_fnn_dialled_result_category Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	telemarketing_fnn_dialled_result_category;";
		
		//	2:	Populate the telemarketing_fnn_dialled_result_category Table
		$strSQL = "	INSERT INTO	telemarketing_fnn_dialled_result_category
						(name		, description		, system_name	, const_name)
					VALUES
						('Not Available'	, 'Not Available'		, 'NOT_AVAILABLE'		, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_AVAILABLE'),
						('Call Back'		, 'Call Back'			, 'CALL_BACK'			, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_BACK'),
						('Not Interested'	, 'Not Interested'		, 'NOT_INTERESTED'		, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_INTERESTED'),
						('Automated Answer'	, 'Automated Answer'	, 'AUTOMATED_ANSWER'	, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_AUTOMATED_ANSWER'),
						('Sale Completed'	, 'Sale Completed'		, 'SALE_COMPLETED'		, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_SALE_COMPLETED'),
						('Not Qualified'	, 'Not Qualified'		, 'NOT_QUALIFIED'		, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED'),
						('Call Dropped'		, 'Call Dropped'		, 'CALL_DROPPED'		, 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_DROPPED');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the telemarketing_fnn_dialled_result_category Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		//	3:	Add the telemarketing_fnn_dialled_result.telemarketing_fnn_dialled_result_category_id Field and make the id non-AUTO_INCREMENT
		$strSQL = "	ALTER TABLE			telemarketing_fnn_dialled_result
					MODIFY	COLUMN		id																	BIGINT(20)	UNSIGNED	NOT NULL	COMMENT 'Unique Identifier',
					ADD		COLUMN		telemarketing_fnn_dialled_result_category_id						INTEGER		UNSIGNED	NOT NULL	COMMENT '(FK) Category for this Call Result',
					ADD		CONSTRAINT	fk_telemarketing_fnn_dialled_result_fnn_dialled_result_category_id	FOREIGN KEY	(telemarketing_fnn_dialled_result_category_id)	REFERENCES telemarketing_fnn_dialled_result_category(id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the telemarketing_fnn_dialled_result.telemarketing_fnn_dialled_result_category_id Field and make the id non-AUTO_INCREMENT. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE			telemarketing_fnn_dialled_result
									DROP	CONSTRAINT	fk_telemarketing_fnn_dialled_result_fnn_dialled_result_category_id,
									DROP	COLUMN		telemarketing_fnn_dialled_result_category_id,
									MODIFY	COLUMN		id																BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier';";
		
		//	4:	Populate the telemarketing_fnn_dialled_result Table
		$strSQL = "	INSERT INTO	telemarketing_fnn_dialled_result
						(id		, name				, description		, const_name										,	telemarketing_fnn_dialled_result_category_id)
					VALUES
						(101	, 'Line Busy'				, 'Line Busy'							, 'TELEMARKETING_FNN_DIALLED_RESULT_LINE_BUSY'					, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_AVAILABLE')),
						(102	, 'No Ring Tone'			, 'No Ring Tone'						, 'TELEMARKETING_FNN_DIALLED_RESULT_NO_RING_TONE'				, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_AVAILABLE')),
						(103	, 'No Answer'				, 'No Answer'							, 'TELEMARKETING_FNN_DIALLED_RESULT_NO_ANSWER'					, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_AVAILABLE')),
						(104	, 'Blank Call'				, 'Blank Call'							, 'TELEMARKETING_FNN_DIALLED_RESULT_BLANK_CALL'					, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_AVAILABLE')),
						
						(201	, 'Documentation Requested'	, 'Documentation Requested'				, 'TELEMARKETING_FNN_DIALLED_RESULT_DOCUMENTATION_REQUESTED'	, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_BACK')),
						(202	, 'Brochure Request'		, 'Brochure Request by Fax/Email'		, 'TELEMARKETING_FNN_DIALLED_RESULT_BROCHURE_REQUEST'			, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_BACK')),
						(203	, 'Follow Up'				, 'Blank Call'							, 'TELEMARKETING_FNN_DIALLED_RESULT_CALL_BACK'					, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_BACK')),
						
						(301	, 'Hung Up'					, 'Hung Up'								, 'TELEMARKETING_FNN_DIALLED_RESULT_HUNG_UP'					, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_INTERESTED')),
						(302	, 'Do Not Call'				, 'Do Not Call'							, 'TELEMARKETING_FNN_DIALLED_RESULT_DO_NOT_CALL'				, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_INTERESTED')),
						
						(401	, 'Answering Machine'		, 'Answering Machine'					, 'TELEMARKETING_FNN_DIALLED_RESULT_ANSWERING_MACHINE'			, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_AUTOMATED_ANSWER')),
						(402	, 'Fax Machine'				, 'Fax Machine'							, 'TELEMARKETING_FNN_DIALLED_RESULT_FAX_MACHINE'				, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_AUTOMATED_ANSWER')),
						
						(501	, 'Sales Done'				, 'Sales Done'							, 'TELEMARKETING_FNN_DIALLED_RESULT_SALES_DONE'					, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_SALE_COMPLETED')),
						
						(601	, 'ABN Mismatch'			, 'ABN Not Matching'					, 'TELEMARKETING_FNN_DIALLED_RESULT_ABN_MISMATCH'				, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED')),
						(602	, 'Bad Credit History'		, 'Bad Credit History'					, 'TELEMARKETING_FNN_DIALLED_RESULT_BAD_CREDIT_HISTORY'			, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED')),
						(603	, 'Suspicious Customer'		, 'Suspicious Customer'					, 'TELEMARKETING_FNN_DIALLED_RESULT_SUSPICIOUS_CUSTOMER'		, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED')),
						(604	, 'Unauthorised Person'		, 'Unauthorised Person'					, 'TELEMARKETING_FNN_DIALLED_RESULT_UNAUTHORISED_PERSON'		, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED')),
						(605	, 'Foreign Language'		, 'Foreign Language'					, 'TELEMARKETING_FNN_DIALLED_RESULT_FOREIGN_LANGUAGE'			, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED')),
						(606	, 'Wrong Number'			, 'Wrong Number'						, 'TELEMARKETING_FNN_DIALLED_RESULT_WRONG_NUMBER'				, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_NOT_QUALIFIED')),
						
						(701	, 'Static on Line'			, 'Static on the Line'					, 'TELEMARKETING_FNN_DIALLED_RESULT_STATIC_ON_LINE'				, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_DROPPED')),
						(702	, 'Unknown Termination'		, 'Call Terminated For Unknown Reasons'	, 'TELEMARKETING_FNN_DIALLED_RESULT_UNKNOWN_TERMINATION'		, (SELECT id FROM telemarketing_fnn_dialled_result_category WHERE const_name = 'TELEMARKETING_FNN_DIALLED_RESULT_CATEGORY_CALL_DROPPED'));";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the telemarketing_fnn_dialled_result Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	TRUNCATE TABLE	telemarketing_fnn_dialled_result;";
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