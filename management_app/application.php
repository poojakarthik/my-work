<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
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
 * @package		management_app
 * @author		Rich 'Waste' Davis
 * @version		7.05
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationManagement
//----------------------------------------------------------------------------//
/**
 * ApplicationManagement
 *
 * System Management Module
 *
 * System Management Module
 *
 *
 * @prefix		app
 *
 * @package		management_app
 * @class		ApplicationManagement
 */
class ApplicationManagement extends ApplicationBaseClass
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
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		$this->_arrConfig = $arrConfig;
 	}
 	
 	//------------------------------------------------------------------------//
	// LockManager
	//------------------------------------------------------------------------//
	/**
	 * LockManager()
	 *
	 * Initiates the Lock Manager Interface
	 *
	 * Initiates the Lock Manager Interface
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	function LockManager()
 	{
 		// Init NCurses Interface
 		$this->_itfLockManager = new CLIInterface("viXen Page Lock Manager v7.05");
 		
 		// Main application loop
 		$mixMenuOption = FALSE;
 		while ($mixMenuOption !== NULL)
 		{
 			// Main Menu
 			$mixMenuOption = $this->_itfLockManager->DrawMenu($this->_arrConfig['Application']['LockManager']['MainMenu'], "Please select an action to take");
 			switch ($mixMenuOption)
 			{
 				case LOCK_ADD:
 					// Add a lock
 					$this->AddLock();
 					break;
 				
 				case LOCK_VIEW:
 					// View locks
 					// $this->ViewLocks();
 					break;
 					
 				case FALSE:
 					// Exit the application
 					break 2;
 			}
 		}
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// AddLock
	//------------------------------------------------------------------------//
	/**
	 * AddLock()
	 *
	 * Provides series of prompts to create a new Page Lock
	 *
	 * Provides series of prompts to create a new Page Lock
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	 function AddLock()
	 {
	 	// Draw LockType menu
	 	$arrLockTypes = Array();
	 	$arrLockTypes[1]['Name'] = "Billing Run (Prevents Adding of new Payments)";
	 	//$arrLockTypes[FALSE]['Name'] = "Return to Main Menu";
	 	$mixLockOption = $this->_itfLockManager->DrawMenu($arrLockTypes, "Select a type of Page Lock");
		if ($mixLockOption === FALSE)
		{
			return TRUE;
		}
		
		// LockedBy menu
		$selAdmins = new StatementSelect("Employee", "Id, CONCAT(FirstName, ' ', LastName) AS FullName, PassWord", "Privileges = ".PRIVILEGE_GOD_MODE);
		$selAdmins->Execute();
		$arrAdmins = Array();
		while ($arrAdmin = $selAdmins->Fetch())
		{
			$arrAdmins[$arrAdmin['Id']]['Name']		= $arrAdmin['FullName'];
			$arrAdmins[$arrAdmin['Id']]['Password']	= $arrAdmin['PassWord'];
		}
	 	//$arrAdmins[FALSE]['Name'] = "Return to Main Menu";
	 	$mixAdmin = $this->_itfLockManager->DrawMenu($arrAdmins, "Please select your identity");
		if ($mixAdmin === FALSE)
		{
			return TRUE;
		}
		
		// Check password
		if (LOCK_AUTHENTICATE)
	 	{
	 		$intTries = 0;
	 		$mixAuth = NULL;
	 		while (sha1($mixAuth) !== $arrAdmins[$mixAdmin]['Password'])
	 		{
		 		$intTries++;
		 		$mixAuth = $this->_itfLockManager->DrawPrompt("Please enter your password", '//', TRUE);
		 		if ($mixAuth === NULL)
		 		{
		 			return TRUE;
		 		}
		 		if ($intTries == 3)
		 		{
		 			return FALSE;
		 		}
	 		}
	 	}
	 	
	 	// Get TTL
		$mixTTL = $this->_itfLockManager->DrawPrompt("Please enter the Lock's TTL (seconds)", "/^\d+$/");
		if ($mixTTL === NULL)
		{
			
		}
	 }
}










?>