<?php

/**
 * Cli_App_Payments
 *
 * @parent	Cli
 */
class Cli_App_Payments extends Cli
{
	const	SWITCH_TEST_RUN				= 't';
	const	SWITCH_MODE					= 'm';
	const	SWITCH_PAYMENT_ID			= 'p';
	const	SWITCH_PAYMENT_RESPONSE_ID	= 'r';
	const	SWITCH_FILE_IMPORT_ID		= 'f';
	const	SWITCH_FILE_IMPORT_DATA_ID	= 'd';
	const	SWITCH_LIMIT				= 'x';
	
	const	MODE_PROCESS	= 'PROCESS';
	const	MODE_NORMALISE	= 'NORMALISE';
	const	MODE_APPLY		= 'APPLY';
	const	MODE_EXPORT		= 'EXPORT';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_aArgs = $this->getValidatedArguments();
			
			$sMode	= '_'.strtolower($this->_aArgs[self::SWITCH_MODE]);
			if (!method_exists($this, $sMode))
			{
				throw new Exception("Invalid Mode '{$sMode}'");
			}
			else
			{
				$this->$sMode();
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	protected function _process()
	{
		// Optional FileImport.Id parameter
		$iFileImportId	= $this->_aArgs[self::SWITCH_FILE_IMPORT_ID];
		if ($iFileImportId && ($oFileImport = File_Import::getForId()))
		{
			if ($oFileImport->Status !== FILE_IMPORTED)
			{
				throw new Exception("Only Files with Status FILE_IMPORTED (".FILE_IMPORTED.") can be Processed");
			}
			
			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}
		
		// Optional Limit Parameter
		$iLimit	= (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);
		
		// Process the Files
		try
		{
			Resource_Type_File_Import_Payment::preProcessFiles($iFileImportId, $iLimit);
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}
	
	protected function _normalise()
	{
		// Optional file_import_data.id parameter
		$iFileImportDataId	= $this->_aArgs[self::SWITCH_FILE_IMPORT_DATA_ID];
		if ($iFileImportDataId && ($oFileImportData = File_Import_Data::getForId($iFileImportDataId)))
		{
			if ($oFileImportData->Status !== FILE_IMPORT_DATA_STATUS_IMPORTED)
			{
				throw new Exception("Only File Data with Status FILE_IMPORT_DATA (".FILE_IMPORT_DATA_STATUS_IMPORTED.") can be Normalised");
			}
			
			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}
		
		// Optional Limit Parameter
		$iLimit	= (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);
		
		// Process the Records
		try
		{
			Resource_Type_File_Import_Payment::processRecords($iFileImportDataId, $iLimit);
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}
	
	protected function _apply()
	{
		
	}
	
	protected function _export()
	{
		try
		{
			$bTestRun	= $this->_aArgs[self::SWITCH_TEST_RUN];
			if ($bTestRun)
			{
				Log::getLog()->log("** TEST MODE **");
				Log::getLog()->log("The exported files will NOT be delivered, instead will be emailed to ybs-admin@ybs.net.au");
				
				// Enable file delivery testing (this will force emailling of all files to ybs-admin@ybs.net.au)
				Resource_Type_File_Deliver::enableTestMode();
				
				$oDataAccess	= DataAccess::getDataAccess();
				if ($oDataAccess->TransactionStart() === false)
				{
					throw new Exception("Failed to START db transaction");
				}
				Log::getLog()->log("Transaction started");
			}
			
			Resource_Type_File_Export_Payment::exportDirectDebits();
			
			if ($bTestRun)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to ROLLBACK db transaction");
				}
				Log::getLog()->log("Transaction rolled back");
			}
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to export. ".$oException->getMessage());
		}
	}
	
	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Payment operation to perform [".self::MODE_PROCESS."|".self::MODE_NORMALISE."|".self::MODE_APPLY."|".self::MODE_EXPORT."]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("'.self::MODE_PROCESS.'","'.self::MODE_NORMALISE.'","'.self::MODE_APPLY.'","'.self::MODE_EXPORT.'"))'
			),
			
			self::SWITCH_PAYMENT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "PAYMENT_ID",
				self::ARG_DESCRIPTION	=> "Payment Id (".self::MODE_APPLY." Mode only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_PAYMENT_RESPONSE_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "PAYMENT_RESPONSE_ID",
				self::ARG_DESCRIPTION	=> "Payment Response Id (".self::MODE_NORMALISE." Mode only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_FILE_IMPORT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "FILE_IMPORT_ID",
				self::ARG_DESCRIPTION	=> "File Import Id (".self::MODE_PROCESS.", ".self::MODE_NORMALISE.", ".self::MODE_APPLY." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_LIMIT => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "LIMIT",
				self::ARG_DESCRIPTION	=> "Limit/Maximum Items to Process (".self::MODE_PROCESS.", ".self::MODE_NORMALISE." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}

?>