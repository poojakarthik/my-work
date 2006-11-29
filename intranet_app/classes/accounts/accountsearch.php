<?php

//----------------------------------------------------------------------------//
// accountsearch.php
//----------------------------------------------------------------------------//
/**
 * accountsearch.php
 *
 * Contains the Class that Controls Account Searching
 *
 * This file contains the Class that is used for Searching or Filtering Information
 * in an Account.
 *
 * @file	accountsearch.php
 * @language	PHP
 * @package	intranet_app
 * @author	Bashkim 'bash' Isai
 * @version	6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// AccountSearch
	//----------------------------------------------------------------------------//
	/**
	 * AccountSearch
	 *
	 * Controls the Maintenance for Searching for an existing account
	 *
	 * Controls the Maintenance for Searching for an existing account
	 *
	 *
	 * @prefix	acs
	 *
	 * @package	intranet_app
	 * @class	AccountSearch
	 * @extends	dataObject
	 */
	
	class AccountSearch extends Search
	{
	
		//------------------------------------------------------------------------//
		// AccountSearch
		//------------------------------------------------------------------------//
		/**
		 * AccountSearch()
		 *
		 * Constructs an Account Searching Routine
		 *
		 * Contorls the Bulk of the work required for searching for a particular account
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('AccountSearch', 'Account', 'Account');
		}
	}
	
?>
