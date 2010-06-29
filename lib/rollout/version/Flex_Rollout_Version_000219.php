<?php

/**
 * Version 219 of database update.
 * This version: -
 *
 *	1:	Add the 'VNS Solutions' Carrier
 *
 */

class Flex_Rollout_Version_000219 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$aOperations	=	array();
		
		// Check to see if VNS Solutions has already been added
		$oResult	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)->query("SELECT Id FROM Carrier WHERE const_name = 'CARRIER_VNS_SOLUTIONS'");
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to detect if VNS Solutions were already defined as a Carrier. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		elseif ($oResult->numRows())
		{
			// VNS Solutions already exists as a Carrier
			$this->outputMessage("Skipping ".self::getRolloutVersionNumber(__CLASS__).": VNS Solutions already exists as a Carrier...\n");
			return;
		}
		
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the 'VNS Solutions' Carrier",
									'sAlterSQL'			=>	"	INSERT INTO	Carrier
																	(Name				, carrier_type																		, description						, const_name)
																VALUES
																	('VNS Solutions'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE')	, 'VNS Solutions'					, 'CARRIER_VNS_SOLUTIONS');",
									'sRollbackSQL'		=>	array
															(
																"DELETE FROM	Carrier	WHERE const_name IN ('CARRIER_VNS_SOLUTIONS');",
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