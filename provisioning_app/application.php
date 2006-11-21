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
		// get list of provisioning files
		//TODO!!!!
		
		// for each file
		//TODO!!!!
		
			// set status of file
			//TODO!!!!
			
			// read in file line by line
			//TODO!!!!
			
			// for each line
			//TODO!!!!
			
				// find service & current status
				//TODO!!!!
			
				// work out the new status
				//TODO!!!!
				
				// if status has changed
				//TODO!!!!
				
					// set status of service
					//TODO!!!!
				
					// add to status changelog
					//TODO!!!!
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
