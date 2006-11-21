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
 * @author		Jared 'flame' Herbohn
 * @version		6.10
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
		
		/*
		$this->_arrProvisioningModule[PROV_UNTIEL_STATUS]	= new ProvisioningModuleUnitelStatus();
		$this->_arrProvisioningModule[PROV_UNTIEL_REJECT]	= new ProvisioningModuleUnitelStatus();
 		$this->_arrProvisioningModule[PROV_AAPT_ALL]		= new ProvisioningModuleAAPT();
 		$this->_arrProvisioningModule[PROV_OPTUS_ALL]		= new ProvisioningModuleOptus();
 		*/
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
		$selGetFiles		= new StatementSelect("FileImport", "*", "Status = ".PROVFILE_WAITING);
		$ubiSetFileStatus	= new StatementUpdateById("FileImport", Array('Status' => NULL));
		$selGetLineStatus	= new StatementSelect("Service", "*", "FNN = <FNN>");
		$ubiSetLineStatus	= new StatementUpdateById("Service", Array('Status' => NULL));
		
		// Report header
		
		
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
			
			// read in file line by line
			$resFile 		= fopen($arrFile['Location'], "r");
			$arrFileData	= Array();
			while (!feof($resFile))
			{
				// read the data into an indexed array
				$this->_prvCurrentModule->Add(fgets($resFile));
			}
			fclose($resFile);
			
			// for each line
			while ($arrLineData = $this->_prvCurrentModule->GetLine())
			{			
				// find service & current status
				$arrWhere['FNN'] = $arrLineData['FNN'];
				$selGetLineStatus->Execute($arrWhere);
				if(!$arrStatus = $selGetLineStatus->Fetch())
				{
					// No FNN match, Report
					// TODO
				}
				
				// work out the new status
				//TODO!!!!
				$intStatus = 0;
				
				// if status has changed
				if ($intStatus != $arrStatus['Status'])
				{				
					// set status of service
					$ubiSetLineStatus->Execute();
					$arrStatusData['Status']	= $intStatus;
					$arrStatusData['Id']		= $arrStatus['Id'];
				
					// add to status changelog
					//TODO!!!!
				}
			
			// set status of file
			$arrStatusData['Status']	= PROVFILE_COMPLETED;
			$ubiSetFileStatus->Execute($arrStatusData);
			}
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
		// set up export module objects
		//TODO!!!!
		
		// get a list of requests from the DB
		//TODO!!!!
		
		// for each request
		//TODO!!!!
		
			// build request (use module)
			//TODO!!!!
			
			// send request (use module)
			//TODO!!!!
			
			// set status of request in db
			//TODO!!!!
		
		
	}
 }


?>
