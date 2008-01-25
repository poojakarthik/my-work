<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		Provisioning_application
 * @author		Rich "Waste" Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationProvisioning
//----------------------------------------------------------------------------//
/**
 * ApplicationProvisioning
 *
 * Provisioning Application
 *
 * Provisioning Application
 *
 * @prefix		app
 *
 * @package		Provisioning_application
 * @class		ApplicationProvisioning
 */
 class ApplicationProvisioning extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return	ApplicationProvisioning
	 *
	 * @method
	 */
 	function __construct()
 	{
 		parent::__construct();
 		
 		// Init  Export Modules
 		$this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION]	= new ExportUnitelPreselection();
 		
 		//$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_FULL_SERVICE]			= new ExportUnitelDailyOrder();
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_PRESELECTION]			= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_BAR_SOFT]				= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_UNBAR_SOFT]			= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_ACTIVATION]			= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_DEACTIVATION]			= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_PRESELECTION_REVERSE]	= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_BAR_HARD]				= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		$this->_arrExportModules[CARRIER_UNITEL]	[REQUEST_UNBAR_HARD]			= $this->_arrExportFiles[FILE_EXPORT_UNITEL_PRESELECTION];
 		
 		// Init Import Modules
 		$this->_arrImportModules[FILE_IMPORT_UNITEL_DAILY_STATUS]	= new ImportUnitelDSC();
 	}
 	
 	//------------------------------------------------------------------------//
	// Import
	//------------------------------------------------------------------------//
	/**
	 * Import()
	 *
	 * Import provisioning files into the system
	 *
	 * Import provisioning files into the system
	 * 
	 *
	 * @return	integer					Number of Responses Imported
	 *
	 * @method
	 */
	function Import()
 	{
 		// Statements
 		$arrCols				= Array();
		$arrCols['Id']			= NULL;
		$arrCols['Response']	= NULL;
		$arrCols['LastUpdated']	= NULL;
 		$ubiRequest			= new StatementUpdateById("ProvisioningRequest", $arrCols);
 		
 		$selImport			= new StatementSelect("FileImport", "*", "Status = ".PROVFILE_WAITING);
 		$selServiceCarrier	= new StatementSelect("Service", "Carrier, CarrierPreselect", "Id = <Service>");
 		
 		$arrCols				= $this->db->FetchClean("ProvisioningResponse");
		$arrCols['ImportedOn']	= new MySQLFunction("NOW()");
 		$insResponse		= new StatementInsert("ProvisioningResponse", $arrCols);
 		
 		$arrData = Array();
 		$arrData['Status']			= PROVFILE_COMPLETE;
 		$arrData['NormalisedOn']	= new MySQLFunction("NOW()");
 		$ubiFileImport		= new StatementUpdateById("FileImport", $arrData);
 		
 		
 		// Select all Provisioning Files waiting to be imported
 		$selImport->Execute();
 		
 		// For each File
 		$arrDelinquents = Array();
 		while ($arrFile = $selImport->Fetch())
 		{
 			CliEcho("\nOpening {$arrFile['FileName']}...");
 			
 			// Is there a module?
 			if (!$this->_arrImportModules[$arrFile['FileType']])
 			{
 				// TODO: Error
 				CliEcho("\t* No module found");
 				continue;
 			}
	 			
	 		// Open File for Reading
	 		if (!@$ptrFile = fopen($arrFile['Location'], 'r'))
	 		{
	 			// TODO: Error!
 				CliEcho("\t* Unable to read file '{$arrFile['Location']}'");
	 			continue;
	 		}
	 		
	 		// Read file
	 		$arrRawContent = Array();
	 		while (!feof($ptrFile))
	 		{
	 			$arrRawContent[] = fgets($ptrFile);
	 		}
	 		
	 		// Run File PreProcessor
	 		$arrFileContent = $this->_arrImportModules[$arrFile['FileType']]->PreProcess($arrRawContent);
	 		//Debug($arrFileContent);
	 		
	 		// Process Lines
	 		foreach ($arrFileContent as $strLine)
	 		{
	 			// Normalise line
	 			$arrNormalised = $this->_arrImportModules[$arrFile['FileType']]->Normalise($strLine);
	 			
	 			// Add generic fields
	 			$arrNormalised['Carrier']		= $this->_arrImportModules[$arrFile['FileType']]->intCarrier;
	 			$arrNormalised['Raw']			= $strLine;
	 			$arrNormalised['ImportedOn']	= new MySQLFunction("NOW()");
	 			$arrNormalised['FileImport']	= $arrFile['Id'];
	 			
	 			// Is this a valid record?
	 			switch ($arrNormalised['Status'])
	 			{
	 				case RESPONSE_STATUS_BAD_OWNER:
	 					$arrDelinquents[$arrNormalised['FNN']]++;
	 					break;
	 					
	 				case RESPONSE_STATUS_CANT_NORMALISE:
	 					Debug("Unhandled Error!");
	 					break;
	 					
	 				default:
	 					$arrNormalised['Status'] = RESPONSE_STATUS_IMPORTED;
	 			}
	 			
		 		// Attempt to link to a Request
		 		if ($arrNormalised['Request'] = $this->_arrImportModules[$arrFile['FileType']]->LinkToRequest($arrNormalised))
		 		{
		 			// Update Request Table if needed
		 			$arrRequest = Array();
		 			$arrRequest['Id']			= $arrNormalised['Request'];
		 			$arrRequest['Response']		= $arrNormalised['Id'];
		 			$arrRequest['LastUpdated']	= $arrNormalised['EffectiveDate'];
		 			$ubiRequest->Execute($arrRequest);
		 		}
		 		
	 			$selServiceCarrier->Execute($arrNormalised);
				$arrServiceCarrier = $selServiceCarrier->Fetch();
				switch ($arrNormalised['Type'])
				{
					case REQUEST_LOSS_FULL:
						if ($arrNormalised['Carrier'] != $arrServiceCarrier['Carrier'])
						{
							$arrNormalised['Status'] = RESPONSE_STATUS_REDUNDANT;
						}
						break;
						
					case REQUEST_LOSS_PRESELECT:
						if ($arrNormalised['Carrier'] != $arrServiceCarrier['CarrierPreselect'])
						{
							$arrNormalised['Status'] = RESPONSE_STATUS_REDUNDANT;
						}
						break;
				}
		 		
		 		// Update the Service (if needed)
		 		//$this->_UpdateService($arrNormalised);
		 		
		 		// Insert into ProvisioningResponse Table
		 		$insResponse->Execute($arrNormalised);
		 		//Debug($insResponse->Error());
	 		}
	 		
	 		// Update FileImport
	 		$arrFile['Status']			= PROVFILE_COMPLETE;
	 		$arrFile['NormalisedOn']	= new MySQLFunction("NOW()");
	 		$ubiFileImport->Execute($arrFile);
 		}
 	}
 	
 	//------------------------------------------------------------------------//
	// Export
	//------------------------------------------------------------------------//
	/**
	 * Export()
	 *
	 * Export provisioning files from the system
	 *
	 * Export provisioning files from the system
	 * 
	 *
	 * @return	integer					Number of Requests Exported
	 *
	 * @method
	 */
	function Export()
	{
 		// Statements
 		$arrCols = Array();
 		$arrCols['Status']		= NULL;
 		$arrCols['Description']	= NULL;
 		$ubiRequest		= new StatementUpdateById("ProvisioningRequest", $arrCols);
 		$selRequests	= new StatementSelect("ProvisioningRequest", "*", "Status = ".REQUEST_STATUS_WAITING);
 		
 		CliEcho("[ PROVISIONING EXPORT ]\n");
 		
 		// Select all Requests waiting to be sent out
 		$selRequests->Execute();
 		
 		// Loop through Requests
 		CliEcho(" * Processing Requests...");
 		while ($arrRequest = $selRequests->Fetch())
 		{
			CliEcho("\t+ Exporting #{$arrRequest['Id']}...\t\t\t", FALSE);
			
			if ($this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']])
			{
				// Prepare output for this request
				$arrRequest = $this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']]->Output($arrRequest);
				
				if ($arrRequest['**Failed'])
				{
					CliEcho("[ FAILED ]\n\t\t- {$arrRequest['**Failed']}");
				}
				else
				{
					CliEcho("[   OK   ]");
				}
			}
			else
			{
				// No module
				$arrRequest['Status']	= REQUEST_STATUS_NO_MODULE;
				
				$strCarrier	= GetConstantDescription($arrRequest['Carrier'], 'Carrier');
				$strType	= GetConstantDescription($arrRequest['Type'], 'Request');
				CliEcho("[ FAILED ]\n\t\t- No module ($strCarrier: $strType)");
			}
			
			// Update Request Status and Details
			$ubiRequest->Execute($arrRequest);
 		}
 		
 		// Finalise files
 		CliEcho("\n * Finalising and Delivering Files...");
 		foreach ($this->_arrExportFiles as $intType=>$prvModule)
 		{
			if (!$prvModule->bolExported)
			{
				$strCarrier	= GetConstantDescription($prvModule->intCarrier, 'Carrier');
				$strType	= $prvModule->strDescription;
 				CliEcho("\t + $strCarrier: $strType...");
 				$prvModule->Export();
			}
 		}
	}
	
	//------------------------------------------------------------------------//
	// SOAPRequest
	//------------------------------------------------------------------------//
	/**
	 * SOAPRequest()
	 *
	 * Provision a line using SOAP
	 *
	 * Provision a line using SOAP
	 * 
	 *
	 * @return	array					['Message'] : String message describing result
	 * 									['Status']	: Boolean result of the request
	 *
	 * @method
	 */
	function SOAPRequest($intService, $intCarrier, $intRequestType)
	{
 		// Add ProvisioningRequest entry
 		
 		// Communicate with SOAP server
 		$arrResponse = $this->arrSOAPModules[$intCarrier]->Request($intService, $intRequestType);
 		
 		// Add ProvisioningResponse entry
 		
 		// If successful, update Service
 		
 		// Return message & Status
	}
 }