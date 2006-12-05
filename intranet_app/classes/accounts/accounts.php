<?php

	//----------------------------------------------------------------------------//
	// accounts.php
	//----------------------------------------------------------------------------//
	/**
	 * accounts.php
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * @file		accounts.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Accounts
	//----------------------------------------------------------------------------//
	/**
	 * Accounts
	 *
	 * Controls Searching for an existing account
	 *
	 * Controls Searching for an existing account
	 *
	 *
	 * @prefix		acs
	 *
	 * @package		intranet_app
	 * @class		Accounts
	 * @extends		dataObject
	 */
	
	class Accounts extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Account Searching Routine
		 *
		 * Constructs an Account Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Accounts', 'Account', 'Account');
		}
	}
	
?>
