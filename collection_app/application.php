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
	// _resCollection
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
 		// create FTP downloader
		$ftpDownloader = new FtpDownloadModule();
		
		// For each file definition...
 		foreach ($this->_arrCollectionModule as $arrModule)
 		{
			// set current connection
			$this->_arrCurrentConnection = $arrModule;
			
			// connect
			$ftpDownloader->Connect($arrModule);
			
			// download
			while($strFilename = $ftpDownloader->Download(TEMP_DOWNLOAD_DIR)) // returns false once all files have been downloaded 
			{
				// record download in db FileDownload
				// TODO
				
				// unzip files
				$arrFiles = $this->Unzip($strFilename); // returns array of file locations
				
				// import files 
				$this->Import($arrFiles);
			}
			
			// disconnect
			$ftpDownloader->Disconnect();
		}
	}
	
/*
		
		// Clean predefined temp downloads dir
 		RemoveDir(TEMP_DOWNLOAD_DIR);
 		mkdir(TEMP_DOWNLOAD_DIR, TEMP_DOWNLOAD_DIR_PERMISSIONS);
 		
 		// For each file definition...
 		foreach ($this->_arrCollectionModule as $arrModule)
 		{
 			
 			if ($arrModule["Type"] != COLLECTION_TYPE_FTP)
 			{
				throw new Exception("Undefined Collection Module");
 			}
 			
 			// Connect to the remote server
 			if ($this->_resConnection = ftp_connect($arrModule["Server"]) === FALSE)
 			{
 				throw new Exception ("Can't connect to FTP server ".$arrModule["Server"]);
 			}
 			if (!ftp_login($this->_resConnection, $arrModule["Username"], $arrModule["PWord"]))
 			{
 				throw new Exception ("Bad FTP login info");
 			}
 			
 			// If we have a list of directories, call ProcessDirectory for each one
 			if (is_array($arrModule["Dir"]))
 			{
 				foreach ($arrModule["Dir"] as $strDir)
 				{
 					$this->ProcessDirectory($strDir);
 				}
 			}
 			else
 			{
 				$this->ProcessDirectory($arrModule["Dir"]);
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
 					 					
 		// TODO: Send report
 	}

*/
 	//------------------------------------------------------------------------//
	// ProcessDirectory
	//------------------------------------------------------------------------//
	/**
	 * ProcessDirectory()
	 *
	 * Retrieves CDR Files
	 *
	 * Retrieves CDR Files, then sends them to be processed
	 * 
	 * @param	string	$strDirectory		Directory to be processed
	 * 
	 * @method
	 */
 	function ProcessDirectory($strDirectory)
 	{
		$arrFileList = ftp_nlist($this->_resConnection, $strDirectory);
		
		// For each file
		foreach ($arrFileList as $strFileName)
		{
			// Make sure we're dealing with a file and not a directory
				
			// Is the file new??
			///if (IS_NEW($strFileName))
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


 	//------------------------------------------------------------------------//
	// ProcessFile
	//------------------------------------------------------------------------//
	/**
	 * ProcessFile()
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
	function Import($arrFiles)
	{
		foreach ($arrFiles as $strFile)
		{
			// copy file to final location
			
			// find file type
			
			// check uniqueness
			
			// save db record FileImport
		}
	}
}
?>
