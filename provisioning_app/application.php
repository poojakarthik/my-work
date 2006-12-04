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

echo "<pre>";

// Application entry point - create an instance of the application object
$appProvisioining = new ApplicationProvisioning($arrConfig);

$appProvisioining->Import();
//$appProvisioining->Export();

$appProvisioining->FinaliseReport();

// finished
echo("\n-- End of Provisioning --\n");
echo "</pre>";
die();



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
		
		$this->_rptProvisioningReport = new Report("Provisioning Report for ".date("Y-m-d H:i:s", time()), "rich@voiptelsystems.com.au");
		$this->_rptProvisioningReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		// Init Provisioning Import Modules
		$this->_arrProvisioningModules[PRV_UNITEL_DAILY_STATUS_RPT]	= new ProvisioningModuleImportUnitelStatus(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_PRESELECTION_RPT]	= new ProvisioningModuleImportUnitelPreselection(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_DAILY_ORDER_RPT]	= new ProvisioningModuleImportUnitelOrder(&$this->db);
 		$this->_arrProvisioningModules[PRV_AAPT_LSD]				= new ProvisioningModuleImportAAPTLSD(&$this->db);
 		//$this->_arrProvisioningModules[PROV_OPTUS_IMPORT]			= new ProvisioningModuleOptus(&$this->db);
 		
 		// Init Provisioning Export Modules
		$this->_arrProvisioningModules[PRV_UNITEL_PRESELECTION_EXP]	= new ProvisioningModuleExportUnitelPreselection(&$this->db);
		$this->_arrProvisioningModules[PRV_UNITEL_DAILY_ORDER_EXP]	= new ProvisioningModuleExportUnitelOrder(&$this->db);
 		$this->_arrProvisioningModules[PRV_AAPT_EOE]				= new ProvisioningModuleExportAAPTEOE(&$this->db);
 		//$this->_arrProvisioningModules[PROV_OPTUS_EXPORT]			= new ProvisioningModuleOptus(&$this->db);
 		
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
		$selGetFiles			= new StatementSelect("FileImport", "*", "Status = ".CDRFILE_WAITING." AND FileType >= ".PRV_IMPORT_RANGE_MIN." AND FileType <= ".PRV_IMPORT_RANGE_MAX);
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
			while (!feof($resFile))
			{
				$i++;
				
				// Report
				$this->_rptProvisioningReport->AddMessageVariables(MSG_READING_LINE, Array('<LineNo>' => $i));
				
				// normalise this line
				if(($intError = $this->_prvCurrentModule->Normalise(fgets($resFile))) !== TRUE)
				{
					// Report on error
					switch ($intError)
					{
						case PRV_TRAILER_RECORD:
							$strReason = "Trailer Record";
							break;
						case PRV_HEADER_RECORD:
							$strReason = "Header Record";
							break;
						case PRV_BAD_RECORD_TYPE:
							$strReason = "Unkown Record Type";
							break;
						default:
							$strReason = "Unknown Error";
							break;
					}
					
					$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => $strReason));
					
					$intLinesFailed++;
					continue;
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
				if(!$this->_prvCurrentModule->AddToLog())
				{
					// Report on error
					$this->_rptProvisioningReport->AddMessageVariables(MSG_FAILED."\n".MSG_ERROR_LINE_DEEP, Array('<Reason>' => "Updating Log table failed"));
					
					$intLinesFailed++;
					continue;
				}
				
				// Add "OK" to report
				$this->_rptProvisioningReport->AddMessage(MSG_OK);
				
				$intLinesPassed++;
			}
			fclose($resFile);
			
			$intFilesPassed++;
			
			// set status of file
			$arrStatusData['Status']	= CDRFILE_WAITING;
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
		// Init prepared statements
		$selGetRequests		= new StatementSelect("Request", "*", "Status = ".REQUEST_WAITING);
		$ubiUpdateRequest	= new StatementUpdateById("Request", Array("Status" => " "));
		
		// get a list of requests from the DB
		$arrRequests = $selGetRequests->Execute();
		
		// for each request
		foreach($arrRequests as $arrRequest)
		{
			switch ($arrRequest['Carrier'])
			{
				case CARRIER_UNITEL:
					switch ($arrRequest['RequestType'])
					{
						case REQUEST_FULL_SERVICE:
							$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_UNITEL_DAILY_ORDER_EXP];
							break;
						default:
							$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_UNITEL_PRESELECTION_EXP];
							break;
					}
					break;
				case CARRIER_OPTUS:
					//$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_OPTUS_ALL];
					break;
				case CARRIER_AAPT:
					$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_AAPT_EOE];
					break;
				default:
					// There is a problem, Report
			}
			
			// build request
			if(!$this->_prvCurrentModule()->BuildRequest($arrRequest))
			{
				//TODO!!!! - log error & set status
			}
			else
			{
				// set status of request in db
				$arrRequest['Status']		= REQUEST_SENT;
			}
			$ubiUpdateRequest->Execute($arrRequest);
		}

		// Send off requests for each module		
		foreach ($this->_arrProvisioningModule as $prvModule)
		{
			// send request
			$prvModule->SendRequest();
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
