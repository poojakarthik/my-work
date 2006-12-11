<?php

	//----------------------------------------------------------------------------//
	// accountgroups.php
	//----------------------------------------------------------------------------//
	/**
	 * accountgroups.php
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * @file		accountgroups.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// AccountGroups
	//----------------------------------------------------------------------------//
	/**
	 * AccountGroups
	 *
	 * Controls Searching for an existing account
	 *
	 * Controls Searching for an existing account
	 *
	 *
	 * @prefix		acs
	 *
	 * @package		intranet_app
	 * @class		AccountGroups
	 * @extends		dataObject
	 */
	
	class AccountGroups extends Search
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
			parent::__construct ('AccountGroups', 'AccountGroup', 'AccountGroup');
		}
	}
	
?>
