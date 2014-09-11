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
			
			// By default - Order notes by Datetime Descending
			$this->Order ('Datetime', FALSE);
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
		 * @param	Array	$arrDetails		An associative array with the Note information contained.
		 *
		 * @method
		 */
		 
		function Add ($arrDetails)
		{
			$arrNote = Array (
				'Note'			=> $arrDetails ['Note'],
				'NoteType'		=> $arrDetails ['NoteType'],
				
				'AccountGroup'	=> $arrDetails ['AccountGroup'],
				'Account'		=> $arrDetails ['Account'],
				'Service'		=> $arrDetails ['Service'],
				'Contact'		=> $arrDetails ['Contact'],
				
				'Datetime'		=> new MySQLFunction ("NOW()"),
				
				'Employee'		=> $arrDetails ['Employee']
			);
			
			$insNote = new StatementInsert ('Note', $arrNote);
			$intNote = $insNote->Execute ($arrNote);
			
			return new Note ($intNote);
		}
	}
	
?>
