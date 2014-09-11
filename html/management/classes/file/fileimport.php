<?php
	
	//----------------------------------------------------------------------------//
	// fileimport.php
	//----------------------------------------------------------------------------//
	/**
	 * fileimport.php
	 *
	 * File for FileImport Class
	 *
	 * File for FileImport Class
	 *
	 * @file		fileimport.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// FileImport
	//----------------------------------------------------------------------------//
	/**
	 * FileImport
	 *
	 * Contains information reguarding File Imports
	 *
	 * Contains information reguarding File Imports
	 *
	 *
	 * @prefix	fim
	 *
	 * @package		intranet_app
	 * @class		FileImport
	 * @extends		dataObject
	 */
	
	class FileImport extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs FileImport information from the Database
		 *
		 * Constructs FileImport information from the Database
		 *
		 * @param	Integer		$intId		The Id of the FileImport being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('FileImport', $intId);
			
			// Pull the FileImport information and attach it to the Object
			$selFileImport = new StatementSelect ('FileImport', '*', 'Id = <Id>');
			$selFileImport->useObLib (TRUE);
			$selFileImport->Execute (Array ('Id' => $intId));
			$selFileImport->Fetch ($this);
		}
	}
	
?>
