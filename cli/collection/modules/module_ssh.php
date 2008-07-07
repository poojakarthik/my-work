<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
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
 * @package		collection
 * @author		Rich Davis
 * @version		8.07
 * @copyright	2008 VOIPTEL Pty Ltd
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
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleSSH
 */
 class CollectionModuleSSH extends CollectionModuleBase
 {
	private $_resConnection;
 	
	//public $intBaseCarrier			= CARRIER_UNITEL;
	public $intBaseFileType			= FILE_RESOURCE_SSH2;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleSSH
	 *
	 * Constructor for CollectionModuleSSH
	 *
	 * @return		CollectionModuleSSH
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Host']			['Default']		= '';
 		$this->_arrModuleConfig['Host']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Host']			['Description']	= "SSH Server to connect to";
 		
 		$this->_arrModuleConfig['Username']		['Default']		= '';
 		$this->_arrModuleConfig['Username']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Username']		['Description']	= "SSH Username";
 		
 		$this->_arrModuleConfig['Password']		['Default']		= '';
 		$this->_arrModuleConfig['Password']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']		['Description']	= "SSH Password";
 		
 		$this->_arrModuleConfig['FileDefine']	['Default']		= Array();
 		$this->_arrModuleConfig['FileDefine']	['Type']		= DATA_TYPE_ARRAY;
 		$this->_arrModuleConfig['FileDefine']	['Description']	= "Definitions for where to download files from";
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
 	function Connect()
 	{
		$strHost		= $this->GetConfigField('Host');
		$strUsername	= $this->GetConfigField('Username');
		$strPassword	= $this->GetConfigField('Password');
		
 		// Connect to SSH2 server
 		if ($this->_resConnection = ssh2_connect($strHost))
 		{
	 		// Authenticate
	 		if (ssh2_auth_password($this->_resConnection, $strUsername, $strPassword))
	 		{
				// Retrieve full file listing
				$this->_arrDownloadPaths	= $this->_GetDownloadPaths();
				reset($this->_arrDownloadPaths);
	 		}
	 		else
	 		{
	 			return "Invalid username/password combination";
	 		}
 		}
 		else
 		{
 			return "Unable to connect to SSH2 server";
 		}
 		
 		// All good, return TRUE
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
		unset($this->_resConnection);
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
			return "Download() called before Connect()";
		}
		
		// Get the Current path element
		if (!($arrCurrentFile = current($this->_arrDownloadPaths)))
		{
			// No files left, return FALSE
			return FALSE;
		}
		else
		{
			// Advance the arrDownloadPaths internal pointer
			next($this->_arrDownloadPaths);
			
			// Calculate Local Download Path
			$arrCurrentFile['LocalPath']	= $strDestination.basename($arrCurrentFile['RemotePath']);
			
			// Attempt to download this file
			if (ssh2_scp_recv($this->_resConnection, $arrCurrentFile['RemotePath'], $arrCurrentFile['LocalPath']))
			{
				return $arrCurrentFile;
			}
			else
			{
				return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}'";
			}
		}
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
 	
  	//------------------------------------------------------------------------//
	// _GetDownloadPaths
	//------------------------------------------------------------------------//
	/**
	 * _GetDownloadPaths()
	 *
	 * Gets a full list of all files to download
	 *
	 * Gets a full list of all files to download
	 * 
	 * @return		array							Array of files to download
	 *
	 * @method
	 */
	protected function _GetDownloadPaths()
	{
		// Get Path Definitions
		$arrDefinitions		= $this->GetConfigField('FileDefine');
		
		$arrDownloadPaths	= Array();
		foreach ($arrDefinitions as $intFileType=>&$arrFileType)
		{
			foreach ($arrFileType['Paths'] as $strPath)
			{
				// Get Directory Listing
				$strFiles	= $this->_SSH2Execute("ls $strPath");
				$arrFiles	= Array();
				$arrFiles	= explode("\n", trim($strFiles));
				
				// Filter file names that we don't want
				if (is_array($arrFiles))
				{
					foreach ($arrFiles as $strFilePath)
					{
						$strFilePath	= trim($strFilePath);
						
						if ($this->_SSH2IsDir($strFilePath))
						{
							// This is a directory, ignore
							continue;
						}
						
						// Does this file match our REGEX?
						if (!preg_match($arrFileType['Regex'], trim(basename($strFilePath))))
						{
							// No match
							continue;
						}
						
						// Add the FileImport Type to our element
						$arrFileType['FileImportType']	= $intFileType;
						
						// As far as we can tell, this file is valid
						$arrDownloadPaths[]	= Array('RemotePath' => trim($strPath.'/'.$strFilePath), 'FileType' => $arrFileType);
					}
				}
			}
		}
		return $arrDownloadPaths;
	}
}

?>
