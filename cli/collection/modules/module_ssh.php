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
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_RESOURCE_SSH2;
	
	private $_resConnection;
 	
	public $intBaseFileType = RESOURCE_TYPE_FILE_RESOURCE_SSH2;
	
	public static function getConfigDefinition()
	{
		// Values defined in here are DEFAULT values
		return	array
				(
					'Host'	=>		array
									(
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'SSH Server to connect to'
									),
					'Username'		=>	array
									(
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'SSH Username'
									),
					'Password'		=>	array
									(
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'SSH Password'
									),
					'SFTP'			=>	array
									(
										'Value'			=> false,
										'Type'			=> DATA_TYPE_BOOLEAN,
										'Description'	=> 'Enable/Disable SFTP'
									),
					'Port'			=>	array
									(
										'Value'			=> 22,
										'Type'			=> DATA_TYPE_INTEGER,
										'Description'	=> 'SSH Port'
									),
					'FileDefine'	=>	array
									(
										'Value'			=> array(),
										'Type'			=> DATA_TYPE_ARRAY,
										'Description'	=> 'Definitions for where to download files from'
									)
				);
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
		$strHost		= $this->_oConfig->Host;
		$strUsername	= $this->_oConfig->Username;
		$strPassword	= $this->_oConfig->Password;
		$intPort		= $this->_oConfig->Port;
		$bolSFTP		= $this->_oConfig->SFTP;
		
 		// Connect to SSH2 server
 		if ($this->_resConnection = ssh2_connect($strHost, $intPort))
 		{
	 		// Authenticate
	 		if (ssh2_auth_password($this->_resConnection, $strUsername, $strPassword))
	 		{
				// Enable SFTP subsystem (if enabled)
				if ($bolSFTP && !($this->_resSFTPConnection = ssh2_sftp($this->_resConnection)))
				{
					return "Unable to initialise SFTP subsystem";
				}
				
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
			$arrCurrentFile['LocalPath']	= $strDestination.ltrim(basename($arrCurrentFile['RemotePath']), '/');
			
			// Attempt to download this file
			$strSFTPPath	= "ssh2.sftp://{$this->_resSFTPConnection}{$arrCurrentFile['RemotePath']}";
			if ($this->_resSFTPConnection && ($resFileStream = fopen($strSFTPPath, 'r')))
			{
				// SFTP
				if (file_put_contents($arrCurrentFile['LocalPath'], file_get_contents($strSFTPPath)))
				{
					return $arrCurrentFile;
				}
			}
			elseif (ssh2_scp_recv($this->_resConnection, $arrCurrentFile['RemotePath'], $arrCurrentFile['LocalPath']))
			{
				// SCP
				return $arrCurrentFile;
			}
			return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}'";
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
	// _directoryListing
	//------------------------------------------------------------------------//
	/**
	 * _directoryListing()
	 *
	 * Implements dir/ls for SSH2 connections
	 *
	 * Implements dir/ls for SSH2 connections
	 *
	 * @param	string		$strPath		The path to ls
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	protected function _directoryListing($strPath)
 	{
 		$arrFiles	= Array();
 		if ($this->_resSFTPConnection)
 		{
 			// SFTP
 			$resOpenDir	= opendir("ssh2.sftp://{$this->_resSFTPConnection}{$strPath}");
 			while ($strFile = readdir($resOpenDir))
 			{
 				// Only add files
 				if ($strFile !== '.' && $strFile !== '..' && is_file("ssh2.sftp://{$this->_resSFTPConnection}{$strPath}{$strFile}"))
 				{
 					$arrFiles[]	= $strFile;
 				}
 			}
 		}
 		else
 		{
 			// SSH
			$strFiles	= $this->_SSH2Execute("ls $strPath");
			$arrFiles	= explode("\n", trim($strFiles));
 		}
 		
 		return $arrFiles;
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
		$arrDefinitions		= $this->_oConfig->FileDefine;
		
		$arrDownloadPaths	= Array();
		foreach ($arrDefinitions as $intFileType=>&$arrFileType)
		{
			foreach ($arrFileType['Paths'] as $strPath)
			{
				// Get Directory Listing
				$arrFiles	= $this->_directoryListing($strPath);
				
				// Filter file names that we don't want
				if (is_array($arrFiles))
				{
					foreach ($arrFiles as $strFilePath)
					{
						$strFilePath	= trim($strFilePath);
						
						// Ignore directories
						if (!$this->_resSFTPConnection && $this->_SSH2IsDir($strFilePath))
						{
							continue;
						}
						
						// Does this file match our REGEX?
						if (!preg_match($arrFileType['Regex'], trim(basename($strFilePath))))
						{
							// No match
							continue;
						}
						
						// Does this FileType have download uniqueness?
						if ($arrFileType['DownloadUnique'])
						{
							// Does this File Name exist in the database?
							if ($this->_selFileDownloaded->Execute(Array('FileName' => basename($strFilePath))))
							{
								// Yes, so we should skip this file
								continue;
							}
						}
						
						// Add the FileImport Type to our element
						$arrFileType['FileImportType']	= $intFileType;
						
						// As far as we can tell, this file is valid
						$strPath	.= ($strPath) ? '/' : '';
						$arrDownloadPaths[]	= Array('RemotePath' => trim($strPath.$strFilePath), 'FileType' => $arrFileType);
					}
				}
			}
		}
		return $arrDownloadPaths;
	}
}

?>
