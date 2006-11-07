<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationCollection();



// finished
echo("\n-- End of Collection --\n");
die();


//----------------------------------------------------------------------------//
// ApplicationCollection
//----------------------------------------------------------------------------//
/**
 * ApplicationCollection
 *
 * Collection Application
 *
 * Collection Application
 *
 *
 * @prefix		app
 *
 * @package		vixen
 * @class		ApplicationCollection
 */
 class ApplicationCollection extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// errErrorHandler
	//------------------------------------------------------------------------//
	/**
	 * errErrorHandler
	 *
	 * Application Error Handler instance
	 *
	 * Application Error Handler instance
	 *
	 * @type		ErrorHandler
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	public $errErrorHandler;
	
	//------------------------------------------------------------------------//
	// _rptCollectionReport
	//------------------------------------------------------------------------//
	/**
	 * _rptCollectionReport
	 *
	 * Collection report
	 *
	 * Collection Report, including information on errors, failed collections,
	 * and a total of each
	 *
	 * @type		Report
	 *
	 * @property
	 */
	private $_rptCollectionReport;
 	
	//------------------------------------------------------------------------//
	// _arrCollectionModule
	//------------------------------------------------------------------------//
	/**
	 * _arrCollectionModulevv
	 *
	 * Array of collection modules
	 *
	 * Array of collection modules
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrNormalisationModule;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Collection Application
	 *
	 * Constructor for the Collection Application
	 *
	 * @return		ApplicationCollection
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function __construct()
 	{
	 	// Initialise framework components
		$this->_errErrorHandler = new ErrorHandler();
		$this->_rptCollectionReport = new Report("Collection Report for " . now(), "flame@telcoblue.com.au");
		//set_exception_handler(Array($this->_errErrorHandler, "PHPExceptionCatcher"));
		//set_error_handler(Array($this->_errErrorHandler, "PHPErrorCatcher"));
		
		// Create an instance of each Collection module
 		$this->_arrCollectionModule["Unitel"]	["Type"]		= COLLECTION_TYPE_FTP;
 		$this->_arrCollectionModule["Unitel"]	["Server"]		= UNITEL_SERVER;
 		$this->_arrCollectionModule["Unitel"]	["Username"]	= UNITEL_USER;
 		$this->_arrCollectionModule["Unitel"]	["PWord"]		= UNITEL_PWORD;
 		$this->_arrCollectionModule["Unitel"]	["Dir"]			= UNITEL_DIR;

		/*
		 * Skeleton Collection Definition
		 * 
		 *  $this->_arrCollectionModule["MODULE"]	["Type"]				= COLLECTION_TYPE;
 		 *	$this->_arrCollectionModule["MODULE"]	["Server"]				= SERVER;
 		 *	$this->_arrCollectionModule["MODULE"]	["Username"]			= USERNAME;
 		 *	$this->_arrCollectionModule["MODULE"]	["PWord"]				= PWORD;
 		 *	$this->_arrCollectionModule["MODULE"]	["Dir"]		[]			= DIR_1;
 		 *	$this->_arrCollectionModule["MODULE"]	["Dir"]		[]			= DIR_2;
 		 *	$this->_arrCollectionModule["MODULE"]	["ZipPword"]			= ZIP_PWORD;
 		 *	$this->_arrCollectionModule["MODULE"]	["FileType"]["REGEX_1"]	= CONSTANT_1;
 		 *	$this->_arrCollectionModule["MODULE"]	["FileType"]["REGEX_2"]	= CONSTANT_2;
		 */
 	}

 	//------------------------------------------------------------------------//
	// Collect
	//------------------------------------------------------------------------//
	/**
	 * Collect()
	 *
	 * Downloads CDR Files
	 *
	 * Downloads CDR files and performs checks on type
	 *
	 * @method
	 */
 	function Collect()
 	{
 		// Clean predefined temp downloads dir
 		RemoveDir(TEMP_DOWNLOAD_DIR);
 		mkdir(TEMP_DOWNLOAD_DIR, TEMP_DOWNLOAD_DIR_PERMISSIONS);
 		
 		
 		
 		// For each file definition...
 		foreach ($this->_arrCollectionModule as $arrModule)
 		{
 			// Get list of files from remote server
 			if ($resConnection = ftp_connect($arrModule["Server"]) === FALSE)
 			{
 				throw new Exception ("Can't connect to FTP server ".$arrModule["Server"]);
 			}
 			
 			if (!ftp_login($resConnection, $arrModule["Username"], $arrModule["PWord"]))
 			{
 				throw new Exception ("Bad FTP login info");
 			}
 			
 			if (is_array($arrModule["Dir"]))
 			{
 				foreach ($arrModule["Dir"] as $strDir)
 				{
 					$arrFileList = array_merge(arrFileList, ftp_nlist($resConnection, $strDir));
 				}
 			}
 			else
 			{
 				$arrFileList = ftp_nlist($resConnection, $arrModule["Dir"]);
 			}
 			
 			// For each file
 			foreach ($arrFileList as $strFileName)
 			{
 				// Is the file new??
 				if (IS_NEW($strFileName))
 				{
 					// Download from remote server
 					
 					// Is it a zip file??
 					if (IS_ZIP_FILE($strFileName))
 					{
 						// Unzip file to predefined dir ("/tmp/vixen_download") 
 						
 						// For each file
 						foreach ($arrZipFileList as $strZipFile)
 						{
 							Process($filFile);
 						}
 					}
 					else
 					{
						Process($filFile);
 					}
 				}
 			}
 		}
 					 					
 		// Send report
 	}

 	//------------------------------------------------------------------------//
	// Process
	//------------------------------------------------------------------------//
	/**
	 * Process()
	 *
	 * Processes CDR Files
	 *
	 * Copies file to permanent storage, determine file type, returning data to
	 * be directly inserted into the DB.
	 * 
	 * @param	file	$filFile		File to be processed
	 * 
	 * @method
	 */
 	function Process($filFile)
 	{
 		// Copy file to permanent storage
 		
 		// Determine filetype by regex
 		
 		// Check if in the system (SHA-1)
 		
 		// Write to DB
 	}
 	
}
?>
