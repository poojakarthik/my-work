<?php

/**
 * Version 233 of database update.
 */

class Flex_Rollout_Version_000233 extends Flex_Rollout_Version
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
				'sDescription'		=>	"Populate the payment_request_status table",
				'sAlterSQL'			=>	"	INSERT INTO	payment_request_status (name, description, const_name, system_name)
											VALUES		('Pending', 	'Request is awaiting dispatch', 						'PAYMENT_REQUEST_STATUS_PENDING', 		'PENDING'),
														('Dispatched', 	'Request has been dispatched', 							'PAYMENT_REQUEST_STATUS_DISPATCHED', 	'DISPATCHED'),
														('Cancelled',	'Request has been cancelled, will not be dispatched', 	'PAYMENT_REQUEST_STATUS_CANCELLED', 	'CANCELLED');",
				'sRollbackSQL'		=>	"	TRUNCATE payment_request_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Populate table file_import_data_status",
				'sAlterSQL'			=>	"	INSERT INTO file_import_data_status (name, description, const_name, system_name)
											VALUES		('Imported',				'Imported',				'FILE_IMPORT_DATA_STATUS_IMPORTED',				'IMPORTED'),
														('Normalised',				'Normalised',			'FILE_IMPORT_DATA_STATUS_NORMALISED',			'NORMALISED'),
														('Normalisation Failed',	'Normalisation Failed',	'FILE_IMPORT_DATA_STATUS_NORMALISATION_FAILED',	'NORMALISATION_FAILED'),
														('Ignored',					'Ignored',				'FILE_IMPORT_DATA_STATUS_IGNORED',				'IGNORED');",
				'sRollbackSQL'		=>	"	TRUNCATE file_import_data_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Populate payment_response_status",
				'sAlterSQL'			=>	"	INSERT INTO	payment_response_status (name, description, const_name, system_name)
											VALUES		('Imported',			'The response has been imported', 		'PAYMENT_RESPONSE_STATUS_IMPORTED', 			'IMPORTED'),
														('Processed',			'The response has been processed', 		'PAYMENT_RESPONSE_STATUS_PROCESSED',			'PROCESSED'),
														('Processing Failed',	'The response failed to be processed', 	'PAYMENT_RESPONSE_STATUS_PROCESSING_FAILED',	'PROCESSING_FAILED');",
				'sRollbackSQL'		=>	"	TRUNCATE payment_response_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Populate payment_response_type",
				'sAlterSQL'			=>	"	INSERT INTO	payment_response_type (name, description, const_name, system_name)
											VALUES		('Confirmation',	'Payment confirmed/settled/completed', 	'PAYMENT_RESPONSE_TYPE_CONFIRMATION', 	'CONFIRMATION'),
														('Rejection',		'Payment rejected', 					'PAYMENT_RESPONSE_TYPE_REJECTION', 		'REJECTION');",
				'sRollbackSQL'		=>	"	TRUNCATE payment_response_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add new payment_types 'Direct Debit via EFT' & 'Direct Debit via Credit Card'",
				'sAlterSQL'			=>	"	INSERT INTO	payment_type (name, description, const_name)
											VALUES		('Direct Debit via EFT', 			'Direct Debit via EFT',			'PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT'),
														('Direct Debit via Credit Card', 	'Direct Debit via Credit Card', 'PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD');",
				'sRollbackSQL'		=>	"	DELETE FROM payment_type WHERE const_name IN ('PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT', 'PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add new action_type 'EFT One Time Payment'",
				'sAlterSQL'			=>	"	INSERT INTO	action_type (name, description, action_type_detail_requirement_id, is_automatic_only, is_system, active_status_id)
											VALUES		(
															'EFT One Time Payment', 
															'EFT One Time Payment', 
															(SELECT id FROM action_type_detail_requirement WHERE const_name = 'ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED'),
															1, 
															1, 
															(SELECT id FROM active_status WHERE const_name = 'ACTIVE_STATUS_ACTIVE')
														);",
				'sRollbackSQL'		=>	"	DELETE FROM action_type WHERE name = 'EFT One Time Payment';",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add new action_type_action_association_type for 'EFT One Time Payment' and ACTION_ASSOCIATION_TYPE_ACCOUNT",
				'sAlterSQL'			=>	"	INSERT INTO	action_type_action_association_type (action_type_id, action_association_type_id)
											VALUES		(
															(SELECT id FROM action_type WHERE name = 'EFT One Time Payment'),
															(SELECT id FROM action_association_type WHERE const_name = 'ACTION_ASSOCIATION_TYPE_ACCOUNT')
														);",
				'sRollbackSQL'		=>	"	DELETE FROM	action_type_action_association_type 
											WHERE 		action_type_id = (SELECT id FROM action_type WHERE name = 'EFT One Time Payment');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Add a new resource_type 'Email' = FILE_DELIVERER_EMAIL",	
				'sAlterSQL'			=> "INSERT INTO	resource_type (name, description, const_name, resource_type_nature)
										VALUES		('Email', 'Email', 'RESOURCE_TYPE_FILE_DELIVERER_EMAIL', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_DELIVERER'));",
				'sRollbackSQL'		=> "DELETE FROM resource_type WHERE const_name = 'RESOURCE_TYPE_FILE_DELIVERER_EMAIL';",
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