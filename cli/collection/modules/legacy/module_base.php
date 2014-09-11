<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_base
//----------------------------------------------------------------------------//
/**
 * module_base
 *
 * Base Collection Module
 *
 * Base Collection Module
 *
 * @file		module_base.php
 * @language	PHP
 * @package		collection
 * @author		Rich Davis
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleBase
//----------------------------------------------------------------------------//
/**
 * CollectionModuleBase
 *
 * Base Collection Module
 *
 * Base Collection Module
 *
 *
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleBase
 */
 abstract class CollectionModuleBase extends CarrierModule
 {
 	protected	$_selFileExists;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleBase
	 *
	 * Constructor for CollectionModuleBase
	 *
	 * @return		CollectionModuleBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier, MODULE_TYPE_COLLECTION);
 		
 		$this->_selFileDownloaded	= new StatementSelect("FileDownload", "Id", "FileName = <FileName>");
 		$this->_selFileImported		= new StatementSelect("FileImport", "Id", "FileName = <FileName>");
 	}
 	
 	//------------------------------------------------------------------------//
	// Connect
	//------------------------------------------------------------------------//
	/**
	 * Connect()
	 *
	 * Connects to FTP server
	 *
	 * Connects to FTP server using passed definition
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function Connect();
 	
  	//------------------------------------------------------------------------//
	// Disconnect
	//------------------------------------------------------------------------//
	/**
	 * Disconnect()
	 *
	 * Disconnect from FTP server
	 *
	 * Disconnect from FTP server
	 *
	 * @method
	 */
 	abstract function Disconnect();
 	
  	//------------------------------------------------------------------------//
	// Download
	//------------------------------------------------------------------------//
	/**
	 * Download()
	 *
	 * Downloads next file from the Source
	 *
	 * Downloads next file from the Source, returning the FileName or FALSE if no more files
	 *
	 * @return		mixed		String of Filename or FALSE if there is no next file
	 *
	 * @method
	 */
 	abstract function Download();
}

?>
