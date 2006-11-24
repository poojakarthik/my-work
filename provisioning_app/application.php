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
$appSkel = new ApplicationProvisioning($arrConfig);

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
		
		// Init Provisioning Modules (handle both input and output)
		$this->_arrProvisioningModules[PRV_UNITEL_DAILY_STATUS_RPT]	= new ProvisioningModuleUnitel(&$this->db);
 		//$this->_arrProvisioningModules[PROV_AAPT_ALL]				= new ProvisioningModuleAAPT(&$this->db);
 		//$this->_arrProvisioningModules[PROV_OPTUS_ALL]				= new ProvisioningModuleOptus(&$this->db);
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
		$updSetLineStatus		= new StatementUpdate("Service", "FNN = <FNN>", Array('Status' => NULL));
		$updUpdateRequestsGain	= new StatementUpdate("Requests", "FNN = <FNN> AND Carrier = <Carrier> AND RequestType = ".REQUEST_GAIN, Array('GainDate' => NULL));
		$updUpdateRequestsLoss	= new StatementUpdate("Requests", "FNN = <FNN> AND Carrier = <Carrier> AND RequestType = ".REQUEST_LOSS, Array('LossDate' => NULL));
		
		// Report header
		// TODO
		
		// get list of provisioning files
		$selGetFiles->Execute();
		$arrFiles = $selGetFiles->FetchAll();
		
		// for each file
		foreach ($arrFiles as $arrFile)
		{		
			// set status of file
			$arrStatusData['Status']	= PROVFILE_READING;
			$arrStatusData['Id']		= $arrFile['Id'];
			$ubiSetFileStatus->Execute($arrStatusData);
			
			// Set current module
			$this->_prvCurrentModule = $this->_arrProvisioningModules[$arrFile['FileType']];
			
			// read in file line by line
			$resFile 		= fopen($arrFile['Location'], "r");
			$arrFileData	= Array();
			while (!feof($resFile))
			{
				// normalise this line
				$this->_prvCurrentModule->Normalise(fgets($resFile));
				
				// update requests table
				$this->_prvCurrentModule->UpdateRequests();
				
				// update service table
				$this->_prvCurrentModule->UpdateService();	
				
				// add to the log table
				$this->_prvCurrentModule->AddToLog();
			}
			fclose($resFile);

			// set status of file
			$arrStatusData['Status']	= PROVFILE_COMPLETED;
			$ubiSetFileStatus->Execute($arrStatusData);
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
					$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_UNITEL_OUT];
					break;
				case CARRIER_OPTUS:
					$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_OPTUS_ALL];
					break;
				case CARRIER_AAPT:
					$this->_prvCurrentModule = $this->_arrProvisioningModule[PRV_AAPT_ALL];
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
		//for each carrier
		//TODO!!!!
				// send request
				$this->_prvCurrentModule()->SendRequest();
	}
 }


?>
