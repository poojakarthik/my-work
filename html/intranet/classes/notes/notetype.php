<?php
	
	//----------------------------------------------------------------------------//
	// notetype.php
	//----------------------------------------------------------------------------//
	/**
	 * notetype.php
	 *
	 * File containing the Note Type Class
	 *
	 * File containing the Note Type Class
	 *
	 * @file		notetype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// NoteType
	//----------------------------------------------------------------------------//
	/**
	 * NoteType
	 *
	 * A NoteType in the Database
	 *
	 * A NoteType in the Database
	 *
	 *
	 * @prefix		not
	 *
	 * @package		intranet_app
	 * @class		NoteType
	 * @extends		dataObject
	 */
	
	class NoteType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new NoteType
		 *
		 * Constructor for a new NoteType
		 *
		 * @param	Integer		$intId		The Id of the Note Type being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Note Type information and Store it ...
			$selNoteType = new StatementSelect ('NoteType', '*', 'Id = <Id>');
			$selNoteType->useObLib (TRUE);
			$selNoteType->Execute (Array ('Id' => $intId));
			$selNoteType->Fetch ($this);
			
			// Construct the object
			parent::__construct ('NoteType', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
