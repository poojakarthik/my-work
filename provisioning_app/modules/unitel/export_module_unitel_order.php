<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_unitel_order
//----------------------------------------------------------------------------//
/**
 * module_export_unitel_order
 *
 * Unitel Export Module for the provisioning engine (Daily Order File)
 *
 * Unitel Export Module for the provisioning engine (Daily Order File)
 *
 * @file		module_export_unitel_order.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportUnitelOrder
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportUnitelOrder
 *
 * Unitel Export Module for the provisioning engine (Daily Order File)
 *
 * Unitel Export Module for the provisioning engine.  (Daily Order File)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportUnitelOrder
 */
 class ProvisioningModuleExportUnitelOrder extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportUnitelOrder
	 *
	 * Constructor method for ProvisioningModuleExportUnitelOrder
	 *
	 * @return		ProvisioningModuleExportUnitelOrder
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
		// Get the latest Sequence Number
		$this->_selGetSequence->Execute(Array('Module' => "Unitel"));
		if(!($arrResult = $this->_selGetSequence->FetchAll()))
		{
			// Missing config definitions
			return FALSE;
		}
		$intFullServiceRecordSequence	= (int)$arrResult['FullServiceRecordSequence'];
		
		$this->_selGetAddress->Execute(Array('Id' => $arrRequest['ServiceAddress']));
		if(!($arrAddress = $this->_selGetAddress->Fetch()))
		{
			// There is no entry in the address table - wrong service type
			return FALSE;
		}
		
		// Clean the request array
		$arrBuiltRequest = Array();
				
		$arrBuiltRequest['RecordType']					= "12";
		$arrBuiltRequest['RecordSequence']				= "000000000";
		$arrBuiltRequest['ServiceNumber']				= str_pad($arrRequest['FNN'], 17, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['BasketNumber']				= "000";
		$arrBuiltRequest['CASignedDate']				= "        ";
		$arrBuiltRequest['BillName']					= str_pad($arrAddress['BillName'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['BillAddress1']				= str_pad($arrAddress['BillAddress1'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['BillAddress2']				= str_pad($arrAddress['BillAddress2'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['BillLocality']				= str_pad($arrAddress['BillLocality'], 23, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['BillPostcode']				= $arrAddress['BillPostcode'];
		$arrBuiltRequest['EndUserTitle']				= str_pad($arrAddress['EndUserTitle'], 4, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['EndUserGivenName']			= str_pad($arrAddress['EndUserGivenName'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['EndUserLastName']				= str_pad($arrAddress['EndUserFamilyName'], 50, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['EndUserCompany']				= str_pad($arrAddress['EndUserCompany'], 50, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['DateOfBirth']					= $arrAddress['DateOfBirth'];
		$arrBuiltRequest['Employer']					= str_pad($arrAddress['Employer'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['Occupation']					= str_pad($arrAddress['Occupation'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ABN']							= str_pad($arrAddress['ABN'], 11, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['TradingName']					= str_pad($arrAddress['TradingName'], 50, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceAddressType']			= str_pad($arrAddress['ServiceAddressType'], 3, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceAddressTypeNo']		= str_pad($arrAddress['ServiceAddressTypeNumber'], 5, "0", STR_PAD_LEFT);
		$arrBuiltRequest['ServiceAddressTypeSuffix']	= str_pad($arrAddress['ServiceAddressTypeSuffix'], 2, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceStreetNumberStart']	= str_pad($arrAddress['ServiceStreetNumberStart'], 5, "0", STR_PAD_LEFT);
		$arrBuiltRequest['ServiceStreetNumberEnd']		= str_pad($arrAddress['ServiceStreetNumberEnd'], 5, "0", STR_PAD_LEFT);
		$arrBuiltRequest['ServiceStreetNoSuffix']		= str_pad($arrAddress['ServiceStreetNumberSuffix'], 1, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceStreetName']			= str_pad($arrAddress['ServiceStreetName'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceStreetType']			= str_pad($arrAddress['ServiceStreetType'], 4, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceStreetTypeSuffix']		= str_pad($arrAddress['ServiceStreetTypeSuffix'], 2, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServicePropertyName']			= str_pad($arrAddress['ServicePropertyName'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceLocality']				= str_pad($arrAddress['ServiceLocality'], 30, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServiceState']				= str_pad($arrAddress['ServiceState'], 3, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['ServicePostcode']				= $arrAddress['ServicePostcode'];
		
		// Make a record for each of the baskets
		for ($i = 0; $i < 6; $i++)
		{
			$intFullServiceRecordSequence++;
			$arrBuiltRequest['BasketNumber']	= "00".$i;
			$arrBuiltRequest['RecordSequence']	= str_pad($intFullServiceRecordSequence, 9, "0", STR_PAD_LEFT);
			
			// Implode and append to the array for this file
			$arrFullServiceRecords[]			= implode($arrBuiltRequest);
		}
		
		// Update the database
		$this->_updFullServiceRecordSequence->Execute(Array('Value' => "$intFullServiceRecordSequence"));
		
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
		
		$intFullServiceFileSequence		= ((int)$arrResult['FullServiceFileSequence']) + 1;
		
		// Build Header Rows
		$strFullServiceFilename		= "058rslw".str_pad($intFullServiceFileSequence, 4, "0", STR_PAD_LEFT).date("Ymd").".txt";
		$strFullServiceHeaderRow 	= "01".$strFullServiceFilename;
		
		// Get list of requests to generate
		$arrResults = $this->_selGetRequests->FetchAll();
			
		$intNumFullServiceRecords	= count($this->_arrFullServiceRecords);
		
		// Build Footer Rows
		$strFullServiceFooterRow	= "99".str_pad($intNumFullServiceRecords, 7, "0", STR_PAD_LEFT);
		
		// Create Local Full Service File
		if($intNumFullServiceRecords > 0)
		{
			// Only do this if there are records to write
			$resDailyOrderFile = fopen(UNITEL_LOCAL_DAILY_ORDER_DIR.$strFullServiceFilename, "w");
			fwrite($resDailyOrderFile, $strFullServiceHeaderRow."\n");
			
			foreach($this->_arrFullServiceRecords as $strRecord)
			{
				fwrite($resDailyOrderFile, $strRecord."\n");
			}
			
			fwrite($resDailyOrderFile, $strFullServiceFooterRow."\n");
			fclose($resDailyOrderFile);
		}

		
		// Upload to FTP
		/* TODO: Uncomment this later on
		$resFTPConnection = ftp_connect(UNITEL_PROVISIONING_SERVER);
		ftp_login($resFTPConnection, UNITEL_PROVISIONING_USERNAME, UNITEL_PROVISIONING_PASSWORD);
		
		if(file_exists(UNITEL_LOCAL_DAILY_ORDER_DIR.$strFullServiceFilename))
		{
			// Upload the Daily Order File
			ftp_chdir($resFTPConnection, UNITEL_REMOTE_DAILY_ORDER_DIR);
			ftp_put($resFTPConnection, $strFullServiceFilename, UNITEL_LOCAL_DAILY_ORDER_DIR.$strFullServiceFilename);
		}
		
		ftp_close($resFTPConnection);
		*/
		
		// Update database (Request & Config tables)
		$this->_updFullServiceFileSequence->Execute(Array('Value' => "$intFullServiceFileSequence"));
		
		// Return the number of records uploaded
		return $intNumFullServiceRecords;
	} 	
	
 }

?>
