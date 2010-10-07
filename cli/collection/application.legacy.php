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
	public $_rptCollectionReport;

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
		parent :: __construct();

		// Initialise framework components
		$this->_errErrorHandler = new ErrorHandler();
		$this->_rptCollectionReport = new Report("Collection Report for " . date("Y-m-d H:i:s"), "flame@telcoblue.com.au");

		$arrDefine = $this->db->FetchClean("FileImport");
		$arrDefine['ImportedOn'] = new MySQLFunction("NOW()");
		$this->_insFileImport = new statementInsert("FileImport", $arrDefine);
		$this->_selIsUnique = new StatementSelect("FileImport", "Id", "Carrier = <Carrier> AND (SHA1 = <SHA1> OR FileName = <FileName>)");
		$this->_selCheckHash = new StatementSelect("FileImport", "Id", "Carrier = <Carrier> AND SHA1 = <SHA1>");

		// instanciate collection downloaders
		$this->_arrDownloader[COLLECTION_TYPE_FTP] = new CollectionModuleFTP();
		$this->_arrDownloader[COLLECTION_TYPE_AAPT] = new CollectionModuleAAPT();
		$this->_arrDownloader[COLLECTION_TYPE_OPTUS] = new CollectionModuleOptus();

		// module config
		$this->_arrCollectionModule = $arrConfig['Define'];

		// Error array
		$this->_arrErrors = Array ();
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
		$arrDefine = $this->db->FetchClean("FileDownload");
		$arrDefine['CollectedOn'] = new MySQLFunction("NOW()");
		$insFileDownload = new StatementInsert("FileDownload", $arrDefine);

		$arrColumns = Array ();
		$arrColumns['Status'] = TRUE;
		$arrColumns['ImportedOn'] = New MySQLFunction("NOW()");
		$ubiFileDownload = new StatementUpdateById("FileDownload", $arrColumns);

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
				$this->_rptCollectionReport->AddMessageVariables(MSG_NO_COLLECTION_MODULE, Array (
					'<FriendlyName>' => $this->_arrCurrentModule['Name'],
					'<Type>' => $GLOBALS['CollectionType'][$this->_arrCurrentModule['Type']]
				), FALSE, TRUE);
				continue;
			}

			// connect
			if (!$dldDownloader->Connect($arrModule))
			{
				// Connection failed
				$this->_rptCollectionReport->AddMessageVariables(MSG_CONNECTION_FAILED, Array (
					'<FriendlyName>' => $this->_arrCurrentModule['Name'],
					'<Type>' => $this->_arrCurrentModule['Type']
				), FALSE, TRUE);
			}
			else
			{
				// Connection successful
				$this->_rptCollectionReport->AddMessageVariables(MSG_CONNECTED, Array (
					'<FriendlyName>' => $this->_arrCurrentModule['Name'],
					'<Type>' => $this->_arrCurrentModule['Type']
				), FALSE, TRUE);

				// Downloading from report message
				$this->_rptCollectionReport->AddMessageVariables(MSG_DOWNLOADING_FROM, Array (), FALSE, TRUE);
				//TODO!!!!
				/*foreach ($this->_arrCurrentModule['Dir'] as $strDir)
				{
					$this->_rptCollectionReport->AddMessageVariables(MSG_DIRS, Array('<Dir>' => $strDir));
				}*/

				// download
				$intCounter = 0;
				while ($strFile = $dldDownloader->Download(TEMP_DOWNLOAD_DIR))
				{
					$strFileLocation = TEMP_DOWNLOAD_DIR . $strFile;

					// Add to report that we're downloading the file
					$intFileSize = ceil(filesize($strFileLocation) / 1024);

					// set current download file
					$this->_arrCurrentDownloadFile = Array (
						"Location" => $strFileLocation,
						"Status" => RAWFILE_DOWNLOADED
					);

					// unzip files
					$arrFiles = $this->Unzip($strFileLocation); // always returns array of file locations (or FALSE)

					// Add to report that we've unzipped files (provided we actually unzipped)
					if (!$arrFiles || count($arrFiles) < 1)
					{
						$this->_rptCollectionReport->AddMessageVariables(MSG_GRABBING_FILE . MSG_BAD_FILE, Array (
							'<FileName>' => $strFileLocation,
							'<FileSize>' => $intFileSize
						), TRUE, FALSE);
					}
					elseif (count($arrFiles) > 1)
					{
						$this->_rptCollectionReport->AddMessageVariables(MSG_GRABBING_FILE, Array (
							'<FileName>' => $strFileLocation,
							'<FileSize>' => $intFileSize
						), FALSE, FALSE);
						$this->_rptCollectionReport->AddMessage(MSG_UNZIPPED_FILES, FALSE, FALSE);

						foreach ($arrFiles as $strFileName)
						{
							$this->_rptCollectionReport->AddMessageVariables(MSG_UNZIPPED_FILE, Array (
								'<FileName>' => $strFileName
							), FALSE, FALSE);
						}
					}

					// record download in db (FileDownload)
					$this->_arrCurrentDownloadFile['FileName'] = basename($strFileLocation);
					$this->_arrCurrentDownloadFile['Carrier'] = $this->_arrCurrentModule['Carrier'];
					$this->_arrCurrentDownloadFile['CollectedOn'] = New MySQLFunction("NOW()");
					if (($intId = $insFileDownload->Execute($this->_arrCurrentDownloadFile)) === FALSE)
					{

					}

					// set current file Id
					$this->_arrCurrentDownloadFile['Id'] = $intId;

					// import files
					$this->_arrErrors = array_merge($this->_arrErrors, $this->Import($arrFiles));

					// record download in db (FileDownload) - status has now been changed
					if ($ubiFileDownload->Execute($this->_arrCurrentDownloadFile) === FALSE)
					{

					}

					// increment counter
					$intCounter++;
				}

				// End the Report, and send it off
				$this->_rptCollectionReport->AddMessage(" * Imported $intCounter files in " . $this->Framework->LapWatch() . " seconds\n", TRUE, TRUE);

				// disconnect
				$dldDownloader->Disconnect();
			}
		}
		/*
		// Are the any errors?
		if ($this->_arrErrors)
		{
			// Generate Error Email
			$strContent = "Collection/Import errors for ".date("Y-m-d H:i:s")."\n\n";
			foreach ($this->_arrErrors as $strFile=>$arrErrors)
			{
				$strContent .= "\nFile: $strFile\n";
				foreach ($arrErrors as $strError)
				{
					$strContent .= "\t$strError\n";
				}
			}
			
			$arrHeaders = Array();
			$arrHeaders['From']		= 'collection@voiptelsystems.com.au';
			$arrHeaders['Subject']	= "Collection/Import errors for ".date("Y-m-d H:i:s");
				$mimMime = new Mail_mime("\n");
				$mimMime->setTXTBody($strContent);
			$strBody = $mimMime->get();
			$strHeaders = $mimMime->headers($arrHeaders);
				$emlMail =& Mail::factory('mail');
				
				// Send the email
				if (!$emlMail->send('rdavis@ybs.net.au', $strHeaders, $strBody))
				{
					$this->_rptCollectionReport->AddMessage("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
					continue;
				}
		}*/
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
	 * @return	array					Array of Errors
	 * 
	 * @method
	 */
	function Import($arrFiles)
	{
		// set status of downloaded file
		$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORTED;
		$this->_arrCurrentDownloadFile['ImportedOn'] = New MySQLFunction("NOW()");

		$arrError = Array ();

		$bolReturn = TRUE;
		if (!is_array($arrFiles) || count($arrFiles) < 1)
		{
			// set status of downloaded file
			$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORT_FAILED;
			// Add to report that import failed
			$this->_rptCollectionReport->AddMessageVariables(MSG_IMPORT_FAILED, Array (
				'<Reason>' => "Missing File(s)"
			));
			return FALSE;
		}
		else
		{
			foreach ($arrFiles as $strFileLocation => $strFileName)
			{
				// set current import file
				$this->_arrCurrentImportFile = Array (
					"Location" => $strFileLocation,
					"FileName" => $strFileName
				);

				// set status to imported (any errors will change this later)
				$this->_arrCurrentImportFile['Status'] = CDRFILE_WAITING;

				// copy file to final location
				if (!$strFileLocation = $this->_StoreImportFile())
				{
					$this->_rptCollectionReport->AddMessageVariables(MSG_MOVE_FILE_FAILED, Array (
						'<FileName>' => $strFileName
					));
					$arrError[$strFileName][] = "File could not be moved to final location";
				}

				// find file type
				if ($this->_FileType() == CDR_UNKNOWN)
				{
					$this->_rptCollectionReport->AddMessageVariables(MSG_UNKNOWN_FILETYPE, Array (
						'<FileName>' => $strFileName
					));
					$arrError[$strFileName][] = "Unknown File Type!";
				}

				// check uniqueness
				if (!$strHash = $this->_IsUnique())
				{
					//$this->_rptCollectionReport->AddMessageVariables(MSG_NOT_UNIQUE, Array('<FileName>' => $strFileName));
					$arrError[$strFileName][] = "File not unique!";
				}

				// save db record FileImport
				$this->_arrCurrentImportFile['Carrier'] = $this->_arrCurrentModule['Carrier'];
				$this->_arrCurrentImportFile['ImportedOn'] = new MySQLFunction("Now()");
				if (!$this->_insFileImport->Execute($this->_arrCurrentImportFile))
				{
					if ($this->_insFileImport->Error())
					{
					}

					$this->_arrCurrentDownloadFile['Status'] = RAWFILE_IMPORT_FAILED;
					$this->_rptCollectionReport->AddMessageVariables(MSG_IMPORT_FAILED, Array (
						'<Reason>' => "Database Failure"
					));
					$arrError[$strFileName][] = "Insert Query Failed!";
				}
			}
			// Add to report that we've imported
			$this->_rptCollectionReport->AddMessage(MSG_IMPORTED, FALSE, FALSE);
			return $arrError;
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
		$strFileName = $this->_arrCurrentImportFile['FileName'];
		$strFileLocation = $this->_arrCurrentImportFile['Location'];
		$arrCollectionModule = $this->_arrCurrentModule;
		$strUID = uniqid();
		// copy file to final location
		if (!copy($strFileLocation, $arrCollectionModule["FinalDir"] . $strUID . "_" . $strFileName))
		{
			// set status on error
			$this->_arrCurrentImportFile['Status'] = CDRFILE_MOVE_FAILED;
			return FALSE;
		}

		// set new file details
		$this->_arrCurrentImportFile['Location'] = $arrCollectionModule["FinalDir"] . $strUID . "_" . $strFileName;
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
		$strFileName = $this->_arrCurrentImportFile['FileName'];
		$arrFileType = $this->_arrCurrentModule["FileType"];

		// Find file type
		foreach ($arrFileType as $strRegEx => $intFileType)
		{
			if (preg_match($strRegEx . "misU", $strFileName))
			{
				// Set file type
				$this->_arrCurrentImportFile['FileType'] = $intFileType;

				// Set Imported Status based on File Type
				if (array_key_exists($intFileType, $GLOBALS['*arrConstant']['CDRType']))
				{
					$this->_arrCurrentImportFile['Status'] = CDRFILE_WAITING;
				}
				//elseif (array_key_exists($intFileType, $GLOBALS['*arrConstant']['ProvisioningType']))
				elseif ($intFileType >= 5000 && $intFileType < 6000)
				{
					$this->_arrCurrentImportFile['Status'] = PROVFILE_WAITING;
				}

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
		$arrWhere = Array ();
		$arrWhere['Carrier'] = $this->_arrCurrentModule['Carrier'];
		$arrWhere['FileName'] = $strFileName;
		$arrWhere['SHA1'] = $strHash;

		// Is this file type always unique?
		if ($this->_arrCurrentModule['AlwaysUnique'])
		{
			// Always unique
			return $strHash;
		}
		else
		{
			// Not always unique
			/*if (!$this->_selIsUnique->Execute($arrWhere))
			{
				if ($this->_selIsUnique->Error())
				{

				}

				// file is unique
				return $strHash;
			}*/
			
			$bolFileNameUnique	= !(bool)$this->_selFileNameUnique->Execute($arrWhere);
			$bolHashUnique		= !(bool)$this->_selHashUnique->Execute($arrWhere);
			
			if ($bolFileNameUnique)
			{
				// The File Name is unique, therefore the file is unique
				return $strHash;
			}
			elseif ($bolHashUnique)
			{
				// The File Name exists, but the Hash is unique
				$strContent	= "File '$strFileName' already exists in FileImport, but has a unique hash.";
				SendEmail("rdavis@ybs.net.au", "viXen Collection: Possible File Double-Up", $strContent);
				$this->_arrCurrentImportFile['Status'] = CDRFILE_NAME_NOT_UNIQUE;
				return FALSE;
			}
			
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
			return Array (
				$strFileLocation => $strFileName
			);
		}
		else
		{
			// set password
			if ($this->_arrCurrentModule['ZipPWord'])
			{
				$strPassword = "-P {$this->_arrCurrentModule['ZipPWord']}";
			}
			else
			{
				$strPassword = '';
			}

			// set and clean output dir
			$strOutputDir = UNZIP_DIR;
			CleanDir($strOutputDir);

			// unzip files
			$strCommand = "unzip -q $strPassword $strFileLocation -d $strOutputDir";
			exec($strCommand);

			// get list of files (full path)
			$arrFileList = glob("$strOutputDir*");

			// build output
			$arrFiles = Array ();
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
}
?>
