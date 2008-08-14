<?php

// Provisioning Debug Mode (don't deliver files)
define("PROVISIONING_DEBUG_MODE",	FALSE);

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
 	public	$_arrExportFiles;
 	public	$_arrImportFiles;
 	public	$_arrExportModules;
 	
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
 		
	 	$this->_arrImportFiles		= Array();
	 	$this->_arrExportFiles		= Array();
	 	$this->_arrExportModules	= Array();
	 	
	 	CliEcho("\n[ INIT PROVISIONING MODULES ]\n");
 		
 		// Init Import Modules
 		CliEcho(" * IMPORT MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_PROVISIONING_INPUT));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$this->_arrImportFiles[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
 			CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'carrier')." : ".$this->_arrImportFiles[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
 		}
 		
 		// Init Export Modules
 		CliEcho("\n * EXPORT MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_PROVISIONING_OUTPUT));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
 			
 			// Link Provisioning Types to the file
 			foreach ($this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']]->GetTypes() as $intType)
 			{
 				$this->_arrExportModules[$arrModule['Carrier']][$intType]	= $this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']];
 			}
 			
 			CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'carrier')." : ".$this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
 		}
 		CliEcho('');
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
		// Get list of Provisioning Files to import
		$arrFileTypes	= Array();
		foreach ($this->_arrImportFiles as $intCarrier=>$arrCarrierFileTypes)
		{
			foreach (array_keys($arrCarrierFileTypes) as $intFileType)
			{
				$arrFileTypes[]	= $intFileType;
			}
		}
		
		// Do we have any FileTypes to import?
		if (!count($arrFileTypes))
		{
			return FALSE;
		}
 		
 		
 		// Statements
 		$arrCols				= Array();
		$arrCols['Response']	= NULL;
		$arrCols['LastUpdated']	= NULL;
		$arrCols['Status']		= NULL;
		$arrCols['Description']	= NULL;
 		$ubiRequest			= new StatementUpdateById("ProvisioningRequest", $arrCols);
 		
 		$selImport			= new StatementSelect("FileImport", "*", "FileType IN (".implode(', ', $arrFileTypes).") AND Status = ".FILE_COLLECTED);
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
 			if (!$this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']])
 			{
 				// TODO: Error
 				//CliEcho("\t* No module found");
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
	 		$arrFileContent = $this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]->PreProcess($arrRawContent);
	 		//Debug($arrFileContent);
	 		
	 		// Process Lines
	 		$intLineNumber	= 0;
	 		foreach ($arrFileContent as $strLine)
	 		{
	 			// Incremember Line Number
	 			$intLineNumber++;
	 			
	 			// Normalise line
	 			$arrNormalised = $this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]->Normalise($strLine, $intLineNumber);
	 			
	 			// Add generic fields
	 			$arrNormalised['Carrier']		= $arrFile['Carrier'];
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
	 					
	 					// Is this a duplicate?
	 					$this->_selDuplicate	= new StatementSelect(	"ProvisioningResponse",
	 																	"Id",
	 																	"Id != <Id> AND Service = <Service> AND FNN = <FNN> AND Carrier = <Carrier> AND Type = <Type> AND Description = <Description> AND EffectiveDate = <EffectiveDate> AND Status = 402");
	 					if (!$this->_selDuplicate->Execute($arrNormalised))
	 					{
		 					// Valid Response
		 					$arrNormalised['Status'] = RESPONSE_STATUS_IMPORTED;
		 					
					 		// Attempt to link to a Request
					 		if ($arrLinkedRequest = $this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]->LinkToRequest($arrNormalised))
					 		{
					 			$arrNormalised['Request']	= $arrLinkedRequest['Id'];
					 			
						 		// Update Request Table if this is the most recent Response for this Request
					 			if (strtotime($arrLinkedRequest['LastUpdated']) < strtotime($arrNormalised['EffectiveDate']))
					 			{
						 			$arrLinkedRequest['Response']		= $arrNormalised['Id'];
						 			$arrLinkedRequest['LastUpdated']	= $arrNormalised['EffectiveDate'];
						 			$arrLinkedRequest['Status']			= $arrNormalised['request_status'];
						 			$arrLinkedRequest['Description']	= $arrNormalised['Description'];
						 			if ($ubiRequest->Execute($arrLinkedRequest) === FALSE)
						 			{
						 				CliEcho("WARNING: Request Link Update failed: ".$ubiRequest->Error());
						 			}
					 			}
					 		}
					 		
				 			/*
				 			// If the File Carrier doesn't match up with Service Carrier, then mark as redundant
				 			$selServiceCarrier->Execute($arrNormalised);
							$arrServiceCarrier = $selServiceCarrier->Fetch();
							switch ($arrNormalised['Type'])
							{
								case PROVISIONING_TYPE_LOSS_FULL:
									if ($arrNormalised['Carrier'] != $arrServiceCarrier['Carrier'])
									{
										$arrNormalised['Status'] = RESPONSE_STATUS_REDUNDANT;
									}
									break;
									
								case PROVISIONING_TYPE_LOSS_PRESELECT:
									if ($arrNormalised['Carrier'] != $arrServiceCarrier['CarrierPreselect'])
									{
										$arrNormalised['Status'] = RESPONSE_STATUS_REDUNDANT;
									}
									break;
							}*/
					 		
					 		// Update the Service (if needed)
					 		//$this->_UpdateService($arrNormalised);
						}
						else
						{
							// This Response is a duplicate
		 					$arrNormalised['Status']	= RESPONSE_STATUS_DUPLICATE;
		 					$arrNormalised['Duplicate']	= $this->_selDuplicate->Fetch();
						}
	 			}
		 		
		 		// Insert into ProvisioningResponse Table
		 		if (($arrNormalised['Id'] = $insResponse->Execute($arrNormalised)) === FALSE)
		 		{
		 			Debug($insResponse->Error());
		 		}
		 		elseif ($arrNormalised['Status'] === RESPONSE_STATUS_DUPLICATE)
		 		{
		 			CliEcho("Response #{$arrNormalised['Id']} is a duplicate of {$arrNormalised['Duplicate']['Id']}");
		 		}
	 		}
	 		
	 		// Update FileImport
	 		$arrFile['Status']			= PROVFILE_COMPLETE;
	 		$arrFile['NormalisedOn']	= new MySQLFunction("NOW()");
	 		$ubiFileImport->Execute($arrFile);
 		}
 		
 		// Delete all Duplicates
 		CliEcho("\n * Deleting Duplicate Responses...", FALSE);
 		$qryQuery	= new Query();
 		if ($qryQuery->Execute("DELETE FROM ProvisioningResponse WHERE Status = ".RESPONSE_STATUS_DUPLICATE) === FALSE)
 		{
 			CliEcho("\t\t\t[ FAILED ]\n\t -- ".$qryQuery->Error()); 			
 		}
 		else
 		{
 			CliEcho("\t\t\t[   OK   ]");
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
 		$arrCols['CarrierRef']	= NULL;
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
				if ($this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']]->bolCanRunModule)
				{
					// Prepare output for this request
					$arrRequest = $this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']]->Output($arrRequest);
					
					if ($arrRequest['Status'] !== REQUEST_STATUS_EXPORTING)
					{
						CliEcho("[ FAILED ]\n\t\t- {$arrRequest['Description']}");
					}
					else
					{
						CliEcho("[   OK   ]");
					}
				}
				else
				{
					// Too early to run the module
					CliEcho("[  SKIP  ]");
				}
			}
			else
			{
				// No module
				$arrRequest['Status']	= REQUEST_STATUS_NO_MODULE;
				
				$strCarrier	= GetConstantDescription($arrRequest['Carrier'], 'carrier');
				$strType	= GetConstantDescription($arrRequest['Type'], 'provisioning_type');
				CliEcho("[ FAILED ]\n\t\t- No module ($strCarrier: $strType)");
			}
			
			// Update Request Status and Details
			$ubiRequest->Execute($arrRequest);
 		}
 		
 		// Finalise files
 		CliEcho("\n * Finalising and Delivering Files...");
 		foreach ($this->_arrExportFiles as $intCarrier=>$arrModules)
 		{
			foreach ($arrModules as $intType=>$prvModule)
			{
				if (!$arrModules[$intType]->bolExported)
				{
					$strCarrier	= GetConstantDescription($intCarrier, 'carrier');
					$strType	= $arrModules[$intType]->strDescription;
	 				CliEcho("\t + $strCarrier: $strType...\t\t\t", FALSE);
	 				$mixResult	= $arrModules[$intType]->Export();
	 				if ($mixResult['Pass'])
	 				{
	 					CliEcho("[   OK   ]");
	 					if (PROVISIONING_DEBUG_MODE)
	 					{
	 						CliEcho($mixResult['Description']);
	 					}
	 				}
	 				elseif ($mixResult['Pass'] === FALSE)
	 				{
	 					CliEcho("[ FAILED ]\n\t\tReason: {$mixResult['Description']}");
	 				}
	 				else
	 				{
	 					CliEcho("[  SKIP  ]");
	 					if (PROVISIONING_DEBUG_MODE)
	 					{
	 						CliEcho($mixResult['Description']);
	 					}
	 				}
				}
			}
 		}
 		CliEcho('');
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
	
	//------------------------------------------------------------------------//
	// _UpdateService
	//------------------------------------------------------------------------//
	/**
	 * _UpdateService()
	 *
	 * Updates a Service using a given Provisioning Response
	 *
	 * Updates a Service using a given Provisioning Response
	 * 
	 * @param	array	$arrResponse	The Provisioning Response to use
	 * 
	 *
	 * @return	array					['Message'] : Error Message
	 * 									['Pass']	: TRUE: Passed; FALSE: Failed
	 *
	 * @method
	 */
	function _UpdateService($arrResponse)
	{
		// Get Service
		$this->_selService	= new StatementSelect("Service", "*", "Id = <Service>");
		if ($this->_selService->Execute() === FALSE)
		{
			return Array('Pass' => FALSE, 'Message' => "SELECT Query Failed: ".$this->_selService->Error());
		}
		
		if ($arrService = $this->_selService->Fetch())
		{
			// Determine if we need to update
			if ($arrResponse['LineStatus'])
			{
				
			}
		}
		else
		{
			return Array('Pass' => FALSE, 'Message' => "Service '{$arrResponse['Service']}' ({$arrResponse['FNN']}) not found!");
		}
	}
 }