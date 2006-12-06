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
			$selAccount = new StatementSelect ('Contact', '*', 'Id = <Id>');
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			$selAccount->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Contact', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
