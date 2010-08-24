<?php
/**
 * Cli_App_Motorpass
 *
 * Application to
 *
 * @class	Cli_App_Motorpass
 * @parent	Cli
 */
class Cli_App_Motorpass extends Cli
{
	const	SWITCH_TEST_RUN		= 't';
	const	SWITCH_MODE			= 'm';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			if ($this->_arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode.  No files will be sent or imported.", true);
			}
			
			switch ($this->_arrArgs[self::SWITCH_MODE])
			{
				case 'EXPORT':
					$this->_export(!$this->_arrArgs[self::SWITCH_TEST_RUN]);
					break;
				case 'IMPORT':
					$this->_import();
					break;
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	protected function _export($bCommit=false)
	{
		/*
			SUPERHACK!
			This is very specific, only supporting one export module at a time
		 */
		Log::getLog()->log('Getting Carrier Modules');
		$aCarrierModules	= Carrier_Module::getForCarrierModuleType(MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT);
		if (count($aCarrierModules) > 1)
		{
			throw new Exception("There is more than one Motorpass Provisioning Export Carrier Module defined: ".print_r($aCarrierModules, true));
		}
		if (count($aCarrierModules) < 1)
		{
			throw new Exception("There is no Motorpass Provisioning Export Carrier Module defined");
		}
		
		$oCarrierModule			= array_pop($aCarrierModules);
		$sClassName				= $oCarrierModule->Module;
		$oResourceTypeHandler	= new $sClassName($oCarrierModule);
		
		Log::getLog()->log('Starting Transaction...');
		if (!DataAccess::getDataAccess()->TransactionStart())
		{
			throw new Exception('Unable to start a transaction');
		}
		
		try
		{
			// NOTE!
			// Generally, this would happen in a common base class for the module 'type',
			// but seeing as this is both the module type and the module itself, it lives in here
			
			// Get Records to Export
			// NOTE!
			// Generally, this would happen in a common base class for the module 'type',
			// but seeing as this is both the module type and the module itself, it lives in here.
			// This method would also only return records that should be exported by this particular module
			
			Log::getLog()->log('Retrieving Records to Export');
			$oQuery	= new Query();
			$sSQL	= "	SELECT		rm.*
									
						FROM		rebill_motorpass rm
									JOIN motorpass_account ma ON (ma.id = rm.motorpass_account_id)
						
						WHERE		motorpass_account_status_id = ".MOTORPASS_ACCOUNT_STATUS_AWAITING_DISPATCH;
			if (($mResult = $oQuery->Execute($sSQL)) === false)
			{
				throw new Exception($oQuery->Error());
			}
			$aRecords	= array();
			while ($aRebillMotorpass = $mResult->fetch_assoc())
			{
				$aRecords[] = $aRebillMotorpass;
			}
			
			Log::getLog()->log('Processing '.count($aRecords).' Records');
			// Process each Record
			$aProcessedRecords	= array();
			foreach ($aRecords as $aRecord)
			{
				$oResourceTypeHandler->addRecord($aRecord);
				$aProcessedRecords[]	= $aRecord;
			}
			
			// FIXME: This should really go somewhere in the module - the file should determine if it's fit for delivery
			if (count($aProcessedRecords) === 0)
			{
				Log::getLog()->log('No Records to Export -- Aborting');
				DataAccess::getDataAccess()->TransactionRollback();
				return;
			}
			
			Log::getLog()->log('Rendering & Saving the File');
			// Render & Save the File
			$oResourceTypeHandler->render()->save();
			
			Log::getLog()->log('Saving exported Records');
			// Save the exported Records
			foreach ($aProcessedRecords as $aRecord)
			{
				$oMotorpassAccount	= Motorpass_Account::getForId($aRecord['motorpass_account_id']);
				
				$oMotorpassAccount->motorpass_account_status_id	= MOTORPASS_ACCOUNT_STATUS_DISPATCHED;
				$oMotorpassAccount->file_export_id				= $oResourceTypeHandler->getFileExport()->Id;
				
				$oMotorpassAccount->save();
			}
			
			if ($bCommit)
			{
				//throw new Exception("DEBUG -- Remove me in production!");
				
				Log::getLog()->log('Delivering to Carrier');
				// Deliver
				//FIXME: Not until production!
				$oResourceTypeHandler->deliver();
				
				Log::getLog()->log('Committing changes to the Flex Database');
				// Commit
				DataAccess::getDataAccess()->TransactionCommit();
			}
			else
			{
				Log::getLog()->log('Test Mode: Rolling back changes');
				// Rollback
				DataAccess::getDataAccess()->TransactionRollback();
			}
		}
		catch (Exception $oException)
		{
			// Rollback and rethrow
			DataAccess::getDataAccess()->TransactionRollback();
			throw $oException;
		}
		
		// All appears to be OK!
		return;
	}
	
	protected function _import()
	{
		// Check FileImport for Files that are pending parsing
		$sSQL	= "	SELECT		*
					
					FROM		FileImport fi
					
					WHERE		fi.FileType = ".RESOURCE_TYPE_IMPORT_RETAILDECISIONS_APPROVALS."
								AND fi.Status = ".FILE_COLLECTED."";
	}
	
	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database and files will not be sent.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_MODE => array(
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Provisioning operation to perform [IMPORT|EXPORT]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("IMPORT","EXPORT"))'
			)
		);
	}
}
?>