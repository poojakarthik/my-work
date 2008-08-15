<?php

	//----------------------------------------------------------------------------//
	// contacts.php
	//----------------------------------------------------------------------------//
	/**
	 * contacts.php
	 *
	 * Contains the Class that Controls Contact Searching
	 *
	 * Contains the Class that Controls Contact Searching
	 *
	 * @file		contacts.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Contacts
	//----------------------------------------------------------------------------//
	/**
	 * Contacts
	 *
	 * Controls Searching for an existing Contact
	 *
	 * Controls Searching for an existing Contact
	 *
	 *
	 * @prefix		cos
	 *
	 * @package		intranet_app
	 * @class		Contacts
	 * @extends		dataObject
	 */
	
	class Contacts extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a Contact Searching Routine
		 *
		 * Constructs a Contact Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Contacts', 'Contact', 'Contact');
		}
		
		//------------------------------------------------------------------------//
		// UnarchivedUsername
		//------------------------------------------------------------------------//
		/**
		 * UnarchivedUsername()
		 *
		 * Retrieves an Unarchived User from the System based on its Username
		 *
		 * Retrieves an Unarchived User from the System based on its Username
		 *
		 * @param	String		$strUsername		The Username attempting to retrieve
		 * @return	Service
		 *
		 * @method
		 */
		
		public static function UnarchivedUsername ($strUsername)
		{
			$selUsername = new StatementSelect ('Contact', 'Id', 'UserName = <UserName> AND Archived = 0', null, 1);
			$selUsername->Execute (Array ('UserName' => $strUsername));
			
			// If it wasn't found - throw an error
			if ($selUsername->Count () <> 1)
			{
				throw new Exception ('Username not found');
			}
			
			$arrUsername = $selUsername->Fetch ();
			
			// Return the Service that was Found
			return new Contact ($arrUsername ['Id']);
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Create a new Contact
		 *
		 * Adds a new Contact to the Database
		 *
		 * @param	Account		$actAccount			The account where the Contact will be added
		 * @param	Array		$arrDetails			The details of the new Contact requested to be added
		 *
		 * @return	Integer		The Id of the Contact just inserted
		 *
		 * @method
		 */
		
		public function Add (Account $actAccount, $arrDetails)
		{
			if (!$arrDetails ['Email'])
			{
				throw new Exception ('Email cannot be Blank');
			}
			
			$arrData = Array (
				"AccountGroup"		=> $actAccount->Pull ('AccountGroup')->getValue (),
				"Account"			=> $actAccount->Pull ('Id')->getValue (),
				"Title"				=> $arrDetails ['Title'],
				"FirstName"			=> $arrDetails ['FirstName'],
				"LastName"			=> $arrDetails ['LastName'],
				"JobTitle"			=> $arrDetails ['JobTitle'],
				"DOB"				=> sprintf ("%04d", $arrDetails ['DOB:year']) . "-" .
									   sprintf ("%02d", $arrDetails ['DOB:month']) . "-" . 
									   sprintf ("%02d", $arrDetails ['DOB:day']),
				"Email"				=> $arrDetails ['Email'],
				"Phone"				=> $arrDetails ['Phone'],
				"Fax"				=> $arrDetails ['Fax'],
				"Mobile"			=> $arrDetails ['Mobile'],
				"UserName"			=> $arrDetails ['UserName'],
				"PassWord"			=> sha1 ($arrDetails ['PassWord']),
				"CustomerContact"	=> (($arrDetails ['CustomerContact'] == true) ? "1" : "0"),
				"SessionId"			=> "",
				"SessionExpire"		=> "1970-01-01 00:00:00",
				"Archived"			=> 0
			);
			
			$insContact = new StatementInsert ('Contact', $arrData);
			return $insContact->Execute ($arrData);
		}
	}
	
?>
