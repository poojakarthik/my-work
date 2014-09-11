<?php

/**
 * Version 210 of database update.
 * This version: -
 *
 *	1:	Add the carrier_payment_type Table
 *	2:	Add the 'PMF - Payment Merchant Fee' system Charge Type
 *
 */

class Flex_Rollout_Version_000210 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the carrier_payment_type Table",
									'sAlterSQL'			=>	"	CREATE TABLE	carrier_payment_type
																(
																	id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	carrier_id			BIGINT				NOT NULL					COMMENT '(FK) Carrier',
																	payment_type_id		BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Payment Type',
																	surcharge_percent	DECIMAL(4,4)		NULL						COMMENT 'Merchant Fee defined as a percentage of Payment value',
																	
																	CONSTRAINT	pk_carrier_payment_type_id					PRIMARY KEY	(id),
																	CONSTRAINT	fk_carrier_payment_type_carrier_id			FOREIGN KEY	(carrier_id)		REFERENCES Carrier(Id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_carrier_payment_type_payment_charge_id	FOREIGN KEY	(payment_type_id)	REFERENCES payment_type(id)		ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	carrier_payment_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the 'PMF - Payment Merchant Fee' system Charge Type",
									'sAlterSQL'			=>	"	INSERT INTO	ChargeType
																	(ChargeType		, Description				, Nature	, Fixed	, automatic_only	, Amount	, Archived	, charge_type_visibility_id)
																VALUES
																	('PMF'			, 'Payment Merchant Fee'	, 'DR'		, 0		, 1					, 0.00		, 0			, (SELECT id FROM charge_type_visibility WHERE const_name = 'CHARGE_TYPE_VISIBILITY_VISIBLE'));",
									'sRollbackSQL'		=>	"DELETE FROM ChargeType WHERE ChargeType = 'PMF' AND automatic_only = 1;",
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