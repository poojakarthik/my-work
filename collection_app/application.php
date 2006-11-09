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
$appCollection = new ApplicationCollection();



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
	// _insFileImport
	//------------------------------------------------------------------------//
	/**
	 * _insFileImport
	 *
	 * Used to add files to the FileImport table
	 *
	 * Used to add files to the FileImport table
	 *
	 * @type		StatementInsert
	 *
	 * @property
	 */
	private $_insFileImport;
	
	//------------------------------------------------------------------------//
	// _selIsUnique
	//------------------------------------------------------------------------//
	/**
	 * _selIsUnique
	 *
	 * Used to check if a file isnt in the DB already
	 *
	 * Used to check if a file isnt in the DB already
	 *
	 * @type		StatementSelect
	 *
	 * @property
	 */
	private $_selIsUnique;
 	
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
		$this->_rptCollectionReport = new Report("Collection Report for " . date("Y-m-d"), "flame@telcoblue.com.au");
		//set_exception_handler(Array($this->_errErrorHandler, "PHPExceptionCatcher"));
		//set_error_handler(Array($this->_errErrorHandler, "PHPErrorCatcher"));
		
		$this->_insFileImport = new statementInsert("FileImport");
		$this->_selIsUnique = new StatementSelect("FileImport", "Id", "Carrier = <carrier>");
		
		// instanciate collection downloaders
		$this->_arrDownloader[COLLECTION_TYPE_FTP] = new CollectionModuleFTP();
		
		// Define Collection Targets
		$this->_arrCollectionModule["Unitel"]	["Name"]		= "Unitel";
		$this->_arrCollectionModule["Unitel"]	["Carrier"]		= CARRIER_UNITEL;
 		$this->_arrCollectionModule["Unitel"]	["Type"]		= COLLECTION_TYPE_FTP;
 		$this->_arrCollectionModule["Unitel"]	["Server"]		= UNITEL_SERVER;
 		$this->_arrCollectionModule["Unitel"]	["Username"]	= UNITEL_USER;
 		$this->_arrCollectionModule["Unitel"]	["PWord"]		= UNITEL_PWORD;
 		$this->_arrCollectionModule["Unitel"]	["Dir"]			= UNITEL_DIR;
 		$this->_arrCollectionModule["Unitel"]	["FinalDir"]	= UNTIEL_FINAL_DIR;

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
		$insFileDownload = new StatementInsert("FileDownload");
		$ubiFileDownload = new StatementUpdateById("FileDownload");
			
		// For each file definition...
 		foreach ($this->_arrCollectionModule as $arrModule)
 		{
			// set current module
			$this->_arrCurrentModule = $arrModule;
			
			// set download module
			$dldDownloader = $this->_arrDownloader[$arrModule['Type']];
			if (!$dldDownloader)
			{
				// No collection module - append to report
				$this->AddToCollectionReport(MSG_NO_COLLECTION_MODULE, Array(
						'<FriendlyName>' 	=> $this->_arrDownloader[$arrModule['FriendlyName']],
						'<Type>'			=> $this->_arrDownloader[$arrModule['Type']]));
				continue;
			}
			
			// connect
			if(!$dldDownloader->Connect($arrModule))
			{
				// Connection failed
				$this->AddToCollectionReport(MSG_CONNECTION_FAILED, Array(
						'<FriendlyName>' 	=> $this->_arrDownloader[$arrModule['FriendlyName']],
						'<Type>'			=> $this->_arrDownloader[$arrModule['Type']]));
			}
			else
			{
				// Connection successful
				$this->AddToCollectionReport(MSG_CONNECTED, Array(
						'<FriendlyName>' 	=> $this->_arrDownloader[$arrModule['FriendlyName']],
						'<Type>'			=> $this->_arrDownloader[$arrModule['Type']]));
				
				// Downloading from report message
				$this->AddToCollectionReport(MSG_DOWNLOADING_FROM);
				foreach ($this->_arrCurrentModule['Dir'] as $strDir)
				{
					$this->AddToCollectionReport(MSG_DIRS, Array('<Dir>' => $strDir));
				}
				
				// download
				$intCounter = 0;
				while($strFileLocation = $dldDownloader->Download(TEMP_DOWNLOAD_DIR)) 
				{
					// Add to report that we're downloading the file
					$this->AddToCollectionReport(MSG_GRABBING_FILE, Array('<FileName>' => $strFileLocation));
					
					// set current download file
					$this->_arrCurrentDownloadFile = Array("Location" => $strFileLocation, "Status" => RAWFILE_DOWNLOADED);
					
					// unzip files
					$arrFiles = $this->Unzip($strFileLocation); // always returns array of file locations (or FALSE)
					
					// Add to report that we've unzipped files (provided we actually unzipped)
					if (count($arrFiles) > 1)
					{
						$this->AddToCollectionReport(MSG_UNZIPPED_FILES);
						
						foreach ($arrFiles as $strFilename)
						{
							$this->AddToCollectionReport(MSG_UNZIPPED_FILE, Array('<FileName>' => $strFilename));
						}
					}
					
					// record download in db (FileDownload)
					$this->_arrCurrentDownloadFile['FileName'] 		= basename($strFileLocation);
					$this->_arrCurrentDownloadFile['Carrier']		= $this->_arrCurrentModule['Carrier'];
					$this->_arrCurrentDownloadFile['CollectedOn']	= "NOW()";
					$intId = $insFileDownload->Execute($this->_arrCurrentDownloadFile);
					
					// import files
					if (!$this->Import($arrFiles))
					{
						$this->AddToCollectionReport(MSG_IMPORT_FAILED, Array('<Reason>' => "We need a reason to fail?"));
					}
					else
					{
						// Add to report that we've imported
						$this->AddToCollectionReport(MSG_IMPORTED);							
					}
					
					// record download in db (FileDownload) - status has now been changed
					$ubiFileDownload->Execute($this->_arrCurrentDownloadFile);
									
					// increment counter
					$intCounter++;
				}
				
				// End the Report, and send it off
				$this->AddToCollectionReport(MSG_TOTALS, Array('<TotalFiles>' => $intCounter));
				$this->_rptCollectionReport->Finish();
				
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
				$this->_arrCurrentImportFile['Carrier']	= $this->_arrCurrentModule['Carrier'];
				$this->_insFileImport->Execute($this->_arrCurrentImportFile);
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
		$strFileName			= $this->_arrCurrentImportFile['Filename'];
		$strFileLocation		= $this->_arrCurrentImportFile['Location'];
		$arrCollectionModule	= $this->_arrCollectionModule;
		
		// copy file to final location
		if (!copy($strFileLocation, $this->_arrCollectionModule["FinalDir"]))
		{
			// set status on error
			$this->_arrCurrentImportFile['Status'] = CDRFILE_MOVE_FAILED;	
		}
		
		// set new file details
		$this->_arrCurrentImportFile['Location'] = $this->_arrCollectionModule["FinalDir"] . "/" .$strFileName;
	}
	
	function _FileType()
	{
		// get file details
		$strFileName 	= $this->_arrCurrentImportFile['Filename'];
		$arrFileType	= $this->_arrCollectionModule["FileType"];
		
		// Find file type
		if (preg_match(FILE_PREG_RSLCOM))
		{
			$this->_arrCurrentImportFile['FileType'] = CDR_UNTIEL_RSLCOM;
		}
		/*elseif (preg_match())
		{
			
		}*/
		else
		{
			// set status on error
			$this->_arrCurrentImportFile['Status'] = CDRFILE_BAD_TYPE;
		}
	}
	
	//------------------------------------------------------------------------//
	// IsUnique
	//------------------------------------------------------------------------//
	/**
	 * IsUnique()
	 *
	 * Test if the import file is unique on the system
	 *
	 * Test if the import file is unique on the system
	 * 
	 *
	 * @return	string	unique SHA1 hash of the file. returns FALSE if file is
	 * 					not unique
	 * 
	 * @method
	 */
	function _IsUnique()
	{
		// get file details
		$strFileLocation = $this->_arrCurrentImportFile['Location'];
		
		// get SHA1 hash
		$strHash = sha1_file($strFileLocation); 
		
		// set file details
		$this->_arrCurrentImportFile['SHA1'] = $strHash;
		
		// check name & SHA1 hash in the database (check only this carrier!)
		if (!$this->_selIsUnique->Execute(Array('carrier' => $this->_arrCurrentModule['Carrier'])))
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
	 * @param	string	$strFileLocation		Downloaded file
	 *
	 * @return	mixed	array	key = file location, value = file name
	 *					bool	FALSE if passed an invalid (empty) file name
	 * 
	 * @method
	 */
	function Unzip($strFileLocation)
	{
		// get filename
		$strFileName = basename($strFileLocation);
		$this->_arrCurrentDownloadFile['FileName'] = $strFileName;
		
		$strFileLocation = trim($strFileLocation);
		if (!$strFileLocation || !$strFileName)
		{
			// no file
			$this->_arrCurrentDownloadFile['Status'] = RAWFILE_DOWNLOAD_FAILED;
			return FALSE;
		}
		if (strtolower(substr($strFileLocation, -3)) != "zip")
		{
			// not a zip file, return array of 1 file
			return Array($strFileLocation => $strFileName);
		}
		else
		{
			// set password
			if ($this->_arrCurrentModule['ZipPword'])
			{
				$strPassword = "-p {$this->_arrCurrentModule['ZipPword']}";
			}
			else
			{
				$strPassword = '';
			}
			
			// set and clean output dir
			$strOutputDir = UNZIP_DIR;
			CleanDir($strOutputDir);
			
			// unzip files
			$strCommand = "unzip $strPassword $strFileLocation -d $strOutputDir";
			exec($strCommand);
			
			// get list of files (full path)
			$arrFileList = glob("*");
			
			// build output
			$arrFiles = Array();
			foreach ($arrFileList as $strUnzipedFile)
			{
				// build return array
				$strFileName = basename($strUnzipedFile);
				$arrFiles[$strUnzipedFile] = $strFileName;
			}
			return $arrFiles;
		}
	}

 	//------------------------------------------------------------------------//
	// AddToCollectionReport
	//------------------------------------------------------------------------//
	/**
	 * AddToCollectionReport()
	 *
	 * Adds a message to the collection report
	 *
	 * Adds a message to the collection report
	 *
	 * @param	string		$strMessage			The message - use constants
	 * 											from definition.php.
	 * @param	array		$arrAliases			Associative array of alises.
	 * 											MUST use the same aliases as used in the 
	 * 											constant being used.  Key is the alias (including the <>'s)
	 * 											, and the Value is the value to be inserted.
	 * 
	 * @method
	 */
 	function AddToCollectionReport($strMessage, $arrAliases = Array())
 	{
 		foreach ($arrAliases as $arrAlias => $arrValue)
 		{
 			$strMessage = str_replace($arrAlias, $arrValue, $strMessage);
 		}
 		
 		$this->rptCollectionReport->AddMessage($strMessage, FALSE);
 	}
 }

?>
