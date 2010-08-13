<?php
/**
 * Cli_App_RetailDecisions
 *
 * Application to
 *
 * @class	Cli_App_RetailDecisions
 * @parent	Cli
 */
class Cli_App_RetailDecisions extends Cli
{
	const	SWITCH_TEST_RUN		= 't';
	
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
			
			// Export
			$this->_export();
			
			// Import
			$this->_import();
		}
		catch (Exception $oException)
		{
			return 1;
		}
	}
	
	protected function _export()
	{
		// Find a list of "requests" to export
		$sSQL	= "	SELECT		*
								
					FROM		rebill_motorpass rm
					
					WHERE		account_number IS NULL
								AND motorpass_status_id = ".MOTORPASS_STATUS_REQUESTED;
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
			),
			
			self::SWITCH_INPUT_FILE => array(
				self::ARG_REQUIRED		=> true,
				self::ARG_LABEL			=> "CSV_FILE",
				self::ARG_DESCRIPTION	=> "CSV File to Import from",
				self::ARG_VALIDATION	=> 'Cli::_validFile("%1$s", true)'
			),
		);
	}
}
?>