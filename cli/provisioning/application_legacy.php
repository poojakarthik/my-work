<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
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
 * @author		Jared 'flame' Herbohn, Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationProvisioning
//----------------------------------------------------------------------------//
/**
 * ApplicationProvisioning
 *
 * Provisioning Module
 *
 * Provisioning Module
 *
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
	 * @return			ApplicationCollection
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		$this->_rptProvisioningReport = new Report("Provisioning Report for ".date("Y-m-d H:i:s", time()), "rdavis@ybs.net.au");
		$this->_rptProvisioningReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		// Init Provisioning Import Modules
		$this->_arrProvisioningModules[PRV_UNITEL_DAILY_STATUS_RPT]	= new ProvisioningModuleImportUnitelStatus(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_PRESELECTION_RPT]	= new ProvisioningModuleImportUnitelPreselection(&$this->db);
		//$this->_arrProvisioningModules[PRV_UNITEL_DAILY_ORDER_RPT]	= new ProvisioningModuleImportUnitelOrder(&$this->db);
 		//$this->_arrProvisioningModules[PRV_AAPT_LSD]				= new ProvisioningModuleImportAAPTLSD(&$this->db);
 		$this->_arrProvisioningModules[PROV_OPTUS_IMPORT]			= new ProvisioningModuleImportOptusStatus(&$this->db);
 		
 		// Init Provisioning Export Modules
		$this->_arrProvisioningModules[PRV_UNITEL_PRESELECTION_EXP]		= new ProvisioningModuleExportUnitelPreselection(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_DAILY_ORDER_EXP]		= new ProvisioningModuleExportUnitelOrder(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_VT_PRESELECTION_EXP]	= new ProvisioningModuleExportUnitelVoiceTalkPreselection(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_VT_DAILY_ORDER_EXP]	= new ProvisioningModuleExportUnitelVoiceTalkOrder(&$this->db);
 		//$this->_arrProvisioningModules[PRV_AAPT_EOE]					= new ProvisioningModuleExportAAPTEOE(&$this->db);
 		$this->_arrProvisioningModules[PRV_OPTUS_PRESELECTION_EXP]		= new ProvisioningModuleExportOptusPreselection(&$this->db);
 		$this->_arrProvisioningModules[PRV_OPTUS_RESTORE_EXP]			= new ProvisioningModuleExportOptusRestore(&$this->db);
 		$this->_arrProvisioningModules[PRV_OPTUS_SUSPEND_EXP]			= new ProvisioningModuleExportOptusSuspend(&$this->db);
 		$this->_arrProvisioningModules[PRV_OPTUS_BAR_EXP]				= new ProvisioningModuleExportOptusBar(&$this->db);
 		$this->_arrProvisioningModules[PRV_OPTUS_PRESELECTION_REV_EXP]	= new ProvisioningModuleExportOptusPreselectionReverse($this->db);
 		
 		$this->Framework->StartWatch();
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
	 * @return			bool
	 *
	 * @method
	 */
	function Import()
	{
		// Init Statements
		$selGetFiles			= new StatementSelect("FileImport", "*", "Status = ".PROVFILE_WAITING);
		$ubiSetFileStatus		= new StatementUpdateById("FileImport", Array('Status' => NULL));
		$selGetLineStatus		= new StatementSelect("Service", "*", "FNN = <FNN>");
		$updSetLineStatus		= new StatementUpdate("Service", "FNN = <FNN>", Array('LineStatus' => NULL));

		// Report header
		$this->_rptProvisioningReport->AddMessage(MSG_PROV_IMPORT);
		
		// get list of provisioning files
		$selGetFiles->Execute();
		$arrFiles = $selGetFiles->FetchAll();
		
		$intLinesPassed	= 0;
		$intLinesFailed = 0;
		$intFilesFailed = 0;
		$intFilesPassed = 0;
		
		// for each file
		foreach ($arrFiles as $arrFile)
		{		
			if (!file_exists($arrFile['Location']))
			{
				continue;
			}
			
			$this->_rptProvisioningReport->AddMessageVariables(MSG_IMPORT_LINE, Array("<Filename>" => $arrFile['FileName']), FALSE);
			
			// set status of file
			$arrStatusData['Status']	= PROVFILE_READING;
			$arrStatusData['Id']		= $arrFile['Id'];
			$ubiSetFileStatus->Execute($arrStatusData);
			
			// Set current module
			$this->_prvCurrentModule = $this->_arrProvisioningModules[$arrFile['FileType']];
			if (!$this->_prvCurrentModule)
			{
				// Report error: no module
				$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED.MSG_ERROR_LINE_SHALLOW, Array('<Reason>' => "No Provisioning module found!"));
				$intFilesFailed++;
				continue;
			}
			else
			{
				$this->_rptProvisioningReport->AddMessage("");
			}
			
			// read in file line by line
			$resFile 		= fopen($arrFile['Location'], "r");
			$arrFileData	= Array();
			
			$i = 0;
			$bolParseAgain = FALSE;
			$strLine = NULL;
			while (!feof($resFile))
			{
				// Check to see if we need to reparse the line
				if (!$bolParseAgain)
				{
					$i++;
					
					// Report
					$this->_rptProvisioningReport->AddMessageVariables(MSG_READING_LINE, Array('<LineNo>' => $i), FALSE);
					
					$strLine		= fgets($resFile);
				}
				$bolParseAgain = FALSE;
				
				// normalise this line
				if(($intError = $this->_prvCurrentModule->Normalise($strLine)) !== TRUE)
				{
					// Check to see if we have a continuable request
					if ($intError == CONTINUABLE_FINISHED)
					{
						$bolParseAgain = TRUE;
					}
					elseif ($intError == CONTINUABLE_CONTINUE)
					{
						$this->_rptProvisioningReport->AddMessage(MSG_OK);
						continue;
					}
					else
					{
						// By default we assume its an error, not an ignore
						$bolError = TRUE;
						
						// Report on error
						switch ($intError)
						{
							case PRV_TRAILER_RECORD:
								$bolError = FALSE;
								$strReason = "Trailer Record";
								break;
							case PRV_HEADER_RECORD:
								$bolError = FALSE;
								$strReason = "Header Record";
								break;
							case PRV_BAD_RECORD_TYPE:
								$strReason = "Unknown Record Type";
								break;
							case PRV_OLD_STATUS:
								$strReason = "Outdated Status";
								$bolError = FALSE;
								break;
							default:
								$strReason = "Unknown Error";
								break;
						}
						
						if ($bolError)
						{
							// It's an error, so give a reason
							$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => $strReason));
							$intLinesFailed++;
						}
						else
						{
							// It's an Ignore, so print this instead
							$this->_rptProvisioningReport->AddMessageVariables(MSG_IGNORE."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => $strReason));
							$intLinesPassed++;
						}
											
						continue;
					}
				}
				
				// update requests table
				if(!$this->_prvCurrentModule->UpdateRequests())
				{
					// Report on error
					$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => "Updating Request table failed"));
					
					$intLinesFailed++;
					continue;
				}
				
				// update service table
				if(($mixError = $this->_prvCurrentModule->UpdateService()) !== TRUE)
				{
					// Report on error
					if ($mixError == PRV_NO_SERVICE)
					{
						$strReason = "Cannot match to a service";
					}
					else
					{
						$strReason = "Updating Service table failed";
					}
					
					$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => $strReason));
					
					$intLinesFailed++;
					continue;
				}
				
				// add to the log table
				if(!$this->_prvCurrentModule->AddToProvisioningLog())
				{
					// Report on error
					$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => "Updating Log table failed"));
					
					$intLinesFailed++;
					continue;
				}
				
				// Email the employee who made the request
				//$this->_prvCurrentModule->EmailReport();
				
				// Add "OK" to report
				if (!$bolParseAgain)
				{
					$this->_rptProvisioningReport->AddMessage(MSG_OK);
				}
				
				$intLinesPassed++;
			}
			fclose($resFile);
			
			$intFilesPassed++;
			
			// set status of file
			$arrStatusData['Status']	= PROVFILE_COMPLETE;
			$ubiSetFileStatus->Execute($arrStatusData);
		}
		
		// Report
		$arrMessageData['<Lines>']			= $intLinesPassed + $intLinesFailed;
		$arrMessageData['<Files>']			= $intFilesPassed + $intFilesFailed;
		$arrMessageData['<LinesPassed>']	= $intLinesPassed;
		$arrMessageData['<LinesFailed>']	= $intLinesFailed;
		$arrMessageData['<FilesPassed>']	= $intFilesPassed;
		$arrMessageData['<FilesFailed>']	= $intFilesFailed;
		$arrMessageData['<Time>']			= $this->Framework->LapWatch();
		$this->_rptProvisioningReport->AddMessageVariables(MSG_IMPORT_REPORT, $arrMessageData);
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
	 * @return			bool
	 *
	 * @method
	 */
	function Export()
	{
		$this->_rptProvisioningReport->AddMessage("[ BUILDING REQUESTS ]\n");
		
		// Init prepared statements
		$selGetRequests		= new StatementSelect("Request", "*", "Status = ".REQUEST_STATUS_WAITING);
		$ubiUpdateRequest	= new StatementUpdateById("Request", Array("Status" => " "));
		
		// get a list of requests from the DB
		$arrRequests = $selGetRequests->Execute();
		
		$arrRequests = $selGetRequests->FetchAll();
		
		// for each request
		foreach($arrRequests as $arrRequest)
		{
			//$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
			
			switch ($arrRequest['Carrier'])
			{
				case CARRIER_UNITEL:
					switch ($arrRequest['RequestType'])
					{
						case REQUEST_FULL_SERVICE:
						case REQUEST_FULL_SERVICE_REVERSE:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_UNITEL_DAILY_ORDER_EXP];
							break;
							
						case REQUEST_PRESELECTION:
						case REQUEST_BAR_SOFT:
						case REQUEST_UNBAR_SOFT:
						case REQUEST_ACTIVATION:
						case REQUEST_DEACTIVATION:
						case REQUEST_PRESELECTION_REVERSE:
						case REQUEST_BAR_HARD:
						case REQUEST_UNBAR_HARD:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_UNITEL_PRESELECTION_EXP];
							break;
						
						default:
							$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
							$this->_rptProvisioningReport->AddMessage("[ FAILED ]\n\t\t- Reason: No module found!");
							continue 3;
					}
					break;
				
				case CARRIER_UNITEL_VOICETALK:
					switch ($arrRequest['RequestType'])
					{
						case REQUEST_FULL_SERVICE:
						case REQUEST_FULL_SERVICE_REVERSE:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_UNITEL_VT_DAILY_ORDER_EXP];
							break;
							
						case REQUEST_PRESELECTION:
						case REQUEST_BAR_SOFT:
						case REQUEST_UNBAR_SOFT:
						case REQUEST_ACTIVATION:
						case REQUEST_DEACTIVATION:
						case REQUEST_PRESELECTION_REVERSE:
						case REQUEST_BAR_HARD:
						case REQUEST_UNBAR_HARD:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_UNITEL_VT_PRESELECTION_EXP];
							break;
						
						default:
							$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
							$this->_rptProvisioningReport->AddMessage("[ FAILED ]\n\t\t- Reason: No module found!");
							continue 3;
					}
					break;
				
				case CARRIER_OPTUS:
					switch ($arrRequest['RequestType'])
					{
						/*case REQUEST_FULL_SERVICE:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_UNITEL_DAILY_ORDER_EXP];
							break;*/
							
						case REQUEST_PRESELECTION:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_OPTUS_PRESELECTION_EXP];
							break;
						
						case REQUEST_PRESELECTION_REVERSE:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_OPTUS_PRESELECTION_REV_EXP];
							break;
							
						case REQUEST_BAR_SOFT:
						case REQUEST_BAR_HARD:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_OPTUS_BAR_EXP];
							break;
							
						case REQUEST_UNBAR_HARD:
						case REQUEST_UNBAR_SOFT:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_OPTUS_RESTORE_EXP];
							break;
							
						case REQUEST_DEACTIVATION:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_OPTUS_SUSPEND_EXP];
							break;
							
						/*case REQUEST_BAR_HARD:
							$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_OPTUS_SUSPEND_EXP];
							break;*/
							
						default:
							$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
							$this->_rptProvisioningReport->AddMessage("[ FAILED ]\n\t\t- Reason: No module found!");
							continue 3;
					}
					break;
				
				/*case CARRIER_AAPT:
					$this->_prvCurrentModule = $this->_arrProvisioningModules[PRV_AAPT_EOE];
					break;*/
				default:
					$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
					$this->_rptProvisioningReport->AddMessage("[ FAILED ]\n\t\t- Reason: No module found!");
					continue 2;
			}
			
			
			// build request
			$mixResponse = $this->_prvCurrentModule->BuildRequest($arrRequest);
			if(!$mixResponse || is_int($mixResponse))
			{
				$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
				switch ($mixResponse)
				{
					case REQUEST_STATUS_REJECTED:
					// set status of request in db
						$this->_rptProvisioningReport->AddMessage("[ IGNORE ]\n\t\t- Reason: Request Rejected (See Log)");
						$arrRequest['Status']		= REQUEST_STATUS_REJECTED;
						break;
						
					case REQUEST_STATUS_DUPLICATE:
					// set status of request in db
						$this->_rptProvisioningReport->AddMessage("[ IGNORE ]\n\t\t- Reason: Duplicate Request");
						$arrRequest['Status']		= REQUEST_STATUS_DUPLICATE;
						break;
						
					case REQUEST_IGNORE:
						// report and continue
						$this->_rptProvisioningReport->AddMessage("[  SKIP  ]\n\t\t- Reason: Too early to generate request file");
						continue 2;
						
					case FALSE:
					default:
						// log error & set status
						$this->_rptProvisioningReport->AddMessage("[ FAILED ]\n\t\t- Reason: Request Build failed");
				}
				
				$this->_prvCurrentModule->AddToProvisioningLog();
			}
			else
			{
				// add to provisioning log
				if ($this->_prvCurrentModule->AddToProvisioningLog() !== FALSE)
				{
					// set status of request in db
					//$this->_rptProvisioningReport->AddMessage("[   OK   ]");
					$arrRequest['Status']		= REQUEST_STATUS_PENDING;
				}
				else
				{
					$this->_rptProvisioningReport->AddMessage("\t+ Building Request #{$arrRequest['Id']} ...\t\t\t\t", FALSE);
					$this->_rptProvisioningReport->AddMessage("[ FAILED ]\n\t\t- Reason: Unable to add to log");
				}
			}
			
			// Update the DB
			$ubiUpdateRequest->Execute($arrRequest);
		}
		
		$this->_rptProvisioningReport->AddMessage("\n[ SENDING REQUESTS ]\n");
		
		// Send off requests for each module
		foreach ($this->_arrProvisioningModules as $intKey=>$prvModule)
		{
			// send request (only if its an export module)
			if (method_exists($prvModule, "SendRequest"))
			{
				$this->_rptProvisioningReport->AddMessage("\t+ Sending Requests for ".str_pad(GetConstantDescription($intKey, 'ProvisioningType'), 30, " ", STR_PAD_RIGHT), FALSE);
				if (($intCount = $prvModule->SendRequest()) !== FALSE)
				{
					if ($intCount)
					{
						$this->_rptProvisioningReport->AddMessage("\t[   OK   ]");
						$intStatus = PROVISIONING_FILE_SENT;
					}
					else
					{
						$this->_rptProvisioningReport->AddMessage("\t[  SKIP  ]");
					}
				}
				else
				{
					$this->_rptProvisioningReport->AddMessage("\t[ FAILED ]");
					$intStatus = PROVISIONING_FILE_FAILED;
				}
			}
			
			// Add to ProvisioningExport
			if (method_exists($prvModule, "AddToProvisioningExport") && $intCount !== 0)
			{
				$prvModule->AddToProvisioningExport($intStatus);
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// FinaliseReport
	//------------------------------------------------------------------------//
	/**
	 * FinaliseReport()
	 *
	 * Finalises the Billing Report
	 *
	 * Adds a footer to the report and sends it off
	 * 
	 *
	 * @return		integer		No of emails sent
	 *
	 * @method
	 */
 	function FinaliseReport()
 	{
		// Add Footer
		$this->_rptProvisioningReport->AddMessageVariables("\n".MSG_HORIZONTAL_RULE.MSG_PROVISIONING_FOOTER, Array('<Time>' => $this->Framework->SplitWatch()));
		
		// Send off the report
		return $this->_rptProvisioningReport->Finish();
	}
 }


?>
