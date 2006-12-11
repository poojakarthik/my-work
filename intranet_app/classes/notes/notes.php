<?php

	//----------------------------------------------------------------------------//
	// notes.php
	//----------------------------------------------------------------------------//
	/**
	 * notes.php
	 *
	 * Contains the Class that Controls Note Searching
	 *
	 * Contains the Class that Controls Note Searching
	 *
	 * @file		notes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Notes
	//----------------------------------------------------------------------------//
	/**
	 * Notes
	 *
	 * Controls Searching for an existing Note
	 *
	 * Controls Searching for an existing Note
	 *
	 *
	 * @prefix		nos
	 *
	 * @package		intranet_app
	 * @class		Notes
	 * @extends		Search
	 */
	
	class Notes extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Note Searching Routine
		 *
		 * Constructs an Note Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Notes', 'Note', 'Note');
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Add a new Note to the Database
		 *
		 * Add a new Note to the Database
		 *
		 *
		 * @param	Array	$arrNote		An associative array with the Note information contained.
		 *
		 * @method
		 */
		 
		function Add ($arrNote)
		{
			$insNote = new StatementInsert ('Note');
			$intNote = $insNote->Execute ($arrNote);
			
			return new Note ($intNote);
		}
	}
	
?>
