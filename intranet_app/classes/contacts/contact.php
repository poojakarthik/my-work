<?php
	
	//----------------------------------------------------------------------------//
	// contact.php
	//----------------------------------------------------------------------------//
	/**
	 * contact.php
	 *
	 * File containing Contact Class
	 *
	 * File containing Contact Class
	 *
	 * @file		contact.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Contact
	//----------------------------------------------------------------------------//
	/**
	 * Contact
	 *
	 * A contact in the Database
	 *
	 * A contact in the Database
	 *
	 *
	 * @prefix	con
	 *
	 * @package		intranet_app
	 * @class		Contact
	 * @extends		dataObject
	 */
	
	class Contact extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Contact
		 *
		 * Constructor for a new Contact
		 *
		 * @param	Integer		$intId		The Id of the Contact being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the contact information and Store it ...
			$selContact = new StatementSelect ('Contact', '*', 'Id = <Id>', null, 1);
			$selContact->useObLib (TRUE);
			$selContact->Execute (Array ('Id' => $intId));
			
			if ($selContact->Count () <> 1)
			{
				throw new Exception ('Contact not found');
			}
			
			$selContact->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Contact', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// getAccounts
		//------------------------------------------------------------------------//
		/**
		 * getAccounts()
		 *
		 * Accessible Accounts
		 *
		 * Gets a list of Accounts that the Contact has access to
		 *
		 * @return	Accounts	Account listing of Accessible Accounts
		 *
		 * @method
		 */
		
		public function getAccounts ()
		{
			// Start an Account Search
			$acsAccounts = new Accounts ();
			
			// If the Contact is a Customer Contact, get all the Accounts that
			// are in their Account Group. Otherwise get only the single Account
			// they have access to
			
			if ($this->Pull ('CustomerContact')->getValue () == 1)
			{
				$acsAccounts->Constrain ('AccountGroup', 'EQUALS', $this->Pull ('AccountGroup')->getValue ());
			}
			else
			{
				$acsAccounts->Constrain ('Id', 'EQUALS', $this->Pull ('Account')->getValue ());
			}
			
			return $acsAccounts;
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Update a Contact
		 *
		 * Changes the Information about a Contact
		 *
		 * @param	Array		$arrDetails		An associative of tainted information about an account
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Update ($arrDetails)
		{
			// Check the Email Address is not Blank
			if (!$arrDetails ['Email'])
			{
				throw new Exception ('Email');
			}
			
			// Check that the DOB Date actually Exists
			if (!checkdate ($arrDetails ['DOB-month'], $arrDetails ['DOB-day'], $arrDetails ['DOB-year']))
			{
				throw new Exception ('DOB');
			}
			
			// If we are changing the UserName, check that a duplicate active Contact (Identified by the UserName) does not exist
			// This clause itself is only used for contacts that are not Archived. UserNames can be changed for Archived
			// contacts without error checking occurring
			if ($this->Pull ('Archived')->getValue () == 0)
			{
				$selUserNames = new StatementSelect ('Contact', 'Id', 'UserName = <UserName> AND Archived = 0 AND Id != <Id>', null, 1);
				$selUserNames->Execute (Array ('UserName' => $_POST ['UserName'], 'Id' => $this->Pull ('Id')->getValue ()));
				
				if ($selUserNames->Count () <> 0)
				{
					throw new Exception ('UserName');
				}
			}
			
			// Set the Data to Update
			$arrData = Array (
				"Title"				=> $arrDetails ['Title'],
				"FirstName"		=> $arrDetails ['FirstName'],
				"LastName"			=> $arrDetails ['LastName'],
				"DOB"				=> sprintf ("%04d", $arrDetails ['DOB-year']) . "-" .
									   sprintf ("%02d", $arrDetails ['DOB-month']) . "-" .
									   sprintf ("%02d", $arrDetails ['DOB-day']),
				"JobTitle"			=> $arrDetails ['JobTitle'],
				"Email"			=> $arrDetails ['Email'],
				"CustomerContact"	=> ($arrDetails ['CustomerContact'] == true) ? "1" : "0",
				"Phone"				=> $arrDetails ['Phone'],
				"Mobile"			=> $arrDetails ['Mobile'],
				"Fax"				=> $arrDetails ['Fax'],
				"UserName"			=> $arrDetails ['UserName']
			);
			
			// If the Password is set, update it (with SHA1)
			if ($arrDetails ['PassWord'])
			{
				$arrData ['PassWord'] = sha1 ($arrDetails ['PassWord']);
			}
			
			// Do the Update
			$updContact = new StatementUpdate ('Contact', 'Id = <Id>', $arrData, 1);
			$updContact->Execute ($arrData, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// ArchiveStatus
		//------------------------------------------------------------------------//
		/**
		 * ArchiveStatus()
		 *
		 * Update Contact Archive Status
		 *
		 * Update Contact Archive Status. If an Unarchive is being attempted, 
		 * this method will check that the UserName hasn't been 'snatched' by someone else.
		 * If it has been snatched, then it will throw an Exception
		 *
		 * @param	Boolean		$bolArchive		TRUE:	Archive this Contact
		 *										FALSE:	Unarchive this Contact
		 * @return	Void
		 *
		 * @method
		 */
		
		public function ArchiveStatus ($bolArchive)
		{
			// If we want to Unarchive a Contact, we have to Ensure that there isn't an unarchive (active)
			// account with the same username
			
			if ($bolArchive == FALSE)
			{
				$selContact = new StatementSelect ('Contact', 'count(*) AS length', 'UserName = <UserName> AND Archived = 0');
				$selContact->Execute (Array ('UserName' => $this->Pull ('UserName')->getValue ()));
				$arrUserNames = $selContact->Fetch ();
				
				if ($arrUserNames ['length'] <> 0)
				{
					throw new Exception ('UserName Obtained Elsewhere');
				}
			}
			
			// Set up an Archive SET clause
			$arrArchive = Array (
				"Archived"	=>	($bolArchive == TRUE) ? "1" : "0"
			);
			
			$updContact = new StatementUpdate ('Contact', 'Id = <Id>', $arrArchive, 1);
			$updContact->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>
