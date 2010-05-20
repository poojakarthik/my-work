<?php

/**
 * Version 217 of database update.
 * This version: -
 *
 *	1:	Add action_type_action_association_types for 'Adjustment Requested' and 'Adjustment Request Outcome'
 */

class Flex_Rollout_Version_000217 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add action_type_action_association_types for 'Adjustment Requested' and 'Adjustment Request Outcome'",
									'sAlterSQL'			=>	"	INSERT INTO	action_type_action_association_type (action_type_id, action_association_type_id)
																VALUES		(
																				(
																					SELECT	id
																					FROM	action_type
																					WHERE	name = 'Adjustment Requested'
																				), 1
																			),
																			(
																				(
																					SELECT	id
																					FROM	action_type
																					WHERE	name = 'Adjustment Requested'
																				), 2
																			),
																			(
																				(
																					SELECT	id
																					FROM	action_type
																					WHERE	name = 'Adjustment Request Outcome'
																				), 1
																			),
																			(
																				(
																					SELECT	id
																					FROM	action_type
																					WHERE	name = 'Adjustment Request Outcome'
																				), 2
																			);",
									'sRollbackSQL'		=>	"	DELETE FROM action_type_action_association_type
																WHERE		action_type_id IN (
																				SELECT	id
																				FROM	action_type
																				WHERE	name IN ('Adjustment Requested', 'Adjustment Request Outcome')
																			);",
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