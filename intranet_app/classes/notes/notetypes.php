<?php

	//----------------------------------------------------------------------------//
	// notetypes.php
	//----------------------------------------------------------------------------//
	/**
	 * notetypes.php
	 *
	 * Contains the Class that Controls Note Type Listing
	 *
	 * Contains the Class that Controls Note Type Listing
	 *
	 * @file		notetypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// NoteTypes
	//----------------------------------------------------------------------------//
	/**
	 * NoteTypes
	 *
	 * Controls Listing of existing Note Types
	 *
	 * Controls Listing of existing Note Types
	 *
	 *
	 * @prefix		nts
	 *
	 * @package		intranet_app
	 * @class		NoteTypes
	 * @extends		dataCollection
	 */
	
	class NoteTypes extends dataCollection
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a list of all NoteTypes
		 *
		 * Constructs a list of all NoteTypes
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('NoteTypes', 'NoteType');
			
			// Pull all the NoteTypes and attach them
			$selNoteTypes = new StatementSelect ('NoteType', 'Id');
			$selNoteTypes->Execute (Array ());
			
			foreach ($selNoteTypes->FetchAll () as $arrNoteType)
			{
				$this->Push (new NoteType ($arrNoteType ['Id']));
			}
		}
	}
	
?>
