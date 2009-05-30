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
 class CollectionModuleFTP extends CollectionModuleBase
 {
	private $_resConnection;

	//public $intBaseCarrier			= CARRIER_UNITEL;
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_FTP;

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
 		$this->_arrModuleConfig['Host']			['Default']		= '';
 		$this->_arrModuleConfig['Host']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Host']			['Description']	= "FTP Server to connect to";

 		$this->_arrModuleConfig['Username']		['Default']		= '';
 		$this->_arrModuleConfig['Username']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Username']		['Description']	= "FTP Username";

 		$this->_arrModuleConfig['Password']		['Default']		= '';
 		$this->_arrModuleConfig['Password']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']		['Description']	= "FTP Password";

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
	 * Connects to FTP server
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
		CliEcho("Connecting to {$strUsername}@{$strHost}");
		$this->_resConnection	= ($this->GetConfigField('SSL') === TRUE) ? @ftp_ssl_connect($strHost) : @ftp_connect($strHost);
		if ($this->_resConnection)
		{
			// Log in to the Server
			CliEcho("Authenticating with u:'{$strUsername}';p:'{$strPassword}'");
			if (@ftp_login($this->_resConnection, $strUsername, $strPassword))
			{
				CliEcho("Getting list of paths...");
				// Retrieve full file listing
				$this->_arrDownloadPaths	= $this->_GetDownloadPaths();
				CliEcho(count($this->_arrDownloadPaths)." paths to download from");
				reset($this->_arrDownloadPaths);
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
			$arrCurrentFile['LocalPath']	= $strDestination.ltrim(basename($arrCurrentFile['RemotePath']), '/');

			// Attempt to download this file
			if (ftp_get($this->_resConnection, $arrCurrentFile['LocalPath'], $arrCurrentFile['RemotePath'], $arrCurrentFile['FileType']['FTPMode']))
			{
				return $arrCurrentFile;
			}
			else
			{
				return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}': {$php_errormsg}";
			}
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
		$arrDefinitions		= $this->GetConfigField('FileDefine');

		//Debug($arrDefinitions);

		$arrDownloadPaths	= Array();
		foreach ($arrDefinitions as $intFileType=>&$arrFileType)
		{
			foreach ($arrFileType['Paths'] as $strPath)
			{
				// Get the directory listing for this
				CliEcho("ls -F {$strPath}");
				//$arrFiles	= ftp_nlist($this->_resConnection, "-F {$strPath}");
				$arrFiles	= ftp_nlist($this->_resConnection, "{$strPath}");
				$arrFiles	= ftp_nlist($this->_resConnection);

				$arrFiles	= scandir("ftp://{$this->_resConnection}/{$strPath}");
				var_dump($arrFiles);

				Debug($arrFiles);

				// Filter file names that we don't want
				if (is_array($arrFiles))
				{
					foreach ($arrFiles as &$strFilePath)
					{
						$strFilePath	= $strPath.rtrim(trim($strFilePath), '*');

						//if (substr(trim($strFilePath), -1) === '/')
						if (is_dir("ftp://{$this->_resConnection}/{$strFilePath}"))
						{
							// This is a directory, ignore
							CliEcho("Ignoring Directory '".basename($strFilePath));
							continue;
						}

						// Does this file match our REGEX?
						if (!preg_match($arrFileType['Regex'], trim(basename($strFilePath))))
						{
							// No match
							CliEcho("File '".basename($strFilePath)."' does not match Regex of '{$arrFileType['Regex']}'");
							continue;
						}

						// Does this FileType have download uniqueness?
						if ($arrFileType['DownloadUnique'])
						{
							// Does this File Name exist in the database?
							if ($this->_selFileDownloaded->Execute(Array('FileName' => basename($strFilePath))))
							{
								// Yes, so we should skip this file
								CliEcho("File '".basename($strFilePath)."' is not unique");
								continue;
							}
						}

						// Add the FileImport Type to our element
						$arrFileType['FileImportType']	= $intFileType;

						// As far as we can tell, this file is valid
						$arrDownloadPaths[]	= Array('RemotePath' => trim($strFilePath), 'FileType' => $arrFileType);
					}
				}
			}
		}
		return $arrDownloadPaths;
	}
}

?>