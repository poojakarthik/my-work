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
		
		// instanciate collection downloaders
		$this->_arrDownloader[COLLECTION_TYPE_FTP] = new FtpDownloadModule();
		
		// Define Collection Targets
		$this->_arrCollectionModule["Unitel"]	["Name"]		= "Unitel";
 		$this->_arrCollectionModule["Unitel"]	["Type"]		= COLLECTION_TYPE_FTP;
 		$this->_arrCollectionModule["Unitel"]	["Server"]		= UNITEL_SERVER;
 		$this->_arrCollectionModule["Unitel"]	["Username"]	= UNITEL_USER;
 		$this->_arrCollectionModule["Unitel"]	["PWord"]		= UNITEL_PWORD;
 		$this->_arrCollectionModule["Unitel"]	["Dir"]			= UNITEL_DIR;

		/*
		 * Skeleton Collection Definition
		 * 
		 *	$this->_arrCollectionModule["MODULE"]	["Name"]				= "Friendly Name";
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
		// For each file definition...
 		foreach ($this->_arrCollectionModule as $arrModule)
 		{
			// set current module
			$this->_arrCurrentModule = $arrModule;
			
			// set download module
			$dldDownloader = $this->_arrDownloader[$arrModule['Type']];
			if (!$dldDownloader)
			{
				// no collection module
				// TODO!!!!
				// report
				//TODO!!!! "MISSING COLLECTION MODULE : {$arrModule['Name']}"
				continue;
			}
			
			// connect
			if(!$dldDownloader->Connect($arrModule))
			{
				// report
				//TODO!!!! "CONNECTION FAILED : {$arrModule['Name']}"
			}
			else
			{
				// report
				//TODO!!!! "Connected to : {$arrModule['Name']}"
				
				// download
				$intCounter = 0;
				while($strFileLocation = $dldDownloader->Download(TEMP_DOWNLOAD_DIR)) 
				{
					// set current download file
					$this->_arrCurrentDownloadFile = Array("Location" => $strFileLocation, "Status" => RAWFILE_DOWNLOADED);
						
					// unzip files
					$arrFiles = $this->Unzip($strFilename); // always returns array of file locations (or FALSE)
					
					// record download in db (FileDownload)
					// TODO!!!! - get insert ID
					
					// import files 
					$this->Import($arrFiles);
					
					// record download in db (FileDownload) - status has now been changed
					// UpdateById
					// TODO!!!!
					
					// increment counter
					$intCounter++;
				}
				
				// report
				//TODO!!!! "$intCounter files downloaded from : {$arrModule['Name']}"
				
				// disconnect
				$dldDownloader->Disconnect();
			}
		}
	}

 	//------------------------------------------------------------------------//
	// Import
	//------------------------------------------------------------------------//
	/**
	 * Import()
	 *
	 * Import Downloaded CDR Files
	 *
	 * Copies file to permanent storage, determine file type & uniqueness
	 * 
	 * @param	array	$arrFiles		Files to be imported
	 * @return	bool
	 * 
	 * @method
	 */
	function Import($arrFiles)
	{
		if (is_array($arrFiles))
		{
			foreach ($arrFiles as $strFileLocation => $strFileName)
			{				
				// set current import file
				$this->_arrCurrentImportFile = Array("Location" => $strFileLocation,"FileName" => $strFileName);
				
				// set status to imported (any errors will change this later)
				$this->_arrCurrentImportFile['Status'] = CDRFILE_WAITING;
				
				// copy file to final location
				$strFileLocation = $this->_StoreImportFile();
				
				// find file type
				$strFileType = $this->_FileType();
				
				// check uniqueness
				$strHash = $this->_IsUnique();
				
				// save db record FileImport
				//TODO!!!!
			}
			// set status of downloaded file
			$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORTED;
			return TRUE;
		}
		else
		{
			// set status of downloaded file
			$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORT_FAILED;
			return FALSE;
		}
	}
	
	function _StoreImportFile()
	{
		// get file details
		$strFileName 		= $this->_arrCurrentImportFile['Filename'];
		$strFileLocation 	= $this->_arrCurrentImportFile['Location'];
		$arrCollectionModule	= $this->_arrCollectionModule;
		
		// copy file to final location
		//TODO!!!!
		// set status on error
		$this->_arrCurrentImportFile['Status'] = CDRFILE_MOVE_FAILED;
		
		// set file details
		//TODO!!!!
		//$this->_arrCurrentImportFile['Filename'] =;
		//$this->_arrCurrentImportFile['Location'] =;
	}
	
	function _FileType()
	{
		// get file details
		$strFileName 	= $this->_arrCurrentImportFile['Filename'];
		$arrFileType	= $this->_arrCollectionModule["FileType"];
		
		// Find file type
		//TODO!!!!
		// set status on error
		$this->_arrCurrentImportFile['Status'] = CDRFILE_BAD_TYPE;
		
		// set file details
		//TODO!!!!
		//$this->_arrCurrentImportFile['FileType'] =;
	}
	
	//------------------------------------------------------------------------//
	// IsUnique
	//------------------------------------------------------------------------//
	/**
	 * IsUnique()
	 *
	 * Test if a file is unique on the system
	 *
	 * Test if a file is unique on the system
	 * 
	 * @param	string	$strFileName	Downloaded file name
	 *
	 * @return	string	unique SHA1 hash of the file. returns FALSE if file is
	 * 					not unique
	 * 
	 * @method
	 */
	function _IsUnique($strFileName)
	{
		// get SHA1 hash
		//TODO!!!!
		// $strHash = 
		
		// set file details
		$this->_arrCurrentImportFile['SHA1'] = $strHash;
		
		// check name & SHA1 hash in the database (check only this carrier!)
		//TODO!!!!
		if ()//TODO!!!!
		{
			// file is unique
			return $strHash;
		}
		else
		{
			// file is not uhique, set status
			$this->_arrCurrentImportFile['Status'] = CDRFILE_NOT_UNIQUE;
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------//
	// Unzip
	//------------------------------------------------------------------------//
	/**
	 * Unzip()
	 *
	 * Unzip Downloaded CDR File
	 *
	 * Unzip Downloaded CDR File, also sets the filename in _arrCurrentDownloadFile
	 * 
	 * @param	string	$strFile		Downloaded file
	 *
	 * @return	mixed	array	key = file location, value = file name
	 *					bool	FALSE if passed an invalid (empty) file name
	 * 
	 * @method
	 */
	function Unzip($strFile)
	{
		// get filename
		$strFileName = basename($strFile);
		$this->_arrCurrentDownloadFile['FileName'] = $strFileName;
			
		$strFile = trim($strFile);
		if (!$strFile || !$strFileName)
		{
			// no file
			$this->_arrCurrentDownloadFile['Status'] = RAWFILE_DOWNLOAD_FAILED;
			return FALSE;
		}
		if (strtolower(substr($strFile, -3)) != "zip")
		{
			// not a zip file, return array of 1 file
			return Array($strFile => $strFileName);
		}
		else
		{
			// add line to report
			//TODO!!!! "Unziping File : $strFile"
			
			// clean unzip dir
			// TODO!!!!
			
			// unzip files
			// TODO!!!!
			
			// get list of files (full path)
			//TODO!!!!
			// $arrFileList =
			
			// build output
			$arrFiles = Array();
			foreach ($arrFileList as $strUnzipedFile)
			{
				// add unziped file name to report
				//TODO!!!! "	Extracted File : $strUnzipedFile"
				
				// build return array
				$strFileName = basename($strUnzipedFile);
				$arrFiles[$strUnzipedFile] = $strFileName;
			}
			return $arrFiles;
		}
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
/* 	function ProcessDirectory($strDirectory)
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
	
*/

?>
