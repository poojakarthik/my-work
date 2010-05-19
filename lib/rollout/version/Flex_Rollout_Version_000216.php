<?php

/**
 * Version 216 of database update.
 * This version: -
 *
 *	1:	Update action_type 'Adjustment Requested' to be 'Charge Requested'
 * 	2:	Update action_type 'Adjustment Request Outcome' to be 'Charge Request Outcome'
 * 	3:	Update action_type 'Recurring Adjustment Requested' to be 'Recurring Charge Requested'
 * 	4:	Update action_type 'Recurring Adjustment Request Outcome' to be 'Recurring Charge Request Outcome'
 *  5:	Insert new action_type 'Adjustment Requested'
 * 	6:	Insert new action_type 'Adjustment Request Outcome'
 */

class Flex_Rollout_Version_000216 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Update action_type 'Adjustment Requested' to be 'Charge Requested'",
									'sAlterSQL'			=>	"	UPDATE	action_type
																SET		name = 'Charge Requested',
																		description = 'Charge Requested'
																WHERE	id = 12;",
									'sRollbackSQL'		=>	"	UPDATE	action_type
																SET		name = 'Adjustment Requested',
																		description = 'Adjustment Requested'
																WHERE	id = 12;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Update action_type 'Adjustment Request Outcome' to be 'Charge Request Outcome'",
									'sAlterSQL'			=>	"	UPDATE	action_type
																SET		name = 'Charge Request Outcome',
																		description = 'Charge Request Outcome'
																WHERE	id = 13;",
									'sRollbackSQL'		=>	"	UPDATE	action_type
																SET		name = 'Adjustment Request Outcome',
																		description = 'Adjustment Request Outcome'
																WHERE	id = 13;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Update action_type 'Recurring Adjustment Requested' to be 'Recurring Charge Requested'",
									'sAlterSQL'			=>	"	UPDATE	action_type
																SET		name = 'Recurring Charge Requested',
																		description = 'Recurring Charge Requested'
																WHERE	id = 14;",
									'sRollbackSQL'		=>	"	UPDATE	action_type
																SET		name = 'Recurring Adjustment Requested',
																		description = 'Recurring Adjustment Requested'
																WHERE	id = 14;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Update action_type 'Recurring Adjustment Request Outcome' to be 'Recurring Charge Request Outcome'",
									'sAlterSQL'			=>	"	UPDATE	action_type
																SET		name = 'Recurring Charge Request Outcome',
																		description = 'Recurring Charge Request Outcome'
																WHERE	id = 15;",
									'sRollbackSQL'		=>	"UPDATE	action_type
															SET		name = 'Recurring Adjustment Request Outcome',
																	description = 'Recurring Adjustment Request Outcome'
															WHERE	id = 15;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Insert new action_type 'Adjustment Requested'",
									'sAlterSQL'			=>	"	INSERT INTO	action_type (name, description, action_type_detail_requirement_id, is_automatic_only, is_system, active_status_id)
																VALUES		('Adjustment Requested', 'Adjustment Requested', 3,	1, 1, 2);",
									'sRollbackSQL'		=>	"	DELETE FROM	action_type
																WHERE		name = 'Adjustment Requested'
																AND			id <> 12;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Insert new action_type 'Adjustment Request Outcome'",
									'sAlterSQL'			=>	"	INSERT INTO	action_type (name, description, action_type_detail_requirement_id, is_automatic_only, is_system, active_status_id)
																VALUES		('Adjustment Request Outcome', 'Adjustment Request Outcome', 3, 1, 1, 2);",
									'sRollbackSQL'		=>	"	DELETE FROM	action_type
																WHERE		name = 'Adjustment Request Outcome'
																AND			id <> 13;",
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