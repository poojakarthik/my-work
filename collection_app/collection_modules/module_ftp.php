<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_ftp
//----------------------------------------------------------------------------//
/**
 * module_ftp
 *
 * FTP Collection Module
 *
 * FTP Collection Module
 *
 * @file		module_ftp.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleFTP
//----------------------------------------------------------------------------//
/**
 * CollectionModuleFTP
 *
 * FTP Collection Module
 *
 * FTP Collection Module
 *
 *
 * @prefix		clt
 *
 * @package		vixen
 * @class		CollectionModuleFTP
 */
 class CollectionModuleFTP
 {
 	//------------------------------------------------------------------------//
	// _resConnection
	//------------------------------------------------------------------------//
	/**
	 * _resCollection
	 *
	 * FTP Connection
	 *
	 * FTP Connection
	 *
	 * @type		resource
	 *
	 * @property
	 */
	private $_resConnection;
	
 	//------------------------------------------------------------------------//
	// _arrDefine
	//------------------------------------------------------------------------//
	/**
	 * _arrDefine
	 *
	 * Collection definition
	 *
	 * Current Collection definition
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrDefine;
	
	//------------------------------------------------------------------------//
	// _arrFileListing
	//------------------------------------------------------------------------//
	/**
	 * _arrFileListing
	 *
	 * File list
	 *
	 * File list for current working directory
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrFileListing;
 	
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleFTP
	 *
	 * Constructor for CollectionModuleFTP
	 *
	 * @return		CollectionModuleFTP
	 *
	 * @method
	 */
 	function __construct()
 	{
 		// TODO
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
 	function Connect($arrDefine)
 	{
		// Connect to the remote server
		if (($this->_resConnection = ftp_connect($arrDefine["Server"])) === FALSE)
		{
			return FALSE;
		}
		
		// Login using passed details
		if (!ftp_login($this->_resConnection, $arrDefine["Username"], $arrDefine["PWord"]))
		{
			return FALSE;
		}
		
		// If the directory passed to us is just a string, convert it to an array so we can
		// handle directories uniformly
		if (is_string($arrDefine['Dir']))
		{
			$arrDefine['Dir'] = Array($arrDefine['Dir']);
		}
		
		// Set the directory
		reset($arrDefine['Dir']);
		ftp_chdir($this->_resConnection, current($arrDefine['Dir']));
		
		// Set private copy of arrDefine
		$this->_arrDefine = $arrDefine;
 	}
 	
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
 	function Disconnect()
 	{
		if ($this->_resConnection)
		{
			ftp_close($this->_resConnection);
		}
 	}
 	
  	//------------------------------------------------------------------------//
	// Download
	//------------------------------------------------------------------------//
	/**
	 * Download()
	 *
	 * Downloads next file from FTP
	 *
	 * Downloads the next file from the FTP server to the specified directory.
	 * If there is no next file, then FALSE is returned
	 *
	 * @return		mixed		String of Filename or FALSE if there is no next file
	 *
	 * @method
	 */
 	function Download($strDestination)
 	{
 		if (!$this->_resConnection)
		{
			DebugBacktrace();
			throw new Exception("Download called before Connect!");
		}
		
		// Download the next file
		
		
 	}
 }

?>
