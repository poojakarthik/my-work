<?php

/**
 * Version 234 of database update.
 */

class Flex_Rollout_Version_000234 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	= 
		array
		(
			array
			(
				'sDescription'		=>	"Update the created_datetime for existing Payment records",
				'sAlterSQL'			=>	"	UPDATE	Payment
											SET		created_datetime = PaidOn;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Temporarily add column payment_id to file_import_data",
				'sAlterSQL'			=>	"	ALTER TABLE	file_import_data
											ADD COLUMN	payment_id BIGINT UNSIGNED NULL;",
				'sRollbackSQL'		=>	"	ALTER TABLE file_import_data
											DROP COLUMN	payment_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Initial file_import_data records. Move all Payment, File & SequenceNo data from Payment to file_import_data, also copy Id (so that the payment can be linked to a payment_response)",
				'sAlterSQL'			=>	"	INSERT INTO	file_import_data (file_import_id, sequence_no, data, file_import_data_status_id, payment_id)
											(
												SELECT	p.File, 
														p.SequenceNo, 
														p.Payment, 
														(
															SELECT	id
															FROM	file_import_data_status
															WHERE	const_name = 'FILE_IMPORT_DATA_STATUS_NORMALISED'
														),
														p.Id
												FROM	Payment p
												JOIN	FileImport fi ON fi.Id = p.File
											);",
				'sRollbackSQL'		=>	"	TRUNCATE file_import_data;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Initial payment_response records. Create a payment_response for each Payment record",
				'sAlterSQL'			=>	"	INSERT INTO	payment_response
														(
															account_group_id, 
															account_id, 
															paid_date, 
															payment_type_id, 
															amount, 
															origin_id, 
															file_import_data_id, 
															transaction_reference, 
															payment_id,
															payment_response_type_id,
															payment_response_status_id, 
															created_datetime
														)
											(
												SELECT		IF(p.AccountGroup <> 0, p.AccountGroup, NULL), 
															IF(a.Id IS NOT NULL, p.Account, NULL), 
															p.PaidOn, 
															IF(pt.id IS NOT NULL, p.PaymentType, NULL), 
															p.Amount, 
															p.OriginId, 
															fid.id, 
															p.TXNReference,
															p.Id, 
															(SELECT id FROM payment_response_type WHERE system_name = 'CONFIRMATION'),
															IF(
																p.Status IN (
																	SELECT	id 
																	FROM 	payment_status 
																	WHERE 	const_name IN ('PAYMENT_IMPORTED', 'PAYMENT_WAITING', 'PAYMENT_PAYING', 'PAYMENT_FINISHED', 'PAYMENT_REVERSED')
																),
																(SELECT id FROM payment_response_status WHERE system_name = 'PROCESSED'),
																(SELECT id FROM payment_response_status WHERE system_name = 'PROCESSING_FAILED')
															), 
															p.created_datetime
												FROM		Payment p
												JOIN		file_import_data fid ON fid.payment_id = p.Id
												LEFT JOIN	Account a ON a.Id = p.Account
												LEFT JOIN	payment_type pt ON pt.id = p.PaymentType
											);",
				'sRollbackSQL'		=>	"	TRUNCATE payment_response;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Set the latest_payment_response_id values in the Payment table",
				'sAlterSQL'			=>	"	UPDATE	Payment p
											SET		p.latest_payment_response_id = (
														SELECT	pr.id
														FROM	payment_response pr
														WHERE	pr.payment_id = p.Id
													);",
				'sRollbackSQL'		=>	"	UPDATE	Payment p
											SET		p.latest_payment_response_id = NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Remove temporary column payment_id from file_import_data",
				'sAlterSQL'			=>	"	ALTER TABLE file_import_data
											DROP COLUMN	payment_id;",
				'sRollbackSQL'		=>	"	ALTER TABLE	file_import_data
											ADD COLUMN	payment_id BIGINT UNSIGNED NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			/*array
			(
				'sDescription'		=>	"Remove the Payment, File & SequenceNo fields from the Payment table",
				'sAlterSQL'			=>	"	ALTER TABLE	Payment
											DROP COLUMN	Payment,
											DROP COLUMN	File,
											DROP COLUMN	SequenceNo;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			)*/
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