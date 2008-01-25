<?php

//----------------------------------------------------------------------------//
// contacts.php
//----------------------------------------------------------------------------//
/**
 * contacts.php
 *
 * A class for multiple contact manipulation
 *
 * Provides a class for an authenticated company contact to manipulate 
 * other contacts which are in their account group.
 *
 * @file	contacts.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// Contacts
	//----------------------------------------------------------------------------//
	/**
	 * Contacts
	 *
	 * Gives ability to control Contacts in an Account Group
	 *
	 * Allows Authenticated Contacts who are CustomerContacts to manage
	 * other members in the Account Group
	 *
	 *
	 * @prefix	cts
	 *
	 * @package	client_app
	 * @extends	dataCollation
	 * @class	Contacts
	 */

	class Contacts extends dataCollation
	{
		
		private $_cntContact;
		
		
		//------------------------------------------------------------------------//
		// Contacts
		//------------------------------------------------------------------------//
		/**
		 * Contacts()
		 *
		 * Constructing Method for Contacts Object
		 *
		 * Constructing Method for Contacts Object which calculates the number 
		 * of Contacts in the system and calls the parent for dataCollation
		 *
		 * @param	AuthenticatedContact $cntContact	The AuthenticatedContact who is Logged In
		 * 
		 * @method
		 */
		 
		function __construct (&$cntContact)
		{
			$this->_cntContact =& $cntContact;
			
			// If this person is not a Company Contact, throw up
			if (!$this->_cntContact->isCustomerContact ())
			{
				throw new Exception ("You are not a primary account group contact.");
			}
			
			// Count the number of Contacts in this Acocunt Group
			
			$selContactLength = new StatementSelect(
				"Contact", 
				"count(*) AS collationLength", 
				"AccountGroup = <AccountGroup>"
			);
			
			$selContactLength->Execute(
				Array(
					"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue ()
				)
			);
			
			$arrLength = $selContactLength->Fetch ();
			
			// Construct the Object with the collationLength
			parent::__construct ("Contacts", "Contact", $arrLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Retrieve a contact by Id
		 *
		 * Retrieve a contact from the Database (Identified by their Contact Id)
		 *
		 * @param	Integer		$intId		The Id of the Contact being Requested
		 *
		 * @return	Contact					The Contact that is being Queried
		 * 
		 * @method
		 */
		
		public function ItemId ($intId)
		{
			return new Contact ($this->_cntContact, $intId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Retrieve a contact by Index
		 *
		 * Retrieve a contact from the Database 
		 * (Identified by an Index) in no particular order, but ordered to prevent
		 * ambiguity
		 *
		 * @param	Integer		$intId		The Id of the Contact being Requested
		 *
		 * @return	Contact					The Contact that is being Queried
		 * 
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			// Pull a Contact from the System using Limit
			$selCDRId = new StatementSelect (
				"Contact", 
				"Id", 
				"AccountGroup = <AccountGroup>", 
				null, 
				$intIndex . ", 1"
			);
			
			$selCDRId->Execute(
				Array( 
					"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue ()
				)
			);
			
			// If no contact was found - return null
			if (!$arrCDRId = $selCDRId->Fetch ())
			{
				return null;
			}
			
			// If we're here - a contact has been found, so return it as an object.
			
			return $this->ItemId ($arrCDRId ['Id']);
		}
	}
	
?>
