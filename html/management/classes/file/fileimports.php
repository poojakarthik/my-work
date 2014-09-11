<?php

	//----------------------------------------------------------------------------//
	// fileimports.php
	//----------------------------------------------------------------------------//
	/**
	 * fileimports.php
	 *
	 * Searches for FileImport Information
	 *
	 * Searches for FileImport Information
	 *
	 * @file		fileimports.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */

	//----------------------------------------------------------------------------//
	// FileImports
	//----------------------------------------------------------------------------//
	/**
	 * FileImports
	 *
	 * Class for Searching for File Imports
	 *
	 * Class for Searching for File Imports
	 *
	 *
	 * @prefix		fil
	 *
	 * @package		intranet_app
	 * @class		FileImports
	 * @extends		Search
	 */
	
	class FileImports extends Search
	{
	
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Construct a new File Import Search
		 *
		 * Construct a new File Import Search
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('FileImports', 'FileImport', 'FileImport');
		}
	}
	
?>
