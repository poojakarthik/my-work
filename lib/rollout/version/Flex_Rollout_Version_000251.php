<?php

/**
 * Version 251 of database update.
 */

class Flex_Rollout_Version_000251 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations = array(
			array(
				'sDescription'		=> "Adds the 'Inventory Supplier' Carrier Type",
				'sAlterSQL'			=> "
					INSERT INTO	carrier_type
						(name					, description			, const_name)
					VALUES
						('Inventory Supplier'	, 'Inventory Supplier'	, 'CARRIER_TYPE_INVENTORY_SUPPLIER');
				",
				'sRollbackSQL'		=> "
					DELETE FROM	carrier_type
					WHERE		const_name = 'CARRIER_TYPE_INVENTORY_SUPPLIER';
				",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Adds the 'MobiCity' Carrier",
				'sAlterSQL'			=> "
					INSERT INTO	Carrier
						(Name		, carrier_type	, description	, const_name)
					VALUES
						('MobiCity'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_INVENTORY_SUPPLIER')	, 'MobiCity'	, 'CARRIER_MOBICITY');
				",
				'sRollbackSQL'		=> "
					DELETE FROM	Carrier
					WHERE		const_name = 'CARRIER_MOBICITY';
				",
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
			if (MDB2::isError($oResult))
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
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>