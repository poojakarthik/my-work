<?php

/**
 * Version 224 of database update.
 */

class Flex_Rollout_Version_000224 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=> "Drop trigger for rebill_motorpass on insert",
									'sAlterSQL'			=> "DROP TRIGGER rebill_motorpass_insert;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=> "Create trigger for rebill_motorpass on insert",
									'sAlterSQL'			=> "CREATE TRIGGER rebill_motorpass_insert BEFORE INSERT ON rebill_motorpass
															FOR EACH ROW
															BEGIN
																DECLARE acc_num INTEGER;
																DECLARE expiry DATE;
																DECLARE acc_name VARCHAR(256);

																IF (NEW.motorpass_account_id IS NOT NULL) THEN
																	SELECT	ma.account_name
																	INTO	acc_name
																	FROM	motorpass_account ma
																	WHERE	ma.id = NEW.motorpass_account_id;

																	SELECT	ma.account_number
																	INTO	acc_num
																	FROM	motorpass_account ma
																	WHERE	ma.id = NEW.motorpass_account_id;

																	SELECT	mc.card_expiry_date
																	INTO	expiry
																	FROM	motorpass_account ma
																	JOIN	motorpass_card mc
																				ON ma.motorpass_card_id = mc.id
																	WHERE	ma.id = NEW.motorpass_account_id;

																	SET	NEW.account_name = acc_name;
																	SET	NEW.account_number = acc_num;
																	SET	NEW.card_expiry_date = expiry;
																END IF;
															END;
",
									'sRollbackSQL'		=> "DROP TRIGGER rebill_motorpass_insert;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=> "Drop trigger for rebill_motorpass on update",
									'sAlterSQL'			=> "DROP TRIGGER rebill_motorpass_update;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=> "Create trigger for rebill_motorpass on update",
									'sAlterSQL'			=> "CREATE TRIGGER rebill_motorpass_update BEFORE UPDATE ON rebill_motorpass
															FOR EACH ROW
															BEGIN
																DECLARE acc_num INTEGER;
																DECLARE expiry DATE;
																DECLARE acc_name VARCHAR(256);

																IF (NEW.motorpass_account_id IS NOT NULL) THEN
																	SELECT	ma.account_name
																	INTO	acc_name
																	FROM	motorpass_account ma
																	WHERE	ma.id = NEW.motorpass_account_id;

																	SELECT	ma.account_number
																	INTO	acc_num
																	FROM	motorpass_account ma
																	WHERE	ma.id = NEW.motorpass_account_id;

																	SELECT	mc.card_expiry_date
																	INTO	expiry
																	FROM	motorpass_account ma
																	JOIN	motorpass_card mc
																				ON ma.motorpass_card_id = mc.id
																	WHERE	ma.id = NEW.motorpass_account_id;

																	SET	NEW.account_name = acc_name;
																	SET	NEW.account_number = acc_num;
																	SET	NEW.card_expiry_date = expiry;
																END IF;
															END;",
									'sRollbackSQL'		=> "DROP TRIGGER rebill_motorpass_update;",
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