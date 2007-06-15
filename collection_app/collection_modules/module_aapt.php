<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_aapt
//----------------------------------------------------------------------------//
/**
 * module_aapt
 *
 * AAPT Collection Module (because they're special (see: retards))
 *
 * AAPT Collection Module (because they're special (see: retards))
 *
 * @file		module_aapt.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleAAPT
//----------------------------------------------------------------------------//
/**
 * CollectionModuleAAPT
 *
 * AAPT Collection Module (because they're special (see: retards))
 *
 * AAPT Collection Module (because they're special (see: retards))
 *
 *
 * @prefix		col
 *
 * @package		collection
 * @class		CollectionModuleAAPT
 */
 class CollectionModuleAAPT
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
 		
 		// Init CURL session
 		$this->_ptrSession = curl_init();
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
		// Set private copy of arrDefine
		$this->_arrDefine = $arrDefine;
		
		// Connect to the remote server
		$arrParams['username']		= "username=".urlencode($this->_arrDefine['Username']);
		$arrParams['password']		= "password=".urlencode($this->_arrDefine['PWord']);
		//$arrParams['fileAction']	= "fileAction=".urlencode("allNew");
		curl_setopt($this->_ptrSession, CURLOPT_URL				, "https://wholesalebbs.aapt.com.au/preparedownloads.asp");
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_POSTFIELDS		, implode($arrParams, "&"));
		curl_setopt($this->_ptrSession, CURLOPT_POST			, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
		
		// Prepare the download and retrieve token
		if (!$strXML = curl_exec($this->_ptrSession))
		{
			// Can't connect (probably no internet)
			Debug($strXML);
			return FALSE;
		}
		$this->_domDocument = new DOMDocument('1.0', 'iso-8859-1');
		$this->_domDocument->loadXML($strXML);
		$this->_dxpPath = new DOMXPath($this->_domDocument);
		
		// Find token
		foreach ($this->_dxpPath->query("/PrepareDownloadsResponse/ResultSet") as $xndFileNode)
		{
			$this->_strToken = $this->_dxpPath->query("token", $xndFileNode)->Item(0)->nodeValue;
		}
		return (bool)$this->_strToken;
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
 		if (!$this->_strToken)
		{
			// No files to download
			return FALSE;
		}
		
		// Download the ZIP file
		curl_setopt($this->_ptrSession, CURLOPT_URL				, "https://wholesalebbs.aapt.com.au/autodownload.asp?token=$this->_strToken");
		curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
		
		// Download file and save
		$strZIPData = curl_exec($this->_ptrSession);
		
		$strBasename	= "AAPT_NewFiles_".date("Y-m-d_His").".zip";
		$ptrTempFile	= fopen($strDestination.$strBasename, 'w');
		fwrite($ptrTempFile, $strZIPData);
		fclose($ptrTempFile);
		
		$this->_strToken = FALSE;
		
		// Return file name, or FALSE on failure
		return (@filesize($strDestination.$strBasename)) ? $strBasename : FALSE;
 	}
}

?>
