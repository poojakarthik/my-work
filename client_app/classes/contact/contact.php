<?php

//----------------------------------------------------------------------------//
// contact.php
//----------------------------------------------------------------------------//
/**
 * contact.php
 *
 * A class for manipulating a single contact
 *
 * Provides a class for an authenticated company contact to manipulate 
 * other contacts within the company or for a contact to edit themselves only.
 *
 * @file	contact.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// Contact
	//----------------------------------------------------------------------------//
	/**
	 * Contact
	 *
	 * Gives ability to control a Contact
	 *
	 * Provides a class for an authenticated company contact to manipulate 
	 * other contacts within the company or for a contact to edit themselves only.
	 *
	 *
	 * @prefix	cnt
	 *
	 * @package	client_app
	 * @extends	dataObject
	 * @class	Contact
	 */

	class Contact extends dataObject
	{
		
		private $_cntContact;
		
		//------------------------------------------------------------------------//
		// Contact
		//------------------------------------------------------------------------//
		/**
		 * Contact()
		 *
		 * Constructing Method for the Contact Object
		 *
		 * Constructing Method for the Contact Object which controls each 
		 * individual contact
		 *
		 * @method
		 */
		
		function __construct (&$cntContact, $Id)
		{
			$this->_cntContact =& $cntContact;
			
			// Start the ObLib Object
			parent::__construct ("Contact");
			
			// If the person is Not a Company Contact and they don't wish to edit themselves, throw up
			if (!$this->_cntContact->isCustomerContact () && $Id <> $this->_cntContact->Pull ("Id")->getValue ())
			{
				throw new Exception ("You are not authorised to view this contact");
			}
			
			// Get the user from the system, matching it against the AccountGroup
			// [The AccountGroup match is moreso only for CustomerContacts because
			// we have already identified the Authentication process above for 
			// non-account group contacts.
			$selContact = new StatementSelect (
				"Contact", "*", "Id = <Id> AND AccountGroup = <AccountGroup>"
			);
			
			$selContact->useObLib (TRUE);
			$selContact->Execute (
				Array (
					"Id" => 			$Id,
					"AccountGroup" =>	$this->_cntContact->Pull ("AccountGroup")->getValue ()
				)
			);
			
			// If the User is not found/not valid, throw up
			if ($selContact->Count () <> 1)
			{
				throw new Exception ("Could not find a contact with the Id requested");
			}
			
			// If the session is valid, fetch the user and use its information
			$selContact->Fetch ($this);
		}
		
		//------------------------------------------------------------------------//
		// setPassword
		//------------------------------------------------------------------------//
		/**
		 * setPassword()
		 *
		 * Change the Password of the Contact
		 *
		 * Change the Password of the Contact
		 *
		 * @param	String	$strPassword	The new Password for this user
		 *
		 * @method
		 */
		
		public function setPassword ($strPassWord)
		{
			// SHA1 the password for the Database.
			$objPassWord = new MySQLFunction ("SHA1(<PassWord>)");
			$objPassWord->setParameters (Array ("PassWord"=>$strPassWord));
			
			$arrUpdate = Array ("PassWord" => $objPassWord);
			
			$arrWhere = Array ("Id" => $this->Pull ("Id")->getValue ());
			
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id>", $arrUpdate);
			$updUpdateStatement->Execute ($arrUpdate, $arrWhere);
		}
		
		//------------------------------------------------------------------------//
		// setProfile
		//------------------------------------------------------------------------//
		/**
		 * setProfile()
		 *
		 * Change the Contact's profile
		 *
		 * Change the Information represented in the Contact's profile
		 *
		 * @param	String	$strTitle			Mr., Mrs., Ms., Miss., Dr., Prof., Baron, Archbishop, Congressman, Dame, Archdeacon - etc
		 * @param	String	$strFirstName		The first name of the Contact
		 * @param	String	$strLastName		The last name of the Contact
		 * @param	String	$strDOB_year		The year the Contact was born
		 * @param	String	$strDOB_month		The month the Contact was born
		 * @param	String	$strDOB_day			The day the Contact was born
		 * @param	String	$strJobTitle		The Job Title of the Contact
		 * @param	String	$strEmail			The Email Address of the Contact
		 * @param	String	$strPhone			The Daytime Phone Number of the Contact
		 * @param	String	$strMobile			The Mobile Number of the Contact
		 * @param	String	$strFax				The Fax Number of the Contact
		 *
		 * @method
		 */
		
		public function setProfile ($strTitle, $strFirstName, $strLastName, $strDOB_year, $strDOB_month, $strDOB_day, $strJobTitle, $strEmail, $strPhone, $strMobile, $strFax)
		{	
			$arrUpdate = Array (
				"Title" =>			$strTitle,
				"FirstName" =>		$strFirstName,
				"LastName" =>		$strLastName,
				"DOB" =>			sprintf ("%04d", $strDOB_year) . "-" . sprintf ("%02d", $strDOB_month) . "-" . sprintf ("%02d", $strDOB_day),
				"JobTitle" =>		$strJobTitle,
				"Email" =>			$strEmail,
				"Phone" =>			$strPhone,
				"Mobile" =>			$strMobile,
				"Fax" =>			$strFax,
			);
			
			$arrWhere = Array ("Id" => $this->Pull ("Id")->getValue ());
			
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id>", $arrUpdate);
			$updUpdateStatement->Execute ($arrUpdate, $arrWhere);
		}
	}
	
?>
