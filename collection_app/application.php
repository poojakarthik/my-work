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
$appCollection = new ApplicationCollection($arrConfig);

// run the thing
$appCollection->Collect();

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
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
	 	// Initialise framework components
		$this->_errErrorHandler = new ErrorHandler();
		$this->_rptCollectionReport = new Report("Collection Report for " . date("Y-m-d H:i:s"), "flame@telcoblue.com.au");
		
		$this->_insFileImport = new statementInsert("FileImport");
		$this->_selIsUnique = new StatementSelect("FileImport", "Id", "Carrier = <Carrier> AND (SHA1 = <SHA1> OR FileName = <FileName>)");
		
		// instanciate collection downloaders
		$this->_arrDownloader[COLLECTION_TYPE_FTP] = new CollectionModuleFTP();
		
		// module config
		$this->_arrCollectionModule = $arrConfig['Define'];
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
			// set current module def
			$this->_arrCurrentModule = $arrModule;
			
			// set download module object
			$dldDownloader = $this->_arrDownloader[$arrModule['Type']];
			
			if (!$dldDownloader)
			{
				// No collection module - append to report
				$this->AddToCollectionReport(MSG_NO_COLLECTION_MODULE, Array(
						'<FriendlyName>' 	=> $this->_arrCurrentModule['Name'],
						'<Type>'			=> $this->_arrCurrentModule['Type']));
				continue;
			}
			
			// connect
			if(!$dldDownloader->Connect($arrModule))
			{
				// Connection failed
				$this->AddToCollectionReport(MSG_CONNECTION_FAILED, Array(
						'<FriendlyName>' 	=> $this->_arrCurrentModule['Name'],
						'<Type>'			=> $this->_arrCurrentModule['Type']));
			}
			else
			{
				// Connection successful
				$this->AddToCollectionReport(MSG_CONNECTED, Array(
						'<FriendlyName>' 	=> $this->_arrCurrentModule['Name'],
						'<Type>'			=> $this->_arrCurrentModule['Type']));
				
				// Downloading from report message
				$this->AddToCollectionReport(MSG_DOWNLOADING_FROM);
				//TODO!!!!
				/*foreach ($this->_arrCurrentModule['Dir'] as $strDir)
				{
					$this->AddToCollectionReport(MSG_DIRS, Array('<Dir>' => $strDir));
				}*/
				
				
				// download
				$intCounter = 0;
				while($strFile = $dldDownloader->Download(TEMP_DOWNLOAD_DIR)) 
				{
					$strFileLocation = TEMP_DOWNLOAD_DIR.$strFile;
					
					// Add to report that we're downloading the file
					$intFileSize = filesize($strFileLocation) / 1024;
					$this->AddToCollectionReport(MSG_GRABBING_FILE, Array('<FileName>' => $strFileLocation, '<FileSize>' => $intFileSize));
					
					// set current download file
					$this->_arrCurrentDownloadFile = Array("Location" => $strFileLocation, "Status" => RAWFILE_DOWNLOADED);
					
					// unzip files
					$arrFiles = $this->Unzip($strFileLocation); // always returns array of file locations (or FALSE)
					
					// Add to report that we've unzipped files (provided we actually unzipped)
					if (!$arrFiles || count($arrFiles) < 1)
					{
						$this->AddToCollectionReport(MSG_BAD_FILE);
					}
					elseif (count($arrFiles) > 1)
					{
						$this->AddToCollectionReport(MSG_UNZIPPED_FILES);
						
						foreach ($arrFiles as $strFileName)
						{
							$this->AddToCollectionReport(MSG_UNZIPPED_FILE, Array('<FileName>' => $strFileName));
						}
					}
					
					// record download in db (FileDownload)
					$this->_arrCurrentDownloadFile['FileName'] 		= basename($strFileLocation);
					$this->_arrCurrentDownloadFile['Carrier']		= $this->_arrCurrentModule['Carrier'];
					$this->_arrCurrentDownloadFile['CollectedOn']	= New MySQLFunction("NOW()");
					$intId = $insFileDownload->Execute($this->_arrCurrentDownloadFile);
					
					// import files
					$this->Import($arrFiles);
					
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
		// set status of downloaded file
		$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORTED;
		$bolReturn = TRUE;
		if (!is_array($arrFiles) || count($arrFiles) < 1)
		{
			// set status of downloaded file
			$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORT_FAILED;
			// Add to report that import failed
			$this->AddToCollectionReport(MSG_IMPORT_FAILED, Array('<Reason>' => "Missing File(s)"));
			return FALSE;
		}
		else
		{
			foreach ($arrFiles as $strFileLocation => $strFileName)
			{				
				// set current import file
				$this->_arrCurrentImportFile = Array("Location" => $strFileLocation,"FileName" => $strFileName);
				
				// set status to imported (any errors will change this later)
				$this->_arrCurrentImportFile['Status'] = CDRFILE_WAITING;
				
				// copy file to final location
				if (!$strFileLocation = $this->_StoreImportFile())
				{
					$this->AddToCollectionReport(MSG_MOVE_FILE_FAILED, Array('<FileName>' => $strFileName));
				}
				
				// find file type
				if ($this->_FileType() == CDR_UNKNOWN)
				{
					$this->AddToCollectionReport(MSG_UNKNOWN_FILETYPE, Array('<FileName>' => $strFileName));
				}
				
				// check uniqueness
				if (!$strHash = $this->_IsUnique())
				{
					$this->AddToCollectionReport(MSG_NOT_UNIQUE, Array('<FileName>' => $strFileName));
				}
				
				
				// save db record FileImport
				$this->_arrCurrentImportFile['Carrier']	= $this->_arrCurrentModule['Carrier'];
				$this->_arrCurrentImportFile['ImportedOn'] = new MySQLFunction("Now()");
				if(!$this->_insFileImport->Execute($this->_arrCurrentImportFile))
				{
					$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORT_FAILED;
					$this->AddToCollectionReport(MSG_IMPORT_FAILED, Array('<Reason>' => "Database Failure"));
					$bolReturn = FALSE;
				}
			}
			// Add to report that we've imported
			$this->AddToCollectionReport(MSG_IMPORTED);
			return $bolReturn;
		}
	}
	
	//------------------------------------------------------------------------//
	// _StoreImportFile
	//------------------------------------------------------------------------//
	/**
	 * _StoreImportFile()
	 *
	 * Copies CDR file to permanent storage
	 *
	 * Copies CDR file to permanent storage
	 * 
	 * @return	boolean		TRUE : Copy successful; FALSE: copy failed
	 * 
	 * @method
	 */
	function _StoreImportFile()
	{
		// get file details
		$strFileName			= $this->_arrCurrentImportFile['FileName'];
		$strFileLocation		= $this->_arrCurrentImportFile['Location'];
		$arrCollectionModule	= $this->_arrCurrentModule;
		$strUID 				= uniqid();
		// copy file to final location
		if (!copy($strFileLocation, $arrCollectionModule["FinalDir"].$strUID."_".$strFileName))
		{
			// set status on error
			$this->_arrCurrentImportFile['Status'] = CDRFILE_MOVE_FAILED;
			return FALSE;
		}
		
		// set new file details
		$this->_arrCurrentImportFile['Location'] = $arrCollectionModule["FinalDir"].$strUID."_".$strFileName;
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _FileType
	//------------------------------------------------------------------------//
	/**
	 * _FileType()
	 *
	 * Determines the CDR File's type
	 *
	 * Determines the CDR File's type based on the filename
	 * 
	 * @return	mixed	integer	: the type of CDR File
	 * 					FALSE	: unknown CDR file type
	 * 
	 * @method
	 */
	function _FileType()
	{
		// get file details
		$strFileName 	= $this->_arrCurrentImportFile['FileName'];
		$arrFileType	= $this->_arrCurrentModule["FileType"];
		
		// Find file type
		foreach($arrFileType as $strRegEx => $intFileType)
		{
			if (preg_match($strRegEx, $strFileName))
			{
				$this->_arrCurrentImportFile['FileType'] = $intFileType;
				return $intFileType;
			}
		}
		// set status on error
		$this->_arrCurrentImportFile['Status'] = CDRFILE_BAD_TYPE;
		$this->_arrCurrentImportFile['FileType'] = CDR_UNKNOWN;
		return FALSE;
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
		$strFileName = $this->_arrCurrentImportFile['FileName'];
		
		// get SHA1 hash
		$strHash = sha1_file($strFileLocation); 
		
		// set file details
		$this->_arrCurrentImportFile['SHA1'] = $strHash;
		
		// check name & SHA1 hash in the database (check only against this carrier!)
		$arrWhere = Array();
		$arrWhere['Carrier']	= $this->_arrCurrentModule['Carrier'];
		$arrWhere['FileName']	= $strFileName;
		$arrWhere['SHA1']		= $strHash;
		if (!$this->_selIsUnique->Execute($arrWhere))
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
			$arrFileList = glob("$strOutputDir*");
			
			// build output
			$arrFiles = Array();
			foreach ($arrFileList as $strUnzipedFile)
			{
				// build return array
				$strFileName = basename($strUnzipedFile);
				$arrFiles[$strUnzipedFile] = $strFileName;
			}
			if (count($arrFiles) < 1)
			{
				$this->_arrCurrentDownloadFile['Status'] = RAWFILE_UNZIP_FAILED;
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
 		
 		$this->_rptCollectionReport->AddMessage($strMessage, FALSE);
 	}
 }

?>
