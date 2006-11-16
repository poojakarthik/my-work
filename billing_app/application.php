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
 * @package		Skeleton_application
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Application entry point - create an instance of the application object
$appSkel = new ApplicationSkel($arrConfig);

// finished
echo("\n-- End of Skeleton --\n");
die();



//----------------------------------------------------------------------------//
// ApplicationSkel
//----------------------------------------------------------------------------//
/**
 * ApplicationSkel
 *
 * Skeleton Module
 *
 * Skeleton Module
 *
 *
 * @prefix		app
 *
 * @package		skeleton_application
 * @class		ApplicationSkel
 */
 class ApplicationSkel extends ApplicationBaseClass
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
	// Execute
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Execute the billing run 
	 *
	 * Generates temporary Invoices. This proccess is scheduled to run once each
	 * day at around 4am. After temporary invoices are created they can be checked
	 * and if there are no problems they can be commited. This allows testing of
	 * the billing run. Bill printing file is also produced here.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Execute()
 	{
		// Empty the temporary invoice table
		//TODO!!!!
		
		// get a list of all accounts that require billing today
		//TODO!!!!
			
			// Set status of CDR_RATED CDRs for this account to CDR_TEMP_INVOICE
			//TODO!!!!
			
			// calculate totals
			//TODO!!!
			
			// write to temporary invoice table
			//TODO!!!
			
			// build output
			//TODO!!! - LATER
			
			// write to billing file
			//TODO!!! - LATER
	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit()
	 *
	 * Commit temporary invoices 
	 *
	 * Commit temporary invoices. Once invoices have been commited they can not
	 * be revoked.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Commit()
 	{
		// copy temporary invoices to invoice table
		// INSERT INTO
		//TODO!!!!
		
		// apply invoice no. to all CDRs for this invoice
		// UPDATE CDR INNER JOIN Invoice using (Account) SET CDR.Invoice = Invoice.Id WHERE ...
		// also set the created on, due on & status
		//TODO!!!!
			
	}
	
	//------------------------------------------------------------------------//
	// Revoke
	//------------------------------------------------------------------------//
	/**
	 * Revoke()
	 *
	 * Revoke temporary invoices 
	 *
	 * Revoke all temporary invoices. Once invoices have been commited they can not
	 * be revoked.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Revoke()
 	{
		// empty temp invoice table
		//TODO!!!!
		
		// change status of CDR_TEMP_INVOICE status CDRs to CDR_RATED
		//TODO!!!!
	}
 }


?>
