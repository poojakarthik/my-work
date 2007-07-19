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
	 * @return	ApplicationCollection
	 *
	 * @method
	 */
 	function __construct()
 	{
 		parent::__construct();
 		
 		// Init Modules
 		// FIXME: Use Customer Config.  For now, use all modules
 		
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
 		$selImport		= new StatementSelect("FileImport", "*", "Status = ".PROVFILE_WAITING);
 		$insResponse	= new StatementInsert("ProvisioningResponse");
 		
 		// Select all Provisioning Files waiting to be imported
 		$selImport->Execute();
 		$arrFiles = $selImport->FetchAll();
 		
 		// For each File
 		while ($arrFile = $selImport->Fetch())
 		{
	 		// Open File for Reading
	 		if (!@$ptrFile = fopen($arrFile['Location'], 'r'))
	 		{
	 			// TODO: Error!
	 		}
	 		
	 		// Read file
	 		$arrRawContent = Array();
	 		while (!feof($ptrFile))
	 		{
	 			$arrRawContent[] = fgets($ptrFile);
	 		}
	 		
	 		// Run File PreProcessor
	 		$arrFileContent = $this->arrImportModules[$arrFile['Carrier']]->PreProcess($arrFile['FileType'], $arrRawContent);
	 		
	 		// Process Lines
	 		foreach ($arrFileContent as $strLine)
	 		{
	 			// Normalise line
	 			$arrNormalised = $this->arrImportModules[$arrFile['Carrier']]->Normalise($arrFile['FileType'], $arrRawContent);
	 			
	 			// Is this a valid record?
	 			switch ($arrNormalised['Status'])
	 			{
	 				case RESPONSE_OTHER:
	 					// The line is not a response (Header or Footer)
	 					continue;
	 					break;
	 			}
	 			
		 		// Attempt to link to a Request
		 		$arrNormalised = $this->_LinkToRequest($arrNormalised);
		 		
		 		// Insert into ProvisioningResponse Table
		 		$insResponse->Execute($arrNormalised);
		 		
		 		// Update the Service (if needed)
		 		$this->_UpdateService($arrNormalised);
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
	 * @return	integer					Number of Requests Exported
	 *
	 * @method
	 */
	function Export()
	{
 	
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
	function SOAPRequest()
	{
 	
 	
 	
 	
	}
 }