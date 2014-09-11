<?php
	
	//----------------------------------------------------------------------------//
	// note.php
	//----------------------------------------------------------------------------//
	/**
	 * note.php
	 *
	 * File containing the Note Class
	 *
	 * File containing the Note Class
	 *
	 * @file		note.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Note
	//----------------------------------------------------------------------------//
	/**
	 * Note
	 *
	 * A Note in the Database
	 *
	 * A Note in the Database
	 *
	 *
	 * @prefix		not
	 *
	 * @package		intranet_app
	 * @class		Note
	 * @extends		dataObject
	 */
	
	class Note extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Note
		 *
		 * Constructor for a new Note
		 *
		 * @param	Integer		$intId		The Id of the Note being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Note information and Store it ...
			$selNote = new StatementSelect ('Note', '*', 'Id = <Id>');
			$selNote->useObLib (TRUE);
			$selNote->Execute (Array ('Id' => $intId));
			$selNote->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Note', $this->Pull ('Id')->getValue ());
			
			$strNote = $this->Pop ('Note')->getValue ();
			$this->Push (new dataString ('Note', nl2br (htmlentities ($strNote))));
			
			$intEmployee = $this->Pop ('Employee')->getValue ();
			
			if ($intEmployee)
			{
				$this->Push (new Employee ($intEmployee));
			}
			else
			{
				$oblarrEmployee = $this->Push (new dataArray ('Employee'));
				$oblarrEmployee->Push (new dataString ('FirstName', 'Automated'));
				$oblarrEmployee->Push (new dataString ('LastName', 'System'));
			}
		}
	}
	
?>
