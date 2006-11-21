<?php
	
//----------------------------------------------------------------------------//
// charge.php
//----------------------------------------------------------------------------//
/**
 * charge.php
 *
 * A charge that has been applied (either to an invoice or an account)
 *
 * Provides a representation of a charge (single or recursive). Like CDR - 
 * it does not perform any Update Functions to the Charge
 *
 * @file	charge.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// Charge
	//----------------------------------------------------------------------------//
	/**
	 * Charge
	 *
	 * An account charge
	 *
	 * A charge that applies to an account (possible billed to an invoice or unbilled)
	 *
	 *
	 * @prefix	crg
	 *
	 * @package	client_app
	 * @class	Charge
	 * @extends	dataObject
	 */
	
	class Charge extends dataObject
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
		// Charge
		//------------------------------------------------------------------------//
		/**
		 * Charge()
		 *
		 * Constructs the Charge
		 *
		 * Constructs a charge that has been applied
		 *
		 * @param	AuthenticatedContact		cntContact	[Reference] The authenticated user who we are currently logged in as
		 * @param	Integer 					intAccount	The account which we wish to view
		 *
		 * @method
		 */
		
		function __construct (AuthenticatedContact &$cntContact, $intId)
		{
			// Store the AuthenticatedContact
			$this->_cntContact =& $cntContact;
			
			// Construct the parent ObLib object
			parent::__construct ("Charge");
			
			if ($this->_cntContact->Pull ("CustomerContact")->isTrue ())
			{
				// If this person is a CustomerContact, we want to Authenticate the Charge against the AccountGroup
				
				$selCharge = new StatementSelect("Charge", "*", "Id = <Id> AND AccountGroup = <AccountGroup>");
				$selCharge->Execute(
					Array(
						"Id"			=> $intId,
						"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue ()
					)
				);
			}
			else
			{
				// If this person is not a CustomerContact, we want to Authenticate the Charge against the Account
				
				$selCharge = new StatementSelect("Charge", "*", "Id = <Id> AND Account = <Account>");
				$selCharge->Execute(
					Array(
						"Id"			=> $intId,
						"Account"		=> $this->_cntContact->Pull ("Account")->getValue ()
					)
				);
			}
			
			// If the charge is not found
			// Or this person does not have access to the charge
			// Die ...
			if ($selCharge->Count () <> 1)
			{
				throw new Exception ("We did not find a charge by the ID of: " . $intId);
			}
			
			// Apply the charge information to the object
			$selCharge->useObLib (TRUE);
			$selCharge->Fetch ($this);
		}
	}
	
?>
