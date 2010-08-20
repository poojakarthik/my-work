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
					$this->_export();
					break;
				case 'IMPORT':
					$this->_import();
					break;
			}
		}
		catch (Exception $oException)
		{
			return 1;
		}
	}
	
	protected function _export()
	{
		/*
			SUPERHACK!
			This is very specific, only supporting one export module
		 */
		$aCarrierModules	= Carrier_Module::getForCarrierModuleType(MODULE_TYPE_MOTORPASS_FILE_EXPORT);
		if ($aCarrierModules > 1)
		{
			throw new Exception("There is more than one Motorpass Provisioning Export Carrier Module defined: ".print_r($aCarrierModules, true));
		}
		if ($aCarrierModules < 1)
		{
			throw new Exception("There is no Motorpass Provisioning Export Carrier Module defined");
		}
		
		$oCarrierModule			= array_pop($aCarrierModules);
		$sClassName				= $oCarrierModule->Module;
		$oResourceTypeHandler	= new $sClassName($oCarrierModule);
		
		// Find a list of "requests" to export
		// FIXME:	Should this logic be moved to the Resource Type handler itself?
		//			Base class (for 'type group', e.g. Motorpass Export base class) would contain the query to retrieve and update records
		//			This would allow us to have a generic provisioning export program, which delegates service/whatever specifics to the modules themselves
		$oQuery	= new Query();
		$sSQL	= "	SELECT		rm.*
								
					FROM		rebill_motorpass rm
								JOIN motorpass_account ma ON (ma.id = rm.motorpass_account_id)
					
					WHERE		motorpass_account_status_id = ".MOTORPASS_ACCOUNT_STATUS_AWAITING_DISPATCH;
		if (($mResult = $oQuery->Execute($sSQL)) === false)
		{
			throw new Exception($oQuery->Error());
		}
		$aExportedRecords	= array();
		while ($aRebillMotorpass = $mResult->fetch_assoc())
		{
			// Process the record
			$oResourceTypeHandler->addRecord($aRebillMotorpass);
			$aExportedRecords[] = $aRebillMotorpass;
		}
		
		// Render the file & log to the database
		$oResourceTypeHandler->render()->save();
		
		// Update Records
		// FIXME:	Should this logic be moved to the Resource Type handler itself?
		//			Base class (for 'type group', e.g. Motorpass Export base class) would contain the query to retrieve and update records
		//			This would allow us to have a generic provisioning export program, which delegates service/whatever specifics to the modules themselves
		foreach ($aExportedRecords as $aRebillMotorpass)
		{
			$oRebillMotorpass	= Rebill_Motorpass::getForId(ORM::extractId($aRebillMotorpass));
			
			$oRebillMotorpass->motorpass_account_status_id	= MOTORPASS_ACCOUNT_STATUS_DISPATCHED;
			$oRebillMotorpass->file_export_id				= $oResourceTypeHandler->getFileExport()->Id;
		}
		
		if (!$this->_arrArgs[self::SWITCH_TEST_RUN])
		{
			// Deliver the file
			// DEBUG: Never deliver this file! (until we hit production)
			//$oResourceTypeHandler->deliver();
		}
		else
		{
			throw new Exception("Test Mode: No files have been delivered, and database changes will be reverted");
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