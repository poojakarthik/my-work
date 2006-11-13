<?php
	
//----------------------------------------------------------------------------//
// cdr.php
//----------------------------------------------------------------------------//
/**
 * cdr.php
 *
 * Call Records
 *
 * Contains information about Calls (CDR - Client Data Records)
 *
 * @file	cdr.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
	//----------------------------------------------------------------------------//
	// CDR
	//----------------------------------------------------------------------------//
	/**
	 * CDR
	 *
	 * Constructor to build a CDR
	 *
	 * Constructor to build a CDR object containing information about a call. This class
	 * does not perform any specific methods to alter a CDR
	 *
	 *
	 * @prefix	cdr
	 *
	 * @package	client_app
	 * @extends	dataObject
	 */
	
	class CDR extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _cntContact
		//------------------------------------------------------------------------//
		/**
		 * _cntContact
		 *
		 * The logged in person
		 *
		 * The Object representing the person who is currently logged in (AuthenticatedContact Object)
		 *
		 * @type	AuthenticatedContact
		 *
		 * @property
		 */
		
		private $_cntContact;
		
		//------------------------------------------------------------------------//
		// CDR
		//------------------------------------------------------------------------//
		/**
		 * CDR()
		 *
		 * Constructs a new CDR Record
		 *
		 * Constructs a new CDR Record to be read by ObLib
		 *
		 * @param	AuthenticatedContact	$cntContact	[Reference]		The person who is logged in so we 
		 * 																can perform authentication checks against the CDR
		 *																to see if they have access
		 * @method
		 */
		
		function __construct (AuthenticatedContact &$cntContact, $intId)
		{
			// Store the AuthenticatedContact that we are using
			$this->_cntContact =& $cntContact;
			
			// Construct the ObLib parent
			parent::__construct ("CDR");
			
			// If this person is a customer contact, then they have a wider range for what they have permission to view
			if ($this->_cntContact->Pull ("CustomerContact")->isTrue ())
			{
				// This person is a customer contact ... :. check the CDR against the AccountGroup
				$selCDR = new StatementSelect("CDR", "*", "Id = <Id> AND AccountGroup = <AccountGroup> AND Status = <Status>");
				$selCDR->Execute(
					Array(
						"Id"			=> $intId, 
						"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue (),
						"Status"		=> CDR_RATED
					)
				);
			}
			else
			{
				// This AuthenticatedContact is not a CustomerContact ... :. check the CDR against the account
				$selCDR = new StatementSelect("CDR", "*", "Id = <Id> AND Account = <Account> AND Status = <Status>");
				$selCDR->Execute(
					Array(
						"Id"			=> $intId, 
						"Account"		=> $this->_cntContact->Pull ("Account")->getValue (),
						"Status"		=> CDR_RATED
					)
				);
			}
			
			// If the CDR was not found
			// Or the user does not have access to the CDR
			// Or if the CDR is currently not rated
			if ($selCDR->Count () <> 1)
			{
				throw new Exception ("The CDR you requested does not exist: " . $intId);
			}
			
			// Get the CDR record and apply it to the object
			$selCDR->useObLib (TRUE);
			$selCDR->Fetch ($this);
			
			// Add an extra field which calculates the duration
			$this->Push (
				new dataDuration (
					"Duration",
					$this->PUll ("EndDatetime")->getValue () - $this->PUll ("StartDatetime")->getValue ()
				)
			);
		}
	}
	
?>
