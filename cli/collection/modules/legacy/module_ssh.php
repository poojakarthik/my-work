<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_ssh
//----------------------------------------------------------------------------//
/**
 * module_ssh
 *
 * SSH Collection Module
 *
 * SSH Collection Module
 *
 * @file		module_ssh.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		7.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleSSH
//----------------------------------------------------------------------------//
/**
 * CollectionModuleSSH
 *
 * SSH Collection Module
 *
 * SSH Collection Module
 *
 *
 * @prefix		ssh
 *
 * @package		vixen
 * @class		CollectionModuleSSH
 */
 class CollectionModuleSSH
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
 		$this->_selFileExists = new StatementSelect("FileDownload", "Id", "FileName = <filename>");
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
 		// Connect to SSH2 server
 		if (!$this->_resConnection = ssh2_connect($arrDefine["Server"]))
 		{
 			return FALSE;
 		}
 		
 		// Authenticate
 		if (!ssh2_auth_password($this->_resConnection, $arrDefine["Username"], $arrDefine["PWord"]))
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
		if (current($this->_arrDefine['Dir']))
		{
			$this->_SSH2Execute("cd ".current($this->_arrDefine['Dir']));
		}
		
		// Get our first list of files
		$this->_arrFileListing = $this->DirectoryListing();
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
	 * Downloads next file from SSH2 Server
	 *
	 * Downloads the next file from the SSH2 server to the specified directory.
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
			if (!$arrCurrent['Directory'])
			{
				// Check that we don't already have this file
				if(!$this->_selFileExists->Execute(Array('filename' => key($this->_arrFileListing))))
				{
					if ($this->_selFileExists->Error())
					{

					}
					
					// Check the file size, sleep for a second, check the size again (make sure a file isnt current uploading to the server)
					$strStat = $this->_SSH2Execute($arrCurrent['FileName']);
					usleep(5000000);
					if ($strStat != $this->_SSH2Execute($arrCurrent['FileName']))
					{
						// File is still uploading to the server, so ignore it, and call Download() again
						return $this->Download($strDestination);
					}
					
					// We have a usable file, so download and return the filename
					$intMode = NULL;
					ftp_get($this->_resConnection, TEMP_DOWNLOAD_DIR.key($this->_arrFileListing), key($this->_arrFileListing), $intMode);
					ssh2_scp_recv($this->_resConnection, $arrCurrent['FileName'], TEMP_DOWNLOAD_DIR.$arrCurrent['FileName']);
					return $arrCurrent['FileName'];					
				}
				else
				{
					// If the file is already downloaded, call Download() again
					return $this->Download($strDestination);
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
			$strDir = current($this->_arrDefine['Dir']);

			// Account for nested directories
			$strDotDotSlash = "";
			if ($strDir{0} != "/")
			{
				$intDepth = count(explode("/", $strDir));
				for ($i = 0; $i <= $intDepth; $i++)
				{
					$strDotDotSlash .= "../";
				}
			}
			
			$this->_SSH2Execute("cd {$strDotDotSlash}$strDir");
			
			// Get our new list of files
			$this->_arrFileListing = $this->DirectoryListing();
			return $this->Download($strDestination);
		}
		else
		{
			// There are no more files to download
			return FALSE;
		}
		
 	}
 	
  	//------------------------------------------------------------------------//
	// DirectoryListing
	//------------------------------------------------------------------------//
	/**
	 * DirectoryListing()
	 *
	 * Get a directory listing
	 *
	 * Get a directory listing
	 * 
	 * @return		array							Directory Listing
	 *
	 * @method
	 */
	function DirectoryListing()
	{
		// Get Raw List
		$arrRawFiles	= explode("\n", $this->_SSH2Execute("ls"));
		
		// Check if files are directories or normal files
		$arrParsedFiles	= Array();
		foreach ($arrRawFiles as $strFile)
		{
			$arrParsedFiles['FileName']		= $strFile;
			$arrParsedFiles['Directory']	= $this->_SSH2IsDir($strFile);
		}
		
		// Return Parsed List
		return $arrParsedFiles;
	}

	//------------------------------------------------------------------------//
	// _SSH2Execute
	//------------------------------------------------------------------------//
	/**
	 * _SSH2Execute()
	 *
	 * Function wrapper for ssh2_exec, with blocking enabled
	 *
	 * Function wrapper for ssh2_exec, with blocking enabled
	 * 
	 * @param	string		$strCommand		The shell command to execute
	 *
	 * @return	boolean
	 *
	 * @method
	 */	
 	protected function _SSH2Execute($strCommand)
 	{
 		$ptrStream = ssh2_exec($this->_resConnection, $strCommand);
 		stream_set_blocking($ptrStream, 1);
 		$strContents = stream_get_contents($ptrStream);
 		fclose($ptrStream);
 		return $strContents;
 	}
 	
	//------------------------------------------------------------------------//
	// _SSH2IsDir
	//------------------------------------------------------------------------//
	/**
	 * _SSH2IsDir()
	 *
	 * Implements is_dir() for SSH2 connections
	 *
	 * Implements is_dir() for SSH2 connections
	 * 
	 * @param	string		$strPath		The path to examine
	 *
	 * @return	boolean
	 *
	 * @method
	 */	
 	protected function _SSH2IsDir($strPath)
 	{
 		$strOutput = $this->_SSH2Execute("stat $strPath");
 		$arrAttribs = explode("\n", $strOutput);
 		return (bool)stristr($arrAttribs[1], "directory");
 	}
}

?>
