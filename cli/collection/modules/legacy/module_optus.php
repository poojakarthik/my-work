<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_optus
//----------------------------------------------------------------------------//
/**
 * module_optus
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 * @file		module_optus.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleOptus
//----------------------------------------------------------------------------//
/**
 * CollectionModuleOptus
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 *
 * @prefix		col
 *
 * @package		collection
 * @class		CollectionModuleOptus
 */
 class CollectionModuleOptus
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
	 * Constructor for CollectionModuleOptus
	 *
	 * Constructor for CollectionModuleOptus
	 *
	 * @return		CollectionModuleOptus
	 *
	 * @method
	 */
 	function __construct()
 	{
 		$this->_selFileExists = new StatementSelect("FileImport", "Id", "FileName = <FileName>");
 		
 		// Init CURL session
 		$this->_ptrSession = curl_init();
 	}
 	
 	//------------------------------------------------------------------------//
	// Connect
	//------------------------------------------------------------------------//
	/**
	 * Connect()
	 *
	 * Connects to HTTP server and gets catalogue file
	 *
	 * Connects to HTTP server and gets catalogue file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function Connect($arrDefine)
 	{
		// Set private copy of arrDefine
		$this->_arrDefine = $arrDefine;
		
		// Get the Catalogue File
		$intCustId	= $arrDefine['Username'];
		$intCheckId	= $arrDefine['PWord'];
		$strURL		= "https://www.optus.com.au/wholesalenet/catalog/catalog.taf?custid=$intCustId&check=$intCheckId&_function=catalog&_filetype=Speedi";
		//Debug($strURL);
		curl_setopt($this->_ptrSession, CURLOPT_URL				, $strURL);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
		
		if (!($strCatalogFile = curl_exec($this->_ptrSession)) || stripos($strCatalogFile, '<html>'))
		{
			// Can't connect (probably no internet)
			return FALSE;
		}
		
		// Parse Catalogue File
		$this->_arrFiles = Array();
		$arrLines = explode("\r\n", trim($strCatalogFile));
		foreach ($arrLines as $intIndex=>$strLine)
		{
			$arrLine = explode("\t", $strLine);
			
			// Make sure there are no double-ups
			if (!in_array(Array('FileName'=>trim($arrLine[0]), 'URL'=>$arrLine[1]), $arrLines))
			{
				$this->_arrFiles[] = Array('FileName'=>trim($arrLine[0]), 'URL'=>$arrLine[1]);
			}
		}
		reset($this->_arrFiles);
		return (bool)$this->_arrFiles;
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
		if ($this->_ptrSession)
		{
			curl_close($this->_ptrSession);
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
 		if (!$this->_arrFiles)
		{
			// No files to download
			return FALSE;
		}
		
		// Download the next file
		if ($arrCurrent = next($this->_arrFiles))
		{
			$strTestName	= $arrCurrent['FileName'];
			if (stripos($strTestName, '.zip'))
			{
				$strTestName	= substr($arrCurrent['FileName'], 0, -4);
			}
			
			// Do we already have this file?
			//if ($this->_selFileExists->Execute(Array('FileName' => substr($arrCurrent['FileName'], -4))))
			if ($this->_selFileExists->Execute(Array('FileName' => $strTestName)))
			{
				// Yes, recursively call until we find a new file (or FALSE)
				return $this->Download($strDestination);
			}
			
			// Download the file
			curl_setopt($this->_ptrSession, CURLOPT_URL				, trim($arrCurrent['URL']));
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
			curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
			$strDownloadedFile = curl_exec($this->_ptrSession);
			
			// Write to temporary directory
			$ptrTempFile	= fopen($strDestination.$arrCurrent['FileName'], 'w');
			fwrite($ptrTempFile, $strDownloadedFile);
			fclose($ptrTempFile);
			
			// Return file path
			return $arrCurrent['FileName'];
		}
		else
		{
			// No more files to download
			return FALSE;
		}
 	}
}

?>
