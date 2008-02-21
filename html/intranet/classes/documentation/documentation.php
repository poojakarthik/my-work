<?php
	
	//----------------------------------------------------------------------------//
	// documentation.php
	//----------------------------------------------------------------------------//
	/**
	 * documentation.php
	 *
	 * Access to Information for Documentation
	 *
	 * Contains the Class which gets Documentation Content from the Database
	 *
	 * @file	documentation.php
	 * @language	PHP
	 * @package	intranet_app
	 * @author	Bashkim 'bash' Isai
	 * @version	6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license	NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Documentation
	//----------------------------------------------------------------------------//
	/**
	 * Documentation
	 *
	 * Documentation Controller
	 *
	 * Retrieves information from the Database which contains data about a 
	 * particular entity or entity->field relationship.
	 *
	 *
	 * @prefix	doc
	 *
	 * @package	intranet_app
	 * @class	Documentation
	 * @extends	dataCollection
	 */
	
	class Documentation extends dataCollection
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a Documentation Container
		 *
		 * Holds Documentation about a particular entity and its files.
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('Documentation');
		}
		
		//------------------------------------------------------------------------//
		// Explain
		//------------------------------------------------------------------------//
		/**
		 * Explain()
		 *
		 * Gets Entity Information
		 *
		 * Retrieves information about a particular entity to output with the 
		 * Documentation Object
		 *
		 * @method
		 */
		
		public function Explain ($strEntity)
		{
			$this->Push (new DocumentationEntity ($strEntity));
		}
	}
	
?>
