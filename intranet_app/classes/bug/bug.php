<?php
	
	//----------------------------------------------------------------------------//
	// bug.php
	//----------------------------------------------------------------------------//
	/**
	 * bug.php
	 *
	 * File containing Bug Class
	 *
	 * File containing Bug Class
	 *
	 * @file		bug.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Bug
	//----------------------------------------------------------------------------//
	/**
	 * Bug
	 *
	 * An bug in the Database
	 *
	 * An bug in the Database
	 *
	 *
	 * @prefix	act
	 *
	 * @package		intranet_app
	 * @class		Bug
	 * @extends		dataObject
	 */
	
	class Bug extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Bug
		 *
		 * Constructor for a new Bug
		 *
		 * @param	Integer		$intId		The Id of the Bug being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the bug information and Store it ...
			$selBug = new StatementSelect ('Bug', '*', 'Id = <Id>', null, 1);
			$selBug->useObLib (TRUE);
			$selBug->Execute (Array ('Id' => $intId));
			
			if ($selBug->Count () <> 1)
			{
				throw new Exception ('Bug does not exist.');
			}
			
			$selBug->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Bug', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
