<?php

/**
 * Version 209 of database update.
 * This version: -
 *
 *	1:	Add the 'Rebiller' Carrier Type
 *	2:	Add the 'Retail Decisions' and 'Peoples Choice Communication' Carriers
 *
 */

class Flex_Rollout_Version_000209 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the 'Rebiller' Carrier Type",
									'sAlterSQL'			=>	"	INSERT INTO	carrier_type
																	(name		, description		, const_name)
																VALUES
																	('Rebiller'	, 'Rebiller'		, 'CARRIER_TYPE_REBILLER');",
									'sRollbackSQL'		=>	array
															(
																"DELETE FROM carrier_type WHERE const_name = 'CARRIER_TYPE_REBILLER';",
																"ALTER TABLE carrier_type AUTO_INCREMENT = 1;"
															),
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the 'Retail Decisions' and 'Peoples Choice Communication' Carriers",
									'sAlterSQL'			=>	"	INSERT INTO	Carrier
																	(Name				, carrier_type																		, description						, const_name)
																VALUES
																	('People\'s Choice'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE')	, 'People\'s Choice Communications'	, 'CARRIER_PEOPLES_CHOICE'),
																	('Retail Decisions'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_REBILLER')			, 'Retail Decisions'				, 'CARRIER_RETAIL_DECISIONS');",
									'sRollbackSQL'		=>	array
															(
																"DELETE FROM	Carrier	WHERE const_name IN ('CARRIER_PEOPLES_CHOICE', 'CARRIER_RETAIL_DECISIONS');",
																"ALTER TABLE Carrier AUTO_INCREMENT = 1;"
															),
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