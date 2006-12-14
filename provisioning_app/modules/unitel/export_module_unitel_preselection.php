<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_unitel_preselection
//----------------------------------------------------------------------------//
/**
 * module_export_unitel_preselection
 *
 * Unitel Export Module for the provisioning engine (Preselection)
 *
 * Unitel Export Module for the provisioning engine (Preselection)
 *
 * @file		module_export_unitel_preselection.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportUnitelPreselection
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportUnitelPreselection
 *
 * Unitel Export Module for the provisioning engine (Preselection)
 *
 * Unitel Export Module for the provisioning engine. (Preselection)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportUnitelPreselection
 */
 class ProvisioningModuleExportUnitelPreselection extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportUnitel
	 *
	 * Constructor method for ProvisioningModuleExportUnitel
	 *
	 * @return		ProvisioningModuleExportUnitel
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName = "Unitel";
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));

 	}

  	//------------------------------------------------------------------------//
	// BuildRequest()
	//------------------------------------------------------------------------//
	/**
	 * BuildRequest()
	 *
	 * Builds a request file
	 *
	 * Builds a request file to be sent off, based on info from the DB
	 *
	 * @param		array		$arrRequest		Array of information on the request to generate
	 * 											Taken straight from the DB
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function BuildRequest($arrRequest)
	{
		// Clean the request array
		$arrBuiltRequest = Array();
				
		switch ($arrRequest['RequestType'])
		{
			case REQUEST_PRESELECTION:
				$arrBuiltRequest['RecordType']			= "11";
				$arrBuiltRequest['ServiceNumber']		= $arrRequest['FNN'];
				$arrBuiltRequest['AgreementDate']		= date("Ymd");
				
				// Append to the array for this file
				$arrPreselectionRecords[]				= implode($arrBuiltRequest);
				break;
				
			case REQUEST_BAR:
				$arrBuiltRequest['RecordType']			= "55";
				$arrBuiltRequest['ServiceNumber']		= $arrRequest['FNN'];
				$arrBuiltRequest['Action']				= "1";
				
				// Append to the array for this file
				$arrPreselectionRecords[]				= implode($arrBuiltRequest);
				break;
				
			case REQUEST_UNBAR:
				$arrBuiltRequest['RecordType']			= "55";
				$arrBuiltRequest['ServiceNumber']		= $arrRequest['FNN'];
				$arrBuiltRequest['Action']				= "0";
				
				// Append to the array for this file
				$arrPreselectionRecords[]				= implode($arrBuiltRequest);
				break;
				
			case REQUEST_ACTIVATION:
				$arrBuiltRequest['RecordType']			= "10";
				$arrBuiltRequest['ServiceNumber']			= $arrRequest['FNN'];
				$arrBuiltRequest['AgreementDate']		= date("Ymd");
				
				// Append to the array for this file
				$arrPreselectionRecords[]				= implode($arrBuiltRequest);
				break;
			
			case REQUEST_DEACTIVATION:
				$arrBuiltRequest['RecordType']			= "20";
				$arrBuiltRequest['ServiceNumber']		= $arrRequest['FNN'];
				
				// Append to the array for this file
				$arrPreselectionRecords[]				= implode($arrBuiltRequest);
				break;
			
			case REQUEST_PRESELECTION_REVERSAL:
				$arrRequest['RecordType']				= "21";
				$arrRequest['ServiceNumber']			= $arrRequest['FNN'];
				
				// Append to the array for this file
				$arrPreselectionRecords[]				= implode($arrBuiltRequest);
				break;
			default:
				// Unhandled Request type -> error
				return FALSE;
		}
		
		// Add additional logging data
		$this->_arrLog['Request']	= $arrRequest['Id'];
		$this->_arrLog['Service']	= $arrRequest['Service'];
		$this->_arrLog['Type']		= $arrRequest['Type'];
	} 	
 	
  	//------------------------------------------------------------------------//
	// SendRequest()
	//------------------------------------------------------------------------//
	/**
	 * SendRequest()
	 *
	 * Sends the current request
	 *
	 * Sends the current request
	 *
	 * @return		integer						Number of requests sent in the file
	 *
	 * @method
	 */
 	function SendRequest()
	{
		// Get the latest Sequence Numbers
		$this->_selGetSequence->Execute(Array('Module' => "Unitel"));
		if(!($arrResult = $this->_selGetSequence->FetchAll()))
		{
			// Missing config definitions
			return FALSE;
		}
		
		$intPreselectionFileSequence	= ((int)$arrResult['PreselectionFileSequence']) + 1;
		
		// Build Header Row
		$strPreselectionFilename	= "sarsw".str_pad($intPreselectionFileSequence, 4, "0", STR_PAD_LEFT).".txt";
		$strPreselectionHeaderRow	= "01".date("Ymd").str_pad($intPreselectionFileSequence, 4, "0", STR_PAD_LEFT)."sarsw";
		
		// Get list of requests to generate
		$arrResults = $this->_selGetRequests->FetchAll();
			
		$intNumPreselectionRecords	= count($this->_arrPreselectionRecords);
	
		// Build Footer Rows
		$strPreselectionFooterRow	= "99".str_pad($intNumPreselectionRecords, 7, "0", STR_PAD_LEFT);
		
		// Create Local Preselection File
		if($intNumPreselectionRecords > 0)
		{
			// Only do this if there are records to write
			$resPreselectionFile = fopen(UNITEL_LOCAL_PRESELECTION_DIR.$strPreselectionFilename, "w");
			fwrite($resPreselectionFile, $strPreselectionHeaderRow."\n");
			
			foreach($this->_arrPreselectionRecords as $strRecord)
			{
				fwrite($resPreselectionFile, $strRecord."\n");
			}
			
			fwrite($resPreselectionFile, $strPreselectionFooterRow."\n");
			fclose($resPreselectionFile);
		}
		
		// Upload to FTP
		/* TODO: Uncomment this later on
		$resFTPConnection = ftp_connect(UNITEL_PROVISIONING_SERVER);
		ftp_login($resFTPConnection, UNITEL_PROVISIONING_USERNAME, UNITEL_PROVISIONING_PASSWORD);
		
		if(file_exists(UNITEL_LOCAL_PRESELECTION_DIR.$strPreselectionFilename))
		{
			// Upload the Preselection File
			ftp_chdir($resFTPConnection, UNITEL_REMOTE_PRESELECTION_DIR);
			ftp_put($resFTPConnection, $strPreselectionFilename, UNITEL_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
		}
		
		ftp_close($resFTPConnection);
		*/
		
		// Update database (Request & Config tables)
		$this->_updPreselectSequence->Execute(Array('Value' => "$intPreselectionFileSequence"));
		
		// Return the number of records uploaded
		return $intNumPreselectionRecords;
	} 	
	
 }

?>
