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
 * @prefix		ftp
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
	// _selFileExists
	//------------------------------------------------------------------------//
	/**
	 * _selFileExists
	 *
	 * StatementSelect used to tell if file is already downloaded
	 *
	 * StatementSelect used to tell if file is already downloaded
	 *
	 * @type		StatementSelect
	 *
	 * @property
	 */
 	private $_selFileExists;
 	
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
 		$this->selFileExists = new StatementSelect("FileDownload", "Id", "FileName = <filename>");
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
		
		// Set private copy of arrDefine
		$this->_arrDefine = $arrDefine;

		// If the directory passed to us is just a string, convert it to an array so we can
		// handle directories uniformly
		if (is_string($this->_arrDefine['Dir']))
		{
			$this->_arrDefine['Dir'] = Array($this->_arrDefine['Dir']);
		}
		
		// Set the directory
		reset($this->_arrDefine['Dir']);
		ftp_chdir($this->_resConnection, current($this->_arrDefine['Dir']));
		
		// Get our first list of files
		$this->_arrFileListing = ParseRawlist(ftp_rawlist($this->_resConnection, "."));
		
		return TRUE;
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
		if (next($this->_arrFileListing))
		{
			$arrCurrent = current($this->_arrFileListing);
			if ($arrCurrent['Type'] == "-")
			{
				// Check that we don't already have this file
				if(!$this->_selFileExists->Execute(Array('filename' => key($this->_arrFileListing))))
				{
					// We have a usable file, so download and return the filename
					ftp_get($this->_resConnection, current($this->arrDefine['Dir']).key($this->_arrFileListing), key($this->_arrFileListing));
					return key($this->_arrFileListing);					
				}
				else
				{
					// If the file is already downloaded, call Download() again
					$this->Download($strDestination);
				}
			}
			else
			{
				// Recursively call Download() until a usable file is found
				return $this->Download($strDestination);
			}
		}
		elseif (next($this->_arrDefine['Dir']))
		{
			// Change to the next directory and call Download() again
			return $this->Download($strDestination);
		}
		else
		{
			// There are no more files to download
			return FALSE;
		}
		
 	}
 	
  	//------------------------------------------------------------------------//
	// ParseRawList
	//------------------------------------------------------------------------//
	/**
	 * ParseRawList()
	 *
	 * Parses ftp_rawlist()
	 *
	 * Parses an array containing results from ftp_rawlist()
	 *
	 * @param		array		$arrRawList			Array to parse
	 * 
	 * @return		array							Cleaned array
	 *
	 * @method
	 */
	function ParseRawlist($arrRawList)
	{
		foreach($arrRawList as $strFile)
			{
			$arrFile = array();
			$arrSplit = preg_split("[ ]", $strFile, 9);
	
			$arrFile['Type']				= $arrSplit[0]{0};
			$arrFile['Permissions']			= $arrSplit[0];
			$arrFile['Number']				= $arrSplit[1];
			$arrFile['Owner']				= $arrSplit[2];
			$arrFile['Group']				= $arrSplit[3];
			$arrFile['Size']				= $arrSplit[4];
			$arrFile['Month']				= $arrSplit[5];
			$arrFile['Day']					= $arrSplit[6];
			$arrFile['TimeYear']			= $arrSplit[7];
			$arrFile['Raw']					= $strFile;
			$arrFile['Name']				= $arrSplit[8];
			$arrCleanList[$arrFile['Name']]	= $arrFile;
		}
		return $arrCleanList;
	}
}

?>
