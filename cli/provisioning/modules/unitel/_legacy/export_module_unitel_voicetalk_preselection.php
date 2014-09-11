<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_unitel_voicetalk_preselection
//----------------------------------------------------------------------------//
/**
 * module_export_unitel_voicetalk_preselection
 *
 * Unitel Export Module for the provisioning engine (Preselection) for VoiceTalk
 *
 * Unitel Export Module for the provisioning engine (Preselection) for VoiceTalk
 *
 * @file		module_export_unitel_voicetalk_preselection.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportUnitelVoiceTalkPreselection
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportUnitelVoiceTalkPreselection
 *
 * Unitel Export Module for the provisioning engine (Preselection) for VoiceTalk
 *
 * Unitel Export Module for the provisioning engine. (Preselection) for VoiceTalk
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportUnitelVoiceTalkPreselection
 */
 class ProvisioningModuleExportUnitelVoiceTalkPreselection extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportUnitelVoiceTalkPreselection
	 *
	 * Constructor method for ProvisioningModuleExportUnitelVoiceTalkPreselection
	 *
	 * @return		ProvisioningModuleExportUnitelVoiceTalkPreselection
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName	= "UnitelVoiceTalk";
		$this->_intCarrier		= CARRIER_UNITEL_VOICETALK;
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'UnitelVoiceTalk' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'UnitelVoiceTalk' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'UnitelVoiceTalk' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));

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
		$this->_selGetAddress					= new StatementSelect(	"Service LEFT OUTER JOIN ServiceAddress ON (ServiceAddress.Service = Service.Id)",
																		"Service.FNN AS FNN, Service.Id AS ServiceId, ServiceAddress.*",
																		"Service.Id = <Service>");
		// Get Service address info
		$arrWhere = Array();
		$arrWhere['Service']	= $arrRequest['Service'];
		$this->_selGetAddress->Execute($arrWhere);
		$arrAddress = $this->_selGetAddress->Fetch();
		
		// Clean the request array
		$arrBuiltRequest = Array();
		
		switch ($arrRequest['RequestType'])
		{
			case REQUEST_PRESELECTION:
				$arrBuiltRequest['RecordType']			= "11";
				$arrBuiltRequest['ServiceNumber']		= $arrAddress['FNN'];
				$arrBuiltRequest['AgreementDate']		= date("Ymd");
				break;
				
			case REQUEST_BAR_SOFT:
			case REQUEST_BAR_HARD:
				$arrBuiltRequest['RecordType']			= "55";
				$arrBuiltRequest['ServiceNumber']		= $arrAddress['FNN'];
				$arrBuiltRequest['Action']				= "1";
				break;
				
			case REQUEST_UNBAR_SOFT:
			case REQUEST_UNBAR_HARD:
				$arrBuiltRequest['RecordType']			= "55";
				$arrBuiltRequest['ServiceNumber']		= $arrAddress['FNN'];
				$arrBuiltRequest['Action']				= "0";
				break;
				
			case REQUEST_ACTIVATION:
				$arrBuiltRequest['RecordType']			= "10";
				$arrBuiltRequest['ServiceNumber']		= $arrAddress['FNN'];
				$arrBuiltRequest['AgreementDate']		= date("Ymd");
				break;
			
			case REQUEST_DEACTIVATION:
				$arrBuiltRequest['RecordType']			= "20";
				$arrBuiltRequest['ServiceNumber']		= $arrAddress['FNN'];
				break;
			
			case REQUEST_PRESELECTION_REVERSE:
				$arrBuiltRequest['RecordType']				= "21";
				$arrBuiltRequest['ServiceNumber']			= $arrAddress['FNN'];
				break;
			default:
				// Unhandled Request type -> error
				return FALSE;
		}
		
		foreach ($this->_arrPreselectionRecords as $arrRecord)
		{
			if ($arrRecord['ServiceNumber'] == $arrBuiltRequest['ServiceNumber'] && $arrRecord['RecordType'] == $arrBuiltRequest['RecordType'])
			{
				// This request already exists in the file - DO NOT DUPLICATE
				$this->_arrLog['Description']	= "Request Not Sent - Duplicate";
				return REQUEST_STATUS_DUPLICATE;
			}
		}
		
		$this->_arrLog['Description']	= "Request Sent Successfully";
		
		// Append to the array for this file
		$this->_arrPreselectionRecords[]	= $arrBuiltRequest;
		
		// Add additional logging data
		$this->_arrLog['Request']	= $arrRequest['Id'];
		$this->_arrLog['Service']	= $arrRequest['Service'];
		$this->_arrLog['Type']		= $arrRequest['RequestType'];
		
		return TRUE;
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
		$this->_selGetSequence->Execute(Array('Module' => "UnitelVoiceTalk", 'Name' => "PreselectionFileSequence"));
		if(!($arrResult = $this->_selGetSequence->Fetch()))
		{
			// Missing config definitions
			return FALSE;
		}
		
		$intPreselectionFileSequence	= ((int)$arrResult['Value']) + 1;
		
		// Build Header Row
		$strPreselectionFilename	= "vorsw".str_pad($intPreselectionFileSequence, 4, "0", STR_PAD_LEFT).".txt";
		$strPreselectionHeaderRow	= "01".date("Ymd").str_pad($intPreselectionFileSequence, 4, "0", STR_PAD_LEFT)."vorsw";
		
		// Get list of requests to generate
		$arrResults = $this->_selGetPreselectRequests->FetchAll();
		
		$intNumPreselectionRecords	= count($this->_arrPreselectionRecords);
		
		// Build Footer Rows
		$strPreselectionFooterRow	= "99".str_pad($intNumPreselectionRecords, 7, "0", STR_PAD_LEFT);
		
		// Create Local Preselection File
		$strLineDelimiter = "\n";
		if($intNumPreselectionRecords > 0)
		{
			$this->_bolSending	= TRUE;
			
			// Only do this if there are records to write
			$resPreselectionFile = fopen(UNITEL_VOICETALK_LOCAL_PRESELECTION_DIR.$strPreselectionFilename, "w");
			fwrite($resPreselectionFile, $strPreselectionHeaderRow."$strLineDelimiter");
			
			foreach($this->_arrPreselectionRecords as $arrRecord)
			{
				$strRecord = implode($arrRecord);
				fwrite($resPreselectionFile, $strRecord."$strLineDelimiter");
			}
			
			fwrite($resPreselectionFile, $strPreselectionFooterRow);
			fclose($resPreselectionFile);

		
			// Upload to FTP
			$resFTPConnection = ftp_connect(UNITEL_VOICETALK_PROVISIONING_SERVER);
			ftp_login($resFTPConnection, UNITEL_VOICETALK_PROVISIONING_USERNAME, UNITEL_VOICETALK_PROVISIONING_PASSWORD);
			
			if(file_exists(UNITEL_VOICETALK_LOCAL_PRESELECTION_DIR.$strPreselectionFilename))
			{
				// Upload the Preselection File
				ftp_chdir($resFTPConnection, UNITEL_REMOTE_PRESELECTION_DIR);
				ftp_put($resFTPConnection, $strPreselectionFilename, UNITEL_VOICETALK_LOCAL_PRESELECTION_DIR.$strPreselectionFilename, FTP_ASCII);
			}
			
			ftp_close($resFTPConnection);
			
			// Update database (Request & Config tables)
			$this->_updPreselectSequence->Execute(Array('Value' => "$intPreselectionFileSequence"), Array());
		}
		
		// Return the number of records uploaded
		return $intNumPreselectionRecords;
	} 	
	
 }

?>
