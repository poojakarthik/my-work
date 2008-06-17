<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
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
 * @package		collection
 * @author		Rich Davis
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
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
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleFTP
 */
 class CollectionModuleFTP
 {
	private $_resConnection;
	private $_arrDefine;
	private $_arrFileListing;
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
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Host']			['Default']		= 'ftp.rslcom.com.au';
 		$this->_arrModuleConfig['Host']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Host']			['Description']	= "FTP Server to connect to";
 		
 		$this->_arrModuleConfig['Username']		['Default']		= '';
 		$this->_arrModuleConfig['Username']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Username']		['Description']	= "FTP Username";
 		
 		$this->_arrModuleConfig['Password']		['Default']		= '';
 		$this->_arrModuleConfig['Password']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']		['Description']	= "FTP Password";
 		
 		$this->_arrModuleConfig['PathDefine']	['Default']		= Array();
 		$this->_arrModuleConfig['PathDefine']	['Type']		= DATA_TYPE_ARRAY;
 		$this->_arrModuleConfig['PathDefine']	['Description']	= "Directory to drop the file in";
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
	 * @return	mixed									TRUE: Pass; string: Error
	 *
	 * @method
	 */
 	function Connect()
 	{
		$strHost		= $this->GetConfigField('Host');
		$strUsername	= $this->GetConfigField('Username');
		$strPassword	= $this->GetConfigField('Password');
		
		// Connect to the Server
		$this->_resConnection	= ($this->GetConfigField('SFTP') === TRUE) ? @ftp_ssl_connect($strHost) : @ftp_connect($strHost);
		if ($this->_resConnection)
		{
			// Log in to the Server
			if (@ftp_login($this->_resConnection, $strUsername, $strPassword))
			{
				// Retrieve full file listing
				$this->_GetDownloadPaths();
			}
			else
			{
				return "Could not log in to server with Username '$strUsername' and Password '".str_repeat('*', strlen($strPassword))."'";
			}
		}
		else
		{
			return "Could not connect to server '$strHost'";
		}
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
					if ($this->_selFileExists->Error())
					{

					}
					
					// Check the file size, sleep for a second, check the size again (make sure a file isnt current uploading to the server)
					$intFilesize = ftp_size($this->_resConnection, key($this->_arrFileListing));
					usleep(5000000);
					if ($intFilesize != ftp_size($this->_resConnection, key($this->_arrFileListing)))
					{
						// File is still uploading to the server, so ignore it, and call Download() again
						return $this->Download($strDestination);
					}
					
					// set download mode
					if(strtolower(substr(key($this->_arrFileListing), -3)) == "zip")
					{
						$intMode = FTP_BINARY;
					}
					else
					{
						$intMode = FTP_ASCII;
					}

					// We have a usable file, so download and return the filename
					//Debug(Array($this->_resConnection, TEMP_DOWNLOAD_DIR.key($this->_arrFileListing), key($this->_arrFileListing), $intMode));
					ftp_get($this->_resConnection, TEMP_DOWNLOAD_DIR.key($this->_arrFileListing), key($this->_arrFileListing), $intMode);
					return key($this->_arrFileListing);					
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
			//Debug("Changing directory to: '$strDotDotSlash$strDir'");
			if (!ftp_chdir($this->_resConnection, $strDotDotSlash.$strDir))
			{
				// Problem changing directory, error out
				return $this->Download($strDestination);
			}
			
			// Get our new list of files
			$this->_arrFileListing = $this->ParseRawlist(ftp_rawlist($this->_resConnection, "."), $this->_strConnectionType);
			return $this->Download($strDestination);
		}
		else
		{
			// There are no more files to download
			return FALSE;
		}
		
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
		$arrDefinitions	= $this->GetConfigField('PathDefine');
		
		foreach ($arrDefinitions as $intFileType=>$arrFileType)
		{
			foreach ($arrFileType['Paths'] as $strPath)
			{
				// Get the directory listing for this
				$arrFiles	= @ftp_nlist($this->_resConnection, "-F $strPath");
				
				// Filter file names that we don't want
				foreach ($arrFiles as $strPath)
				{
					if (substr(trim($strPath), -1) === '/')
					{
						// This is a directory, ignore
						continue;
					}
					
					// Does this file match our REGEX?
					if (!preg_match($arrFileType['Regex'], trim($strPath)))
					{
						// No match
						continue;
					}
					
					// As far as we can tell, this file is valid
					$this->_arrDownloadPaths[]	= trim($strPath);
				}
			}
		}
	}
}

?>
