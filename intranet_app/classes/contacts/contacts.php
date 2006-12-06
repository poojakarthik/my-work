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
	}
	
?>
