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
		
		function getAccounts ()
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
	}
	
?>
