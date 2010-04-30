<?php

/**
 * Version 208 of database update.
 * This version: -
 *
 *	1:	Create the rebill_motorpass Table
 *	2:	Remove the customer_group_billing_type Table (added in 207, but redundant with customer_group_payment_method)
 *
 */

class Flex_Rollout_Version_000208 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Create the rebill_motorpass Table",
									'sAlterSQL'			=>	"	CREATE TABLE	rebill_motorpass
																(
																	id				BIGINT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	rebill_id		BIGINT		UNSIGNED	NOT NULL					COMMENT '(FK) The Rebill record this defines',
																	account_number	INTEGER(9)	UNSIGNED	NOT NULL					COMMENT 'Retail Decisions/Motorpass Account Number',
																	
																	CONSTRAINT	pk_rebill_motorpass_id			PRIMARY KEY (id),
																	CONSTRAINT	fk_rebill_motorpass_rebill_id	FOREIGN KEY	(rebill_id)	REFERENCES rebill(id)	ON UPDATE CASCADE	ON DELETE CASCADE
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	billing_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Remove the customer_group_billing_type Table",
									'sAlterSQL'			=>	"	DROP TABLE IF EXISTS	customer_group_billing_type;",
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