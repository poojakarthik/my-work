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
	 
	 
	 
 	//------------------------------------------------------------------------//
	// RebillCDRs
	//------------------------------------------------------------------------//
	/**
	 * RebillCDRs()
	 *
	 * Forces a change of ownership on already invoiced CDRs, and sets status for
	 * rerating
	 *
	 * Forces a change of ownership on already invoiced CDRs, and sets status for
	 * rerating.  Used in cases of Late/Early Change of Lessee
	 *
	 * @param	integer		$intCurrentService				The current Service Id
	 * @param	integer		$intTargetService				The target Service Id
	 * @param	string		$strStartDate					The earliest date to move CDRs across
	 * @param	string		$strEndDate			optional	The latest date to move CDRs across
	 *
	 * @return	integer								Number of CDRs moved
	 *
	 * @method
	 */
	 function RebillCDRs($intCurrentService, $intTargetService, $strStartDate, $strEndDate = NULL)
	 {	 	
		// Statements
	 	$selServiceData	= new StatementSelect("Service", "Id AS Service, Account, AccountGroup, FNN", "Id = <Id>");
	 	
	 	$arrColumns = Array();
	 	$arrColumns['Service']		= NULL;
	 	$arrColumns['Account']		= NULL;
	 	$arrColumns['AccountGroup']	= NULL;
	 	$updCDROwner	= new StatementUpdate("CDR", "Service = <Service> AND StartDatetime >= <StartDate> AND (StartDatetime <= <EndDate> OR <EndDate> <=> NULL)", $arrColumns);
	 	
	 	$strStatuses = "101, 150, 151, 199, 179, 171, 170, 156, 176";
	 	$arrColumns = Array();
	 	$arrColumns['Status']	= NULL;
	 	$updCDRStatus	= new StatementUpdate("CDR", "Service = <Service> AND StartDatetime >= <StartDate> AND (StartDatetime <= <EndDate> OR <EndDate> <=> NULL) AND Status IN ($strStatuses)", $arrColumns);
	 	
	 	$selCDRCount = new StatementSelect("CDR", "Id", "Service = <Service> AND StartDatetime >= <StartDate> AND (StartDatetime <= <EndDate> OR <EndDate> <=> NULL)");
		
	 	// Start Transaction
	 	$this->db->TransactionStart();
	 	
	 	// DEBUG
	 	$arrWhere = Array();
	 	$arrWhere['Service']	= $intCurrentService;
	 	$arrWhere['StartDate']	= $strStartDate." 00:00:00";
	 	$arrWhere['EndDate']	= ($strEndDate) ? $strEndDate." 23:59:59" : NULL;
	 	Debug("Initial CDR count on $intCurrentService: ".$selCDRCount->Execute($arrWhere));
	 	$arrWhere['Service']	= $intTargetService;
	 	Debug("Initial CDR count on $intTargetService: ".$selCDRCount->Execute($arrWhere));
	 	
	 	// Get Current and Target Service Data
	 	$selServiceData->Execute(Array('Id' => $intCurrentService));
	 	$arrCurrentService = $selServiceData->Fetch();
	 	$selServiceData->Execute(Array('Id' => $intTargetService));
	 	$arrTargetService = $selServiceData->Fetch();
	 	
	 	// Change ownership on all CDRs in the specified period
	 	$arrWhere = Array();
	 	$arrWhere['Service']	= $arrCurrentService['Service'];
	 	$arrWhere['StartDate']	= $strStartDate." 00:00:00";
	 	$arrWhere['EndDate']	= ($strEndDate) ? $strEndDate." 23:59:59" : NULL;
	 	if (($intReOwned = $updCDROwner->Execute($arrTargetService, $arrWhere)) === FALSE)
	 	{
		 	$this->db->TransactionRollback();
		 	return FALSE;
	 	}
	 	
	 	// Change Statuses on all CDRs with 150, 151, 101, 199, and special statuses
	 	$arrWhere = Array();
	 	$arrWhere['Service']	= $arrTargetService['Service'];
	 	$arrWhere['StartDate']	= $strStartDate." 00:00:00";
	 	$arrWhere['EndDate']	= ($strEndDate) ? $strEndDate." 23:59:59" : NULL;
	 	if (($intStatusChanged = $updCDRStatus->Execute(Array('Status' => CDR_NORMALISED), $arrWhere)) === FALSE)
	 	{
		 	$this->db->TransactionRollback();
		 	return FALSE;
	 	}
	 	
	 	//--------------------------------------------------------------------//
	 	// DEBUG: Print Results, Don't add Notes
	 	Debug("From\t\t: $intCurrentService");
	 	Debug("To\t\t: $intTargetService");
	 	Debug("Moved\t\t: $intReOwned");
	 	Debug("Status Changed\t: $intStatusChanged");
	 	
	 	
	 	// DEBUG
	 	$arrWhere = Array();
	 	$arrWhere['Service']	= $intCurrentService;
	 	$arrWhere['StartDate']	= $strStartDate." 00:00:00";
	 	$arrWhere['EndDate']	= ($strEndDate) ? $strEndDate." 23:59:59" : NULL;
	 	Debug("Final CDR count on $intCurrentService: ".$selCDRCount->Execute($arrWhere));
	 	$arrWhere['Service']	= $intTargetService;
	 	Debug("Final CDR count on $intTargetService: ".$selCDRCount->Execute($arrWhere));
	 	
	 	//$this->db->TransactionRollback();
	 	//return TRUE;
	 	//--------------------------------------------------------------------//
	 	
	 	// Commit Transaction
	 	$this->db->TransactionCommit();
	 	
	 	// Note Content
	 	$strContent = "Change of Lessee: All Call data for ";
	 	$strContent2 = " from ".date("d M Y", strtotime($strStartDate));
	 	$strContent .= ($strEndDate) ? " to ".date("d M Y", strtotime($strEndDate)) : " onwards";
	 	
	 	// Add a note to the Current Service
	 	$this->Framework->AddNote($strContent.$arrCurrentService['FNN'].$strContent2." has been moved to Account {$arrTargetService['Account']}, and will be included in that Account's ".date("F")." Invoice", 3, NULL, $arrCurrentService['AccountGroup'], $arrCurrentService['Account'], $intCurrentService);
	 	
	 	// Add a note to the Target Service
	 	$this->Framework->AddNote($strContent.$arrTargetService['FNN'].$strContent2." has been moved here from Account {$arrCurrentService['Account']}, and will be included in this Account's ".date("F")." Invoice", 3, NULL, $arrTargetService['AccountGroup'], $arrTargetService['Account'], $intTargetService);
	 }
	 
	 
}










?>