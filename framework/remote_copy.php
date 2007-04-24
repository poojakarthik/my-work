<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// remote_copy
//----------------------------------------------------------------------------//
/**
 * remote_copy
 *
 * An interface to copy local files to a remote location
 *
 * An interface to copy local files to a remote location using a variety of protocols
 *
 * @file		remote_copy.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste' Davis
 * @version		7.04
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 /*
//----------------------------------------------------------------------------//
// Tester Program
//----------------------------------------------------------------------------//
ob_start();
echo "\n[ TESTING REMOTE_COPY.PHP ]\n\n";

echo " * Connect to FTP...";
$rcpFTP = new RemoteCopyFTP("dps", "flame", "zeemu");
$mixResult = $rcpFTP->Connect();
if (is_string($mixResult))
{
	echo "\t\t\t[ FAILED ]\n\t- $mixResult\n";
	die;
}
else
{
	echo "\t\t\t[   OK   ]\n";
}
ob_flush();

echo " * Copy to FTP...\n";
$mixResult = $rcpFTP->Copy("/home/richdavis/docco/", "/home/flame/docco_test/", RCOPY_REMOVE);
if (is_string($mixResult))
{
	print_r($mixResult);
}

$rcpFTP->Disconnect();

ob_flush();
die;







echo " * Connect to SSH2...";
$rcpSSH2 = new RemoteCopySSH("192.168.1.16", "flame", "zeemu");
$mixResult = $rcpSSH2->Connect();
if (is_string($mixResult))
{
	echo "\t\t\t[ FAILED ]\n\t- $mixResult\n";
}
else
{
	echo "\t\t\t[   OK   ]\n";
}
ob_flush();

echo " * Copy to SSH2...\n\n";
//$rcpSSH2->_SSH2Execute("mkdir /home/flame/docco_test");
$mixResult = $rcpSSH2->Copy("/home/richdavis/docco/", "/home/flame/docco_test/", RCOPY_BACKUP);
if (is_string($mixResult))
{
	print_r($mixResult);
}

$rcpSSH2->Disconnect();

ob_flush();
die;
 */
 
//----------------------------------------------------------------------------//
// RemoteCopy
//----------------------------------------------------------------------------//
/**
 * RemoteCopy
 *
 * Remote Copy Base Class
 *
 * Remote Copy Base Class
 *
 *
 * @prefix		rcp
 *
 * @package		framework
 * @class		RemoteCopy
 */
 abstract class RemoteCopy
 {
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * RemoteCopy constructor
	 *
	 * RemoteCopy constructor
	 *
	 * @return		RemoteCopy
	 *
	 * @method
	 */
 	function __construct($strServer, $strUsername, $strPassword)
 	{
 		$this->_strServer	= $strServer;
 		$this->_strUsername	= $strUsername;
 		$this->_strPassword	= $strPassword;
 	}
 	
	//------------------------------------------------------------------------//
	// Connect
	//------------------------------------------------------------------------//
	/**
	 * Connect()
	 *
	 * Connects to a remote server
	 *
	 * Connects to a remote server.  Should only be called by child objects
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	function Connect($strServer = NULL, $strUsername = NULL, $strPassword = NULL)
 	{
 		// Check to see if we are connected and auto-disconnect is enabled
 		if ($this->ptrConnection)
 		{
 			if (RCOPY_AUTO_DISCONNECT)
 			{
	 			// Disconnect first
	 			$this->Disconnect();
 			}
 			else
 			{
 				// Return Error: must disconnect first
 				return "Cannot connect: Already connected!";
 			}
 		}
 		
 		// Set our new values (if there are any)
 		$this->_strServer	= ($strServer)		? $strServer	: $this->_strServer;
 		$this->_strUsername	= ($strUsername)	? $strUsername	: $this->_strUsername;
 		$this->_strPassword	= ($strPassword)	? $strPassword	: $this->_strPassword;
 		
 		// Check to see that we have a Server, Username and Password
 		if (!$this->_strServer)
 		{
 			// No Server
 			return "Cannot Connect: No Server Specified";
 		}
 		if ($this->_strUsername === NULL)
 		{
 			// No Username
 			return "Cannot Connect: No Username Specified";
 		}
 		if ($this->_strPassword === NULL)
 		{
 			// No Password
 			return "Cannot Connect: No Password Specified";
 		}
 		
 		// All good
 		return TRUE;
 	}
 	
	//------------------------------------------------------------------------//
	// Disconnect
	//------------------------------------------------------------------------//
	/**
	 * Disconnect()
	 *
	 * Disconnects from a remote server
	 *
	 * Disconnects from a remote server.  Should NEVER be called.
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	abstract function Disconnect();
 	
 	
	//------------------------------------------------------------------------//
	// Copy
	//------------------------------------------------------------------------//
	/**
	 * Copy()
	 *
	 * Recursively copies a local path to a remote path
	 *
	 * Recursively copies a local path to a remote path.  Accepts Files and Directories.  Should NEVER be called
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	abstract function Copy($strLocalPath, $strRemotePath, $intCopyMode = RCOPY_BACKUP, $intDepth = 0);
 }
 
 
 
 
//----------------------------------------------------------------------------//
// RemoteCopyFTP
//----------------------------------------------------------------------------//
/**
 * RemoteCopyFTP
 *
 * Remote Copy FTP Class
 *
 * Remote Copy FTP Class
 *
 *
 * @prefix		rcp
 *
 * @package		framework
 * @class		RemoteCopyFTP
 */
 class RemoteCopyFTP extends RemoteCopy
 {
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * RemoteCopyFTP constructor
	 *
	 * RemoteCopyFTP constructor
	 *
	 * @return		RemoteCopyFTP
	 *
	 * @method
	 */
 	function __construct($strServer, $strUsername, $strPassword)
 	{
 		// Call Parent constructor
 		parent::__construct($strServer, $strUsername, $strPassword);
 	}
 	
	//------------------------------------------------------------------------//
	// Connect
	//------------------------------------------------------------------------//
	/**
	 * Connect()
	 *
	 * Connects to a remote FTP server
	 *
	 * Connects to a remote FTP server.
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	function Connect($strServer = NULL, $strUsername = NULL, $strPassword = NULL)
 	{
 		// Call parent Connect()
 		$mixReturn = parent::Connect($strServer, $strUsername, $strPassword);
 		if ($mixReturn !== TRUE)
 		{
 			// If we are not allowed to connect
 			return $mixReturn;
 		}
 		
 		// Connect to FTP server
 		if (!$this->_ptrConnection = ftp_connect($this->_strServer))
 		{
 			return "Cannot Connect: Cannot connect to FTP host '$this->_strServer'";
 		}
 		
 		// Authenticate
 		if (!ftp_login($this->_ptrConnection, $this->_strUsername, $this->_strPassword))
 		{
 			return "Cannot Connect: Authentication failed";
 		}
 		
 		// All awesome'd
 		return TRUE;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Disconnect
	//------------------------------------------------------------------------//
	/**
	 * Disconnect()
	 *
	 * Disconnects from the current remote server
	 *
	 * Disconnects from the current remote server
	 *
	 * @return	boolean
	 *
	 * @method
	 */	
 	function Disconnect()
 	{
 		// If there is a stream open, close it
 		if ($this->_ptrConnection)
 		{
 			return (bool)ftp_close($this->_ptrConnection);
 		}
 		
 		// There was no connect, just return TRUE anyway
 		return TRUE;
 	}
 	
	//------------------------------------------------------------------------//
	// Copy
	//------------------------------------------------------------------------//
	/**
	 * Copy()
	 *
	 * Recursively copies a local path to a remote path
	 *
	 * Recursively copies a local path to a remote path.  Accepts Files and Directories
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	function Copy($strLocalPath, $strRemotePath, $intCopyMode = RCOPY_BACKUP, $intDepth = 0)
 	{
 		echo str_repeat("\t", $intDepth)." + Copying '$strLocalPath' to '$strRemotePath'...\n";
 		ob_flush();
 		
 		// Is $strLocalPath a file or directory?
 		if (is_file($strLocalPath))
 		{
 			// move to the right directory
 			chdir(rtrim($strLocalPath, basename($strLocalPath)));
 			
 			// FILE
 			switch ($intCopyMode)
 			{
 				case RCOPY_BACKUP:
 					// Backup the existing file if necessary
 					$this->_Backup($strRemotePath);
 					break;
 					
 				case RCOPY_REMOVE:
 				case RCOPY_OVERWRITE:
 					// Remove the file if it exists
 					@ftp_delete($this->_ptrConnection, $strRemotePath);
 					break;
 				
 				default:
 					return "Cannot Copy: Bad Copy Mode specified!";
 			}
 			
 			// Copy the file to the remote location
 			if (!ftp_put($this->_ptrConnection, $strRemotePath, $strLocalPath, FTP_BINARY))
 			{
 				return "Cannot Copy: Copy Failed";
 			}
 		}
 		else
 		{
 			// DIRECTORY
 			// move to the right directory
 			chdir($strLocalPath);
	 			
 			// Does the directory exist on the remote server?
 			if (@ftp_chdir($this->_ptrConnection, $strRemotePath))
 			{
	 			switch ($intCopyMode)
	 			{
	 				case RCOPY_BACKUP:
	 				case RCOPY_OVERWRITE:
	 					// Irrelevant for Directories
	 					break;
	 					
	 				case RCOPY_REMOVE:
	 					// Clean the directory if it exists
	 					$this->_CleanDir($strRemotePath);
	 					break;
	 				
	 				default:
	 					return "Cannot Copy: Bad Copy Mode specified!";
	 			}
 			}
 			else
 			{
 				// Create the directory
 				ftp_mkdir($this->_ptrConnection, $strRemotePath);
 				ftp_chdir($this->_ptrConnection, $strRemotePath);
 			}
 			
 			// Check for trailing /
 			if (substr($strRemotePath, -1) != '/')
 			{
 				$strRemotePath .= '/';
 			}
 			if (substr($strLocalPath, -1) != '/')
 			{
 				$strLocalPath .= '/';
 			}
 			
 			// Get local directory listing, and traverse
 			$arrFiles = glob("*");
 			foreach ($arrFiles as $strFile)
 			{
 				// Copy all of the Directory's contents
 				$mixResult = $this->Copy($strLocalPath.$strFile, $strRemotePath.basename($strFile), $intCopyMode, $intDepth+1);
 				
 				// Check for error
 				if (is_string($mixResult))
 				{
 					return $mixResult;
 				}
 			}
 		}
 	}
 	
 	
	//------------------------------------------------------------------------//
	// _CleanDir
	//------------------------------------------------------------------------//
	/**
	 * _CleanDir()
	 *
	 * Cleans a Remote Directory
	 *
	 * Cleans a Remote Directory
	 *
	 * @return		boolean
	 *
	 * @method
	 */	
 	protected function _CleanDir($strPath)
 	{
 		// Change to this dir and Get the file list
 		ftp_chdir($this->_ptrConnection, $strPath);
 		$arrFiles = ftp_nlist($this->_ptrConnection, $strPath);
		
		// Remove all files
 		foreach ($arrFiles as $strFile)
 		{
 			// Check if its a directory
 			if (@ftp_chdir($this->_ptrConnection, $strFile))
 			{
 				// recursively call _CleanDir(), then remove the directory
 				$this->_CleanDir($strFile);
 				ftp_rmdir($this->_ptrConnection, $strFile);
 			}
 			else
 			{
 				// Delete the file
 				ftp_delete($this->_ptrConnection, $strFile);
 			}
 		}
 		
 		ftp_cdup($this->_ptrConnection);
 		return TRUE;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// _Backup
	//------------------------------------------------------------------------//
	/**
	 * _Backup()
	 *
	 * Backs up a remote file if necessary
	 *
	 * Backs up a remote file if necessary
	 *
	 * @return		boolean
	 *
	 * @method
	 */	
 	protected function _Backup($strPath, $intRecursion = 0)
 	{
 		// Check if the file exists
 		if (ftp_size($this->_ptrConnection, $strPath) > 0)
 		{
 			echo "BACKUP";
 			// File exists, so try next filename
 			if (!$intRecursion)
 			{
 				$strBackupPath = $strPath.".bk0";
 			}
 			else
 			{
 				$strBackupPath = rtrim($strPath, ".bk".($intRecursion-1)).".bk$intRecursion";
 			}
 			
 			// Call Backup() until a free filename is found
 			$strBackupPath = $this->_Backup($strBackupPath, $intRecursion+1);
 		}
 		else
 		{
 			// Don't need to back up
 			return $strPath;
 		}
		
		// Is this the version we want?
		if (!$intRecursion)
		{
			// Back up file
			return (bool)ftp_rename($this->_ptrConnection, $strPath, $strBackupPath);
		}
		else
		{
			return $strBackupPath;
		}
 	}
 }
 
 
 
 
 //----------------------------------------------------------------------------//
// RemoteCopySSH
//----------------------------------------------------------------------------//
/**
 * RemoteCopySSH
 *
 * Remote Copy SSH Class
 *
 * Remote Copy SSH Class
 *
 *
 * @prefix		rcp
 *
 * @package		framework
 * @class		RemoteCopySSH
 */
 class RemoteCopySSH extends RemoteCopy
 {
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * RemoteCopySSH constructor
	 *
	 * RemoteCopySSH constructor
	 *
	 * @return		RemoteCopySSH
	 *
	 * @method
	 */
 	function __construct($strServer, $strUsername, $strPassword)
 	{
 		// Call Parent constructor
 		parent::__construct($strServer, $strUsername, $strPassword);
 	}
 	
	//------------------------------------------------------------------------//
	// Connect
	//------------------------------------------------------------------------//
	/**
	 * Connect()
	 *
	 * Connects to a remote SSH2 server
	 *
	 * Connects to a remote SSH2 server.
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	function Connect($strServer = NULL, $strUsername = NULL, $strPassword = NULL)
 	{
 		// Call parent Connect()
 		$mixReturn = parent::Connect($strServer, $strUsername, $strPassword);
 		if ($mixReturn !== TRUE)
 		{
 			// If we are not allowed to connect
 			return $mixReturn;
 		}
 		
 		// Connect to SSH2 server
 		if (!$this->_ptrConnection = ssh2_connect($this->_strServer))
 		{
 			return "Cannot Connect: Cannot connect to SSH2 host '$this->_strServer'";
 		}
 		
 		// Authenticate
 		if (!ssh2_auth_password($this->_ptrConnection, $this->_strUsername, $this->_strPassword))
 		{
 			return "Cannot Connect: Authentication failed";
 		}
 		
 		// Open SFTP Stream
 		/*if (!$this->_ptrSFTPStream = @fopen("ssh2.sftp://$this->_ptrConnection"))
 		{
 			return "Cannot Connect: Unable to open SFTP stream";
 		}*/
 		
 		// All awesome'd
 		return TRUE;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Disconnect
	//------------------------------------------------------------------------//
	/**
	 * Disconnect()
	 *
	 * Disconnects from the current remote server
	 *
	 * Disconnects from the current remote server
	 *
	 * @return	boolean
	 *
	 * @method
	 */	
 	function Disconnect()
 	{
 		// If there is a stream open, close it
 		if ($this->_ptrConnection)
 		{
 			unset($this->_ptrConnection);
 			@fclose($this->_ptrSFTPStream);
 		}
 		
 		// There was no connect, just return TRUE anyway
 		return TRUE;
 	}
 	
	//------------------------------------------------------------------------//
	// Copy
	//------------------------------------------------------------------------//
	/**
	 * Copy()
	 *
	 * Recursively copies a local path to a remote path
	 *
	 * Recursively copies a local path to a remote path.  Accepts Files and Directories
	 *
	 * @return		mixed		TRUE: Success; string: error message
	 *
	 * @method
	 */	
 	function Copy($strLocalPath, $strRemotePath, $intCopyMode = RCOPY_BACKUP, $intDepth = 0)
 	{
 		$strLocalPathEscaped	= str_replace(" ", "\ ", str_replace("\ ", " ", $strLocalPath));
 		$strRemotePathEscaped	= str_replace(" ", "\ ", str_replace("\ ", " ", $strRemotePath));
 		
 		echo str_repeat("\t", $intDepth)." + Copying '$strLocalPath' to '$strRemotePath'...\n";
 		ob_flush();
 		
 		// Is $strLocalPath a file or directory?
 		if (is_file($strLocalPath))
 		{
 			//echo "FILE\n";
 			ob_flush();
 			// FILE
 			// move to the right directory
 			//echo rtrim($strLocalPath, basename($strLocalPath));
 			chdir(rtrim($strLocalPath, basename($strLocalPath)));
 			
 			switch ($intCopyMode)
 			{
 				case RCOPY_BACKUP:
 					// Backup the existing file if necessary
 					$this->_Backup($strRemotePathEscaped);
 					break;
 					
 				case RCOPY_REMOVE:
 				case RCOPY_OVERWRITE:
 					// Remove the file if it exists
 					$strResult = $this->_SSH2Execute("unlink $strRemotePathEscaped");
 					//@unlink($strRemotePath, $this->_ptrSFTPStream);
 					break;
 				
 				default:
 					return "Cannot Copy: Bad Copy Mode specified!";
 			}
 			
 			// Copy the file to the remote location
 			if (!ssh2_scp_send($this->_ptrConnection, $strLocalPath, $strRemotePathEscaped, 0744))
 			{
 				return "Cannot Copy: Copy Failed";
 			}
 		}
 		else
 		{
 			// DIRECTORY
 			//echo "DIR\n";
 			ob_flush();
 			// move to the right directory
 			chdir($strLocalPath);
	 		
 			// Does the directory exist on the remote server?
 			if ($this->_SSH2IsDir($strRemotePathEscaped))
 			{
	 			switch ($intCopyMode)
	 			{
	 				case RCOPY_BACKUP:
	 				case RCOPY_OVERWRITE:
	 					// Irrelevant for Directories
	 					break;
	 					
	 				case RCOPY_REMOVE:
	 					// Clean the directory if it exists
	 					$this->_CleanDir($strRemotePathEscaped);
	 					break;
	 				
	 				default:
	 					return "Cannot Copy: Bad Copy Mode specified!";
	 			}
 			}
 			else
 			{
 				// Create the directory
 				$this->_SSH2Execute("mkdir $strRemotePathEscaped 0777");
 				$this->_SSH2Execute("cd $strRemotePathEscaped");
 			}
 			
 			// Check for trailing /
 			if (substr($strRemotePath, -1) != '/')
 			{
 				$strRemotePath .= '/';
 			}
 			if (substr($strLocalPath, -1) != '/')
 			{
 				$strLocalPath .= '/';
 			}
 			
 			// Get local directory listing, and traverse
 			$arrFiles = glob("*");
 			//print_r($arrFiles);
 			foreach ($arrFiles as $strFile)
 			{
 				// Copy all of the Directory's contents
 				$mixResult = $this->Copy($strLocalPath.$strFile, $strRemotePath.basename($strFile), $intCopyMode, $intDepth+1);
 				
 				// Check for error
 				if (is_string($mixResult))
 				{
 					return $mixResult;
 				}
 			}
 		}
 	}
 	
 	
	//------------------------------------------------------------------------//
	// _CleanDir
	//------------------------------------------------------------------------//
	/**
	 * _CleanDir()
	 *
	 * Cleans a Remote Directory
	 *
	 * Cleans a Remote Directory
	 *
	 * @return		boolean
	 *
	 * @method
	 */	
 	protected function _CleanDir($strPath)
 	{
 		// Change to the new directory and get the file list
 		$arrFiles = explode("\n", $this->_SSH2Execute("ls $strPath"));
 		ob_flush();
		
		// Remove all files
 		foreach ($arrFiles as $strFile)
 		{
 			// Check for trailing /
 			if (substr($strPath, -1) != '/')
 			{
 				$strPath .= '/';
 			}
 			if (substr($strPath, -1) != '/')
 			{
 				$strPath .= '/';
 			}
 			
 			// Check if its a directory
 			if ($this->_SSH2IsDir($strFile))
 			{
 				// recursively call _CleanDir(), then remove the directory
 				$this->_CleanDir($strPath.$strFile);
 				$this->_SSH2Execute("rmdir $strPath");
 			}
 			else
 			{
 				// Delete the file
 				$this->_SSH2Execute("unlink $strPath");
 			}
 		}
 		
 		// return to the parent directory
 		$this->_SSH2Execute("cd ..");
 		return TRUE;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// _Backup
	//------------------------------------------------------------------------//
	/**
	 * _Backup()
	 *
	 * Backs up a remote file if necessary
	 *
	 * Backs up a remote file if necessary
	 *
	 * @return		boolean
	 *
	 * @method
	 */	
 	protected function _Backup($strPath, $intRecursion = 0)
 	{
 		// Check if the file exists
 		if ($this->_SSH2Execute("stat $strPath"))
 		{
 			// File exists, so try next filename
 			if (!$intRecursion)
 			{
 				$strBackupPath = $strPath.".bk0";
 			}
 			else
 			{
 				$strBackupPath = rtrim($strPath, ".bk".($intRecursion-1)).".bk$intRecursion";
 				ob_flush();
 			}
 			
 			// Call Backup() until a free filename is found
 			$strBackupPath = $this->_Backup($strBackupPath, $intRecursion+1);
 		}
 		else
 		{
 			// Don't need to back up
 			return $strPath;
 		}
		
		// Is this the version we want?
		if (!$intRecursion)
		{
			// Back up file
			$this->_SSH2Execute("mv $strPath $strBackupPath");
			return TRUE;
		}
		else
		{
			return $strBackupPath;
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
	 * @return	string
	 *
	 * @method
	 */	
 	protected function _SSH2Execute($strCommand)
 	{
 		$ptrStream = ssh2_exec($this->_ptrConnection, $strCommand);
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
	 * @return	string
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