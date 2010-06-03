<?php

/**
 * Version 218 of database update.
 * This version: -
 *
 *	1:	Add 'followup_type' table
 *	2:	Add 'followup_recurrence_period' table
 *	3:	Add 'followup_closure_type' table
 *	4:	Add 'followup_closure' table
 *	5:	Add 'followup_category' table
 *	6:	Add 'followup' table
 *	7:	Add 'followup_recurring' table
 *	8:	Add 'followup_history' table
 *	9:	Add 'followup_recurring_history' table
 *	10:	Add 'followup_note' table
 *	11:	Add 'followup_action' table
 *	12:	Add 'followup_ticketing_correspondence' table
 *	13: Add 'followup_recurring_note' table
 *	14:	Add 'followup_recurring_action' table
 *	15:	Add 'followup_recurring_ticketing_correspondence' table
 *	16:	Insert records into 'followup_type'
 *	17:	Insert records into 'followup_recurrence_period'
 *	18:	Insert records into 'followup_closure_type'
 */

class Flex_Rollout_Version_000218 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add 'followup_type' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_type
																(
																	id				INT				UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	name			VARCHAR(128)				NOT NULL					COMMENT	'Name of the FollowUp Type',
																	description		VARCHAR(256)				NOT NULL					COMMENT	'Description of the FollowUp Type',
																	const_name		VARCHAR(256)				NOT NULL					COMMENT	'Constant Alias of the FollowUp Type',
																	system_name		VARCHAR(128)				NOT NULL					COMMENT	'System Name of the FollowUp Type',
																	CONSTRAINT	pk_followup_type_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_recurrence_period' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_recurrence_period
																(
																	id				INT				UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	name			VARCHAR(128)				NOT NULL					COMMENT	'Name of the FollowUp Recurrence Period',
																	description		VARCHAR(256)				NOT NULL					COMMENT	'Description of the FollowUp Recurrence Period',
																	const_name		VARCHAR(256)				NOT NULL					COMMENT	'Constant Alias of the FollowUp Recurrence Period',
																	system_name		VARCHAR(128)				NOT NULL					COMMENT	'System Name of the FollowUp Recurrence Period',
																	CONSTRAINT	pk_followup_recurrence_period_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_recurrence_period;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_closure_type' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_closure_type
																(
																	id				INT				UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	name			VARCHAR(128)				NOT NULL					COMMENT	'Name of the FollowUp Closure Type',
																	description		VARCHAR(256)				NOT NULL					COMMENT	'Description of the FollowUp Closure Type',
																	const_name		VARCHAR(256)				NOT NULL					COMMENT	'Constant Alias of the FollowUp Closure Type',
																	system_name		VARCHAR(128)				NOT NULL					COMMENT	'System Name of the FollowUp Closure Type',
																	CONSTRAINT	pk_followup_closure_type_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_closure_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_closure' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_closure
																(
																	id							INT				UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_closure_type_id	INT				UNSIGNED	NOT NULL					COMMENT	'(fk) followup_closure_type - the base type for this closure',
																	name						VARCHAR(128)				NOT NULL					COMMENT	'Name of the FollowUp Closure',
																	description					VARCHAR(256)				NOT NULL					COMMENT	'Reason for the closure of a FollowUp',
																	CONSTRAINT	pk_followup_closure_id							PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_closure_followup_closure_type_id	FOREIGN KEY	(followup_closure_type_id)	REFERENCES	followup_closure_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_closure;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_category' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_category
																(
																	id				INT				UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	name			VARCHAR(128)				NOT NULL					COMMENT	'Name of the FollowUp Category',
																	description		VARCHAR(256)				NOT NULL					COMMENT	'Description of the FollowUp Category',
																	CONSTRAINT	pk_followup_category_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_category;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup
																(
																	id						INT 		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	assigned_employee_id	BIGINT		UNSIGNED	NOT NULL					COMMENT '(fk) Employee - who is assigned the FollowUp',
																	created_datetime		DATETIME				NOT NULL					COMMENT 'Time that the FollowUp is created',
																	due_datetime			DATETIME				NOT NULL					COMMENT 'DateTime that the FollowUp is due',
																	followup_type_id		INT			UNSIGNED	NOT NULL					COMMENT '(fk) followup_type',
																	followup_category_id	INT			UNSIGNED	NOT NULL					COMMENT '(fk) followup_category',
																	followup_closure_id		INT			UNSIGNED	NULL						COMMENT '(fk) followup_closure - reason for closing, is linked to followup_closure_type',
																	closed_datetime			DATETIME				NULL						COMMENT 'Time that the FollowUp is closed',
																	followup_recurring_id	INT			UNSIGNED	NULL						COMMENT '(fk) followup_recurring - optional link to a Recurring FollowUp',
																	modified_datetime		DATETIME				NOT NULL					COMMENT 'Last time that the FollowUp was modified',
																	modified_employee_id	BIGINT		UNSIGNED	NOT NULL					COMMENT '(fk) Employee - who was last to modify the FollowUp',
																	CONSTRAINT	pk_followup_id						PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_assigned_employee_id	FOREIGN KEY	(assigned_employee_id)	REFERENCES Employee(Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_followup_type_id		FOREIGN KEY	(followup_type_id)		REFERENCES followup_type(id)		ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_followup_category_id	FOREIGN KEY	(followup_category_id)	REFERENCES followup_category(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_followup_closure_id		FOREIGN KEY	(followup_closure_id)	REFERENCES followup_closure(id)		ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_modified_employee_id	FOREIGN KEY	(modified_employee_id)	REFERENCES Employee(Id)				ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_recurring' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_recurring
																(
																	id								INT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
																	assigned_employee_id			BIGINT		UNSIGNED	NOT NULL								COMMENT '(fk) Employee - who is assigned the Recurring Followup',
																	created_datetime				DATETIME				NOT NULL								COMMENT 'Time the Recurring FollowUp is created',
																	start_datetime					DATETIME				NOT NULL								COMMENT 'DateTime that the Recurring FollowUp will start recurring',
																	end_datetime 					DATETIME				NULL		DEFAULT '9999-12-31 23:59'	COMMENT 'DateTime that the Recurring FollowUp will stop recurring',
																	followup_type_id				INT			UNSIGNED	NOT NULL								COMMENT '(fk) followup_type',
																	followup_category_id			INT			UNSIGNED	NOT NULL								COMMENT '(fk) followup_category',
																	recurrence_multiplier			INT			UNSIGNED	NOT NULL								COMMENT 'How many recurrence periods should pass between due dates',
																	followup_recurrence_period_id	INT			UNSIGNED	NOT NULL								COMMENT '(fk) followup_recurrence_period_id',
																	modified_datetime				DATETIME				NOT NULL								COMMENT 'Last time that the Recurring FollowUp was modified',
																	modified_employee_id			BIGINT		UNSIGNED	NOT NULL								COMMENT '(fk) Employee - who was last to modify the Recurring FollowUp',
																	CONSTRAINT	pk_followup_recurring_id							PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_recurring_assigned_employee_id			FOREIGN KEY	(assigned_employee_id)			REFERENCES Employee(Id)						ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_followup_type_id				FOREIGN KEY	(followup_type_id)				REFERENCES followup_type(id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_followup_category_id			FOREIGN KEY	(followup_category_id)			REFERENCES followup_category(id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_followup_recurrence_period_id	FOREIGN KEY	(followup_recurrence_period_id)	REFERENCES followup_recurrence_period(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_modified_employee_id			FOREIGN KEY	(modified_employee_id)			REFERENCES Employee(Id)						ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_recurring;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_history' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_history
																(
																	id						INT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_id				INT			UNSIGNED	NOT NULL					COMMENT	'(fk) followup',
																	due_datetime			DATETIME				NOT NULL					COMMENT 'DateTime that the FollowUp was due',
																	assigned_employee_id	BIGINT		UNSIGNED	NOT NULL					COMMENT	'(fk) Employee - who was assigned the FollowUp',
																	modified_datetime		DATETIME				NOT NULL					COMMENT 'Time that the FollowUp was modified',
																	modified_employee_id	BIGINT		UNSIGNED	NOT NULL					COMMENT '(fk) Employee - who modified the FollowUp',
																	CONSTRAINT	pk_followup_history_id						PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_history_followup_id				FOREIGN KEY (followup_id)			REFERENCES	followup(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_history_assigned_employee_id	FOREIGN KEY (assigned_employee_id)	REFERENCES	Employee(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_history_modified_employee_id	FOREIGN KEY	(modified_employee_id)	REFERENCES	Employee(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_history;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_recurring_history' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_recurring_history
																(
																	id						INT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_recurring_id	INT			UNSIGNED	NOT NULL					COMMENT '(fk) followup_recurring',
																	assigned_employee_id	BIGINT		UNSIGNED	NOT NULL					COMMENT	'(fk) Employee - who was assigned the Recurring FollowUp',
																	end_datetime			DATETIME				NULL						COMMENT	'DateTime that the Recurring FollowUp will stop recurring',
																	modified_datetime		DATETIME				NOT NULL					COMMENT 'Time that the Recurring FollowUp was modified',
																	modified_employee_id	BIGINT		UNSIGNED	NOT NULL					COMMENT '(fk) Employee - who modified the Recurring FollowUp',
																	CONSTRAINT	pk_followup_recurring_history_id					PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_recurring_history_followup_recurring_id	FOREIGN KEY (followup_recurring_id)	REFERENCES	followup_recurring(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_history_assigned_employee_id	FOREIGN KEY (assigned_employee_id)	REFERENCES	Employee(Id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_history_modified_employee_id	FOREIGN KEY	(modified_employee_id)	REFERENCES	Employee(Id)			ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_recurring_history;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_note' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_note
																(
																	id				INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_id		INT		UNSIGNED	NOT NULL					COMMENT	'(fk) followup',
																	note_id			BIGINT	UNSIGNED	NOT NULL					COMMENT	'(fk) note - that the FollowUp relates to',
																	CONSTRAINT	pk_followup_note_id				PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_note_followup_id	FOREIGN KEY (followup_id)	REFERENCES	followup(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_note_note_id		FOREIGN KEY (note_id)		REFERENCES	Note(Id)		ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_note;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_action' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_action
																(
																	id				INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_id		INT		UNSIGNED	NOT NULL					COMMENT	'(fk) followup',
																	action_id		BIGINT	UNSIGNED	NOT NULL					COMMENT	'(fk) action - that the FollowUp relates to',
																	CONSTRAINT	pk_followup_action_id			PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_action_followup_id	FOREIGN KEY (followup_id)	REFERENCES	followup(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_action_action_id	FOREIGN KEY (action_id)		REFERENCES	`action`(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_action;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_ticketing_correspondence' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_ticketing_correspondence
																(
																	id								INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_id						INT		UNSIGNED	NOT NULL					COMMENT	'(fk) followup',
																	ticketing_correspondence_id		BIGINT	UNSIGNED	NOT NULL					COMMENT	'(fk) ticketing_correspondence - that the FollowUp relates to',
																	CONSTRAINT	pk_followup_ticketing_correspondence_id								PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_ticketing_correspondence_followup_id					FOREIGN KEY (followup_id)					REFERENCES	followup(id)					ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_ticketing_correspondence_ticketing_correspondence_id	FOREIGN KEY (ticketing_correspondence_id)	REFERENCES	ticketing_correspondance(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_ticketing_correspondence;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_recurring_note' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_recurring_note
																(
																	id						INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_recurring_id	INT		UNSIGNED	NOT NULL					COMMENT	'(fk) followup_recurring',
																	note_id					BIGINT	UNSIGNED	NOT NULL					COMMENT	'(fk) note - that the Recurring FollowUp relates to',
																	CONSTRAINT	pk_followup_recurring_note_id						PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_recurring_note_followup_recurring_id	FOREIGN KEY (followup_recurring_id)	REFERENCES	followup_recurring(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_note_note_id					FOREIGN KEY (note_id)				REFERENCES	Note(Id)				ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_recurring_note;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_recurring_action' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_recurring_action
																(
																	id						INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_recurring_id	INT		UNSIGNED	NOT NULL					COMMENT	'(fk) followup_recurring',
																	action_id				BIGINT	UNSIGNED	NOT NULL					COMMENT	'(fk) action - that the Recurring FollowUp relates to',
																	CONSTRAINT	pk_followup_recurring_action_id						PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_recurring_action_followup_recurring_id	FOREIGN KEY (followup_recurring_id)	REFERENCES	followup_recurring(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_action_action_id				FOREIGN KEY (action_id)				REFERENCES	`action`(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_recurring_action;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add 'followup_recurring_ticketing_correspondence' table",
									'sAlterSQL'			=>	"	CREATE TABLE	followup_recurring_ticketing_correspondence
																(
																	id								INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT	'Unique Identifier',
																	followup_recurring_id			INT		UNSIGNED	NOT NULL					COMMENT	'(fk) followup_recurring',
																	ticketing_correspondence_id		BIGINT	UNSIGNED	NOT NULL					COMMENT	'(fk) ticketing_correspondence - that the Recurring FollowUp relates to',
																	CONSTRAINT	pk_followup_recurring_ticketing_correspondence_id		PRIMARY KEY	(id),
																	CONSTRAINT	fk_followup_recurring_ticketing_correspondence_f_r_id	FOREIGN KEY (followup_recurring_id)			REFERENCES	followup_recurring(id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_followup_recurring_ticketing_correspondence_t_c_id	FOREIGN KEY (ticketing_correspondence_id)	REFERENCES	ticketing_correspondance(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	followup_recurring_ticketing_correspondence;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Insert records into 'followup_type'",
									'sAlterSQL'			=>	"	INSERT INTO	followup_type(name, description, const_name, system_name)
																VALUES		('Note',					'Relates to a Note',							'FOLLOWUP_TYPE_NOTE', 					'NOTE'),
																			('Action',					'Relates to an Action',							'FOLLOWUP_TYPE_ACTION',					'ACTION'),
																			('Ticket Correspondence',	'Relates to a piece of Ticket Correspondence',	'FOLLOWUP_TYPE_TICKET_CORRESPONDENCE',	'TICKET_CORRESPONDENCE');",
									'sRollbackSQL'		=>	"",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Insert records into 'followup_recurrence_period'",
									'sAlterSQL'			=>	"	INSERT INTO	followup_recurrence_period(name, description, const_name, system_name)
																VALUES		('Week',	'Week',		'FOLLOWUP_RECURRENCE_PERIOD_WEEK', 	'WEEK'),
																			('Month',	'Month',	'FOLLOWUP_RECURRENCE_PERIOD_MONTH',	'MONTH');",
									'sRollbackSQL'		=>	"",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Insert records into 'followup_closure_type'",
									'sAlterSQL'			=>	"	INSERT INTO	followup_closure_type(name, description, const_name, system_name)
																VALUES		('Completed',	'The Follow-Up has been completed',	'FOLLOWUP_CLOSURE_TYPE_COMPLETED', 	'COMPLETED'),
																			('Dismissed',	'The Follow-Up has been dismissed',	'FOLLOWUP_CLOSURE_TYPE_DISMISSED',	'DISMISSED');",
									'sRollbackSQL'		=>	"",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								)
							);
		
		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation)
		{
			$iStepNumber++;
			
			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");
			
			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (PEAR::isError($oResult))
			{
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}
			
			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation))
			{
				$aRollbackSQL	= (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);
				
				foreach ($aRollbackSQL as $sRollbackQuery)
				{
					if (trim($sRollbackQuery))
					{
						$this->rollbackSQL[] =	$sRollbackQuery;
					}
				}
			}
		}
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