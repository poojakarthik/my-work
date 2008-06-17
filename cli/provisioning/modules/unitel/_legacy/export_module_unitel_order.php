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
		$this->_intCarrier		= CARRIER_UNITEL;
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));
		
		/*$this->_selGetAddress					= new StatementSelect(	"Service LEFT OUTER JOIN ServiceAddress ON (ServiceAddress.Service = Service.Id)",
																		"Service.FNN AS FNN, Service.Id AS ServiceId, ServiceAddress.*",
																		"Service.Id = <Service>");*/
		
		// Make sure we're allowed to generate this file 
		$selLastGenerated = new StatementSelect("Config", "Value", "Application = 4 AND Module = 'Unitel' AND Name = 'DailyOrderLastSent'");
		$selLastGenerated->Execute();
		$arrLastGenerated = $selLastGenerated->Fetch();
		$strLastGenerated = $arrLastGenerated['Value'];
		
		$intCurDateTime		= time();
		$intLastGenerated	= strtotime("+1 day", strtotime("$strLastGenerated ".DAILY_ORDER_FILE_TIME));
		if ($intCurDateTime < $intLastGenerated)
		{
			// Too early, so ignore all requests
			$this->_bolSending = FALSE;
		}
		else
		{
			// It is late enough in the day to generate
			$this->_bolSending = TRUE;
		}
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
		// Are we generating a file?
		if (!$this->_bolSending)
		{
			// No, ignore for now (will be generated later on)
			return REQUEST_IGNORE;
		}
		
		
		$this->_selGetAddress					= new StatementSelect(	"Service LEFT OUTER JOIN ServiceAddress ON (ServiceAddress.Service = Service.Id)",
																		"Service.FNN AS FNN, Service.Id AS ServiceId, ServiceAddress.*",
																		"Service.Id = <Service>");
		
		// Get the latest Sequence Number
		$this->_selGetSequence->Execute(Array('Module' => "Unitel", 'Name' => "FullServiceRecordSequence"));
		if(!($arrResult = $this->_selGetSequence->Fetch()))
		{
			// Missing config definitions
			Debug("Missing Config");
			return FALSE;
		}
		$intFullServiceRecordSequence	= (int)$arrResult['Value'];
		
		// Add additional logging data
		$this->_arrLog['Request']		= $arrRequest['Id'];
		$this->_arrLog['Service']		= $arrRequest['Service'];
		$this->_arrLog['Type']			= $arrRequest['RequestType'];
		$this->_arrLog['Description']	= "Request Sent Successfully";
		
		// Get Service address info
		$arrWhere = Array();
		$arrWhere['Service']	= $arrRequest['Service'];
		$this->_selGetAddress->Execute($arrWhere);
		$arrAddress = $this->_selGetAddress->Fetch();
		
		// Validate the data
		$arrRequestData = $this->CleanRequest($arrAddress);
		
		foreach ($arrRequestData as $strKey=>$strField)
		{
			if ($strField === FALSE)
			{
				$this->_arrLog['Description']	= "Service Address Error: $strKey is invalid";
				return REQUEST_STATUS_REJECTED;
			}
		}
		
		// Clean the request array
		$arrBuiltRequest = Array();
		switch ($arrRequest['RequestType'])
		{
			case REQUEST_FULL_SERVICE:
				switch ($arrAddress['Residential'])
				{
					// Business
					case 0:
						$arrBuiltRequest['RecordType']					= "12";
						$arrBuiltRequest['RecordSequence']				= "000000000";
						$arrBuiltRequest['ServiceNumber']				= str_pad($arrAddress['FNN'], 17, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BasketNumber']				= "000";
						$arrBuiltRequest['CASignedDate']				= date("Ymd");
						$arrBuiltRequest['BillName']					= str_pad($arrRequestData['BillName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillAddress1']				= str_pad($arrRequestData['BillAddress1'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillAddress2']				= str_pad($arrRequestData['BillAddress2'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillLocality']				= str_pad($arrRequestData['BillLocality'], 23, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillPostcode']				= str_pad($arrRequestData['BillPostcode'], 4, "0", STR_PAD_LEFT);
						$arrBuiltRequest['EndUserTitle']				= str_pad("", 4, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['EndUserGivenName']			= str_pad("", 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['EndUserFamilyName']			= str_pad("", 50, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['EndUserCompanyName']			= str_pad($arrRequestData['EndUserCompanyName'], 50, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['DateOfBirth']					= str_pad("", 8, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['Employer']					= str_pad("", 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['Occupation']					= str_pad("", 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ABN']							= str_pad($arrRequestData['ABN'], 11, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['TradingName']					= str_pad($arrRequestData['TradingName'], 50, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceAddressType']			= str_pad($arrRequestData['ServiceAddressType'], 3, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceAddressTypeNo']		= str_pad($arrRequestData['ServiceAddressTypeNumber'], 5, "0", STR_PAD_LEFT);
						$arrBuiltRequest['ServiceAddressTypeSuffix']	= str_pad($arrRequestData['ServiceAddressTypeSuffix'], 2, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetNumberStart']	= str_pad($arrRequestData['ServiceStreetNumberStart'], 5, "0", STR_PAD_LEFT);
						$arrBuiltRequest['ServiceStreetNumberEnd']		= str_pad($arrRequestData['ServiceStreetNumberEnd'], 5, "0", STR_PAD_LEFT);
						$arrBuiltRequest['ServiceStreetNoSuffix']		= str_pad($arrRequestData['ServiceStreetNumberSuffix'], 1, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetName']			= str_pad($arrRequestData['ServiceStreetName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetType']			= str_pad($arrRequestData['ServiceStreetType'], 4, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetTypeSuffix']		= str_pad($arrRequestData['ServiceStreetTypeSuffix'], 2, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServicePropertyName']			= str_pad($arrRequestData['ServicePropertyName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceLocality']				= str_pad($arrRequestData['ServiceLocality'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceState']				= str_pad($arrRequestData['ServiceState'], 3, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServicePostcode']				= str_pad($arrRequestData['ServicePostcode'], 4, "0", STR_PAD_LEFT);
						break;
					
					// Residential
					case 1:
						$arrBuiltRequest['RecordType']					= "12";
						$arrBuiltRequest['RecordSequence']				= "000000000";
						$arrBuiltRequest['ServiceNumber']				= str_pad($arrAddress['FNN'], 17, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BasketNumber']				= "000";
						$arrBuiltRequest['CASignedDate']				= date("Ymd");
						$arrBuiltRequest['BillName']					= str_pad($arrRequestData['BillName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillAddress1']				= str_pad($arrRequestData['BillAddress1'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillAddress2']				= str_pad($arrRequestData['BillAddress2'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillLocality']				= str_pad($arrRequestData['BillLocality'], 23, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['BillPostcode']				= str_pad($arrRequestData['BillPostcode'], 4, "0", STR_PAD_LEFT);
						$arrBuiltRequest['EndUserTitle']				= str_pad($arrRequestData['EndUserTitle'], 4, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['EndUserGivenName']			= str_pad($arrRequestData['EndUserGivenName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['EndUserFamilyName']			= str_pad($arrRequestData['EndUserFamilyName'], 50, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['EndUserCompany']				= str_pad("", 50, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['DateOfBirth']					= str_pad($arrRequestData['DateOfBirth'], 8, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['Employer']					= str_pad($arrRequestData['Employer'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['Occupation']					= str_pad($arrRequestData['Occupation'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ABN']							= str_pad("", 11, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['TradingName']					= str_pad("", 50, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceAddressType']			= str_pad($arrRequestData['ServiceAddressType'], 3, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceAddressTypeNo']		= str_pad($arrRequestData['ServiceAddressTypeNumber'], 5, "0", STR_PAD_LEFT);
						$arrBuiltRequest['ServiceAddressTypeSuffix']	= str_pad($arrRequestData['ServiceAddressTypeSuffix'], 2, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetNumberStart']	= str_pad($arrRequestData['ServiceStreetNumberStart'], 5, "0", STR_PAD_LEFT);
						$arrBuiltRequest['ServiceStreetNumberEnd']		= str_pad($arrRequestData['ServiceStreetNumberEnd'], 5, "0", STR_PAD_LEFT);
						$arrBuiltRequest['ServiceStreetNoSuffix']		= str_pad($arrRequestData['ServiceStreetNumberSuffix'], 1, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetName']			= str_pad($arrRequestData['ServiceStreetName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetType']			= str_pad($arrRequestData['ServiceStreetType'], 4, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceStreetTypeSuffix']		= str_pad($arrRequestData['ServiceStreetTypeSuffix'], 2, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServicePropertyName']			= str_pad($arrRequestData['ServicePropertyName'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceLocality']				= str_pad($arrRequestData['ServiceLocality'], 30, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServiceState']				= str_pad($arrRequestData['ServiceState'], 3, " ", STR_PAD_RIGHT);
						$arrBuiltRequest['ServicePostcode']				= str_pad($arrRequestData['ServicePostcode'], 4, "0", STR_PAD_LEFT);
						break;
				}
				break;
			
			case REQUEST_FULL_SERVICE_REVERSE:
				$arrBuiltRequest['RecordType']					= "52";
				$arrBuiltRequest['RecordSequence']				= "000000000";
				$arrBuiltRequest['ServiceNumber']				= str_pad($arrAddress['FNN'], 17, " ", STR_PAD_RIGHT);
				$arrBuiltRequest['BasketNumber']				= "000";
				break;
			
			case REQUEST_VIRTUAL_PRESELECTION:
				$arrBuiltRequest['RecordType']					= "13";
				$arrBuiltRequest['RecordSequence']				= "000000000";
				$arrBuiltRequest['ServiceNumber']				= str_pad($arrAddress['FNN'], 10, " ", STR_PAD_RIGHT);
				$arrBuiltRequest['CASignedDate']				= date("Ymd");
		}
		
		foreach ($this->_arrFullServiceRecords as $arrRecord)
		{
			if ($arrRecord['ServiceNumber'] == $arrBuiltRequest['ServiceNumber'] && $arrRecord['RecordType'] == $arrBuiltRequest['RecordType'])
			{
				// This request already exists in the file - DO NOT DUPLICATE
				return REQUEST_STATUS_DUPLICATE;
			}
		}
		
		if ($arrBuiltRequest['BasketNumber'])
		{
			// Make a record for each of the baskets (001-005)
			for ($i = 1; $i < 6; $i++)
			{
				$intFullServiceRecordSequence++;
				$arrBuiltRequest['BasketNumber']	= "00".$i;
				$arrBuiltRequest['RecordSequence']	= str_pad($intFullServiceRecordSequence, 9, "0", STR_PAD_LEFT);
				
				$this->_arrFullServiceRecords[] = $arrBuiltRequest;
			}
		}
		else
		{
				$intFullServiceRecordSequence++;
				$arrBuiltRequest['RecordSequence']	= str_pad($intFullServiceRecordSequence, 9, "0", STR_PAD_LEFT);
				$this->_arrFullServiceRecords[] = $arrBuiltRequest;
		}
		
		// Update the database
		$this->_updFullServiceRecordSequence->Execute(Array('Value' => "$intFullServiceRecordSequence"), Array());
		// TODO: Update Request Table (sequence #)
		
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
		$this->_selGetSequence->Execute(Array('Module' => "Unitel", 'Name' => "FullServiceFileSequence"));
		if(!($arrResult = $this->_selGetSequence->Fetch()))
		{
			// Missing config definitions
			return FALSE;
		}
		
		$intFullServiceFileSequence		= ((int)$arrResult['Value']) + 1;
		
		// Build Header Rows
		$this->_strFile		= "058rslw".str_pad($intFullServiceFileSequence, 4, "0", STR_PAD_LEFT).date("Ymd").".txt";
		$strFullServiceHeaderRow 	= "01".$this->_strFile;
		
		// Get list of requests to generate
		$this->_selGetFullServiceRequests->Execute(Array('Carrier' => CARRIER_UNITEL));
		$arrResults = $this->_selGetFullServiceRequests->FetchAll();
		
		$intNumFullServiceRecords	= count($this->_arrFullServiceRecords);
		
		// Build Footer Rows
		$strFullServiceFooterRow	= "99".str_pad($intNumFullServiceRecords, 7, "0", STR_PAD_LEFT);
		
		// Create Local Full Service File
		if($intNumFullServiceRecords > 0)
		{
			// Only do this if there are records to write
			$resDailyOrderFile = fopen(UNITEL_LOCAL_DAILY_ORDER_DIR.$this->_strFile, "w");
			fwrite($resDailyOrderFile, $strFullServiceHeaderRow."\n");
			
			foreach($this->_arrFullServiceRecords as $arrBuiltRequest)
			{
				// Implode and append to the array for this file
				$strRecord = implode($arrBuiltRequest);
				
				fwrite($resDailyOrderFile, $strRecord."\n");
			}
			
			fwrite($resDailyOrderFile, $strFullServiceFooterRow);
			fclose($resDailyOrderFile);
		}
		else
		{
			return 0;
		}

		// Upload to FTP
		$resFTPConnection = ftp_connect(UNITEL_PROVISIONING_SERVER);
		ftp_login($resFTPConnection, UNITEL_PROVISIONING_USERNAME, UNITEL_PROVISIONING_PASSWORD);
		
		if(file_exists(UNITEL_LOCAL_DAILY_ORDER_DIR.$this->_strFile))
		{
			// Upload the Daily Order File
			ftp_chdir($resFTPConnection, UNITEL_REMOTE_DAILY_ORDER_DIR);
			ftp_put($resFTPConnection, $this->_strFile, UNITEL_LOCAL_DAILY_ORDER_DIR.$this->_strFile, FTP_ASCII);
		}
		ftp_close($resFTPConnection);
		
		// Add entry to ProvisioningExport table
	/*	$this->_insProvisioningExport = new StatementInsert();
		$arrData = Array();
		$arrData['Location']	= UNITEL_LOCAL_DAILY_ORDER_DIR.$this->_strFile;
		$arrData['Carrier']		= CARRIER_UNITEL;
		$arrData['Status']		= */
		
		// Update database (Request & Config tables)
		$this->_updFullServiceFileSequence->Execute(Array('Value' => "$intFullServiceFileSequence"), Array());
		
		// Update LastSent in config
		$arrColumns = Array();
		$arrColumns['Value'] = date("Y-m-d", time());
		$updLastGenerated = new StatementUpdate("Config", "Application = 4 AND Module = 'Unitel' AND Name = 'DailyOrderLastSent'", $arrColumns);
		$updLastGenerated->Execute($arrColumns, Array());
				
		// Return the number of records uploaded
		return $intNumFullServiceRecords;
	}
	
	
	//------------------------------------------------------------------------//
	// CleanRequest()
	//------------------------------------------------------------------------//
	/**
	 * CleanRequest()
	 *
	 * Validates and Cleans Full Service Provisioning data
	 *
	 * Validates and Cleans Full Service Provisioning data
	 *
	 * @return	array				Associative array of data to be sent off
	 *
	 * @method
	 */ 	
	function CleanRequest($arrAddress)
	{
		$arrClean = Array( 'Residential' => $arrAddress['Residential'] );
		
		// Check our mandatory fields
		$arrClean['BillName']			= (!$arrAddress['BillName']) ? FALSE : $arrAddress['BillName'];
		$arrClean['BillAddress1']		= (!$arrAddress['BillAddress1']) ? FALSE : $arrAddress['BillAddress1'];
		$arrClean['BillLocality']		= (!$arrAddress['BillLocality']) ? FALSE : $arrAddress['BillLocality'];
		$arrClean['BillPostcode']		= (!$arrAddress['BillPostcode']) ? FALSE : $arrAddress['BillPostcode'];
		$arrClean['ServiceLocality']	= (!$arrAddress['ServiceLocality']) ? FALSE : $arrAddress['ServiceLocality'];
		$arrClean['ServiceState']		= (!$arrAddress['ServiceLocality']) ? FALSE : $arrAddress['ServiceState'];
		$arrClean['ServicePostcode']	= (!$arrAddress['ServicePostcode']) ? FALSE : $arrAddress['ServicePostcode'];
		

		if ($arrAddress['Residential'] == 1)
		{
			// Residential-Specific
			// Mandatory
			$arrClean['EndUserTitle']		= (!$arrAddress['EndUserTitle']) ? FALSE : $arrAddress['EndUserTitle'];
			$arrClean['EndUserGivenName']	= (!$arrAddress['EndUserGivenName']) ? FALSE : $arrAddress['EndUserGivenName'];
			$arrClean['EndUserFamilyName']	= (!$arrAddress['EndUserFamilyName']) ? FALSE : $arrAddress['EndUserFamilyName'];
			$arrClean['DateOfBirth']		= ($arrAddress['DateOfBirth'] == "000000") ? FALSE : $arrAddress['DateOfBirth'];
			
			// Empty
			$arrClean['EndUserCompanyName']	= "";
			$arrClean['ABN']				= "";
			$arrClean['TradingName']		= "";
			
			// Optional
			$arrClean['Employer']			= $arrAddress['Employer'];
			$arrClean['Occupation']			= $arrAddress['Occupation'];
		}
		else
		{
			// Business-Specific
			// Mandatory
			$arrClean['EndUserCompanyName']	= (!$arrAddress['EndUserCompanyName']) ? FALSE : $arrAddress['EndUserCompanyName'];
			$arrClean['ABN']				= (!$arrAddress['ABN']) ? FALSE : $arrAddress['ABN'];
			
			// Empty
			$arrClean['EndUserTitle']		= "";
			$arrClean['EndUserGivenName']	= "";
			$arrClean['EndUserFamilyName']	= "";
			$arrClean['DateOfBirth']		= "";
			$arrClean['Employer']			= "";
			$arrClean['Occupation']			= "";
			
			// Optional
			$arrClean['TradingName']		= $arrAddress['TradingName'];
		}
		
		// ServiceAddress
		switch ($arrAddress['ServiceAddressType'])
		{
			// LOTs
			case "LOT":
				// Mandatory
				$arrClean['ServiceAddressTypeNumber']	=	(!$arrAddress['ServiceAddressTypeNumber']) ? FALSE : trim($arrAddress['ServiceAddressTypeNumber']);
				
				// Dependent
				if ($arrAddress['ServiceStreetName'])
				{
					$arrClean['ServiceStreetName']			= $arrAddress['ServiceStreetName'];
					$arrClean['ServiceStreetTypeSuffix']	= $arrAddress['ServiceStreetTypeSuffix'];
					$arrClean['ServicePropertyName']		= $arrAddress['ServicePropertyName'];
					$arrClean['ServiceStreetType']			= (!$arrAddress['ServiceStreetType']) ? FALSE : $arrAddress['ServiceStreetType'];
				}
				elseif ($arrAddress['ServicePropertyName'])
				{
					$arrClean['ServicePropertyName']	= $arrAddress['ServicePropertyName'];
				}
				else
				{
					$arrClean['ServiceStreetName']		= FALSE;
					$arrClean['ServicePropertyName']	= FALSE;
				}
				
				// Empty
				$arrClean['ServiceStreetNumberStart']	= "";
				$arrClean['ServiceStreetNumberEnd']		= "";
				$arrClean['ServiceStreetNumberSuffix']	= "";
				
				// Optional
				$arrClean['ServiceAddressTypeSuffix']	= $arrAddress['ServiceAddressTypeSuffix'];
				break;
			
			// Postal addresses
			case "POB":
			case "PO":
			case "BAG":
			case "CMA":
			case "CMB":
			case "PB":
			case "GPO":
			case "MS":
			case "RMD":
			case "RMB":
			case "LB":
			case "RMS":
			case "RSD":
				// Mandatory
				$arrClean['ServiceAddressTypeNumber']	=	(!$arrAddress['ServiceAddressTypeNumber']) ? FALSE : trim($arrAddress['ServiceAddressTypeNumber']);
				
				// Empty
				$arrClean['ServiceStreetNumberStart']	= "";
				$arrClean['ServiceStreetNumberEnd']		= "";
				$arrClean['ServiceStreetNumberSuffix']	= "";
				$arrClean['ServiceStreetName']			= "";
				$arrClean['ServiceStreetType']			= "";
				$arrClean['ServiceStreetTypeSuffix']	= "";
				$arrClean['ServicePropertyName']		= "";
				
				// Optional	
				$arrClean['ServiceAddressTypeSuffix']	= $arrAddress['ServiceAddressTypeSuffix'];
				break;
			
			// Standard addresses
			default:
				// Mandatory
				
				
				// Dependent
				if ($arrAddress['ServiceAddressType'])
				{
					$arrClean['ServiceAddressTypeNumber']	= (!$arrAddress['ServiceAddressTypeNumber']) ? FALSE : trim($arrAddress['ServiceAddressTypeNumber']);
					$arrClean['ServiceAddressTypeSuffix']	= $arrAddress['ServiceAddressTypeSuffix'];
				}
				else
				{
					$arrClean['ServiceAddressTypeNumber']	= "";
					$arrClean['ServiceAddressTypeSuffix']	= "";
				}
				
				if ($arrAddress['ServiceStreetName'])
				{
					$arrClean['ServiceStreetName']			= $arrAddress['ServiceStreetName'];
					$arrClean['ServiceStreetTypeSuffix']	= $arrAddress['ServiceStreetTypeSuffix'];
					$arrClean['ServicePropertyName']		= $arrAddress['ServicePropertyName'];
					$arrClean['ServiceStreetType']			= (!$arrAddress['ServiceStreetType']) ? FALSE : $arrAddress['ServiceStreetType'];
					
					if ($arrAddress['ServiceStreetNumberStart'])
					{
						$arrClean['ServiceStreetNumberStart']	= trim($arrAddress['ServiceStreetNumberStart']);
						$arrClean['ServiceStreetNumberEnd']		= (!$arrAddress['ServiceStreetNumberEnd']) ? "     " : trim($arrAddress['ServiceStreetNumberEnd']);
						$arrClean['ServiceStreetNumberSuffix']	= $arrAddress['ServiceStreetNumberSuffix'];
					}
					else
					{
						$arrClean['ServiceStreetNumberStart']	= FALSE;
					}
				}
				elseif ($arrAddress['ServicePropertyName'])
				{
					$arrClean['ServicePropertyName']	= $arrAddress['ServicePropertyName'];
				}
				else
				{
					$arrClean['ServiceStreetName']		= FALSE;
					$arrClean['ServicePropertyName']	= FALSE;
				}
				break;
		}
		
		// add optional fields
		$arrClean['BillAddress2']		= $arrAddress['BillAddress2'];
		
		// Trim all fields
		foreach ($arrClean as $strField=>$strValue)
		{
			$arrClean[$strField]	= trim($strValue);
		}

		return $arrClean;
	}
 }

?>
