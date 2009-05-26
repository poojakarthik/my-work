<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_base
//----------------------------------------------------------------------------//
/**
 * module_base
 *
 * Base Collection Module
 *
 * Base Collection Module
 *
 * @file		module_base.php
 * @language	PHP
 * @package		collection
 * @author		Rich Davis
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleBase
//----------------------------------------------------------------------------//
/**
 * CollectionModuleBase
 *
 * Base Collection Module
 *
 * Base Collection Module
 *
 *
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleBase
 */
 abstract class CollectionModuleBase extends CarrierModule
 {
 	protected	$_selFileExists;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleBase
	 *
	 * Constructor for CollectionModuleBase
	 *
	 * @return		CollectionModuleBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier, MODULE_TYPE_COLLECTION);
 		
 		$this->_selFileDownloaded	= new StatementSelect("FileDownload", "Id", "FileName = <FileName>");
 		$this->_selFileImported		= new StatementSelect("FileImport", "Id", "FileName = <FileName>");
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
 	abstract function Connect();
 	
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
 	abstract function Disconnect();
 	
  	//------------------------------------------------------------------------//
	// Download
	//------------------------------------------------------------------------//
	/**
	 * Download()
	 *
	 * Downloads next file from the Source
	 *
	 * Downloads next file from the Source, returning the FileName or FALSE if no more files
	 *
	 * @return		mixed		String of Filename or FALSE if there is no next file
	 *
	 * @method
	 */
 	abstract function Download($strDestination);
 	
  	//------------------------------------------------------------------------//
	// GetFileType
	//------------------------------------------------------------------------//
	/**
	 * GetFileType()
	 *
	 * Determines the FileImport type for a given file
	 *
	 * Determines the FileImport type for a given file
	 * 
	 * @param	array	$arrDownloadFile				FileDownload properties
	 * 
	 * @return	mixed									array: FileImport Type; NULL: Unrecognised type
	 *
	 * @method
	 */
	public function GetFileType($arrDownloadFile)
	{
		// Has this file been extracted from a downloaded archive?
		if ($arrDownloadFile['ArchiveParent'])
		{
			// Get the Download Path relative to the archive extraction directory
			$strRelativeDir	= str_replace($arrDownloadFile['ExtractionDir'], '', $arrDownloadFile['LocalPath']);
			$arrRelativeDir	= explode($arrDownloadFile['ExtractionDir'], dirname($arrDownloadFile['LocalPath']));
			$strRelativeDir	= '/'.trim(end($arrRelativeDir), '/').'/';
			
			foreach ($arrDownloadFile['ArchiveParent']['FileType']['PathDefine'] as $intFileType=>$arrFileType)
			{
				// Does this file match our REGEX?
				if (preg_match($arrFileType['Regex'], trim(basename($arrDownloadFile['LocalPath']))))
				{
					// We have a match
					$arrFileType['FileImportType']	= $intFileType; 
					return $arrFileType;
				}
			}
			
			// No match - this File Type is unrecognised
			return NULL;
		}
		else
		{
			// The File Type for this file is already defined
			return $arrDownloadFile['FileType'];
		}
	}
	
	/**
	 * isDownloadUnique()
	 *
	 * Checks whether a given Filename is unique in the DB for this Carrier
	 * 
	 * @param	string	$strFilename					Filename to check
	 * 
	 * @return	mixed									array: FileImport Type; NULL: Unrecognised type
	 *
	 * @method
	 */
	public function isDownloadUnique($strFilename)
	{
		CliEcho("Checking '{$strFilename}' for uniqueness with Carrier #".$this->GetCarrier());
		
		static	$selFileDownloadUnique;
		$selFileDownloadUnique	= ($selFileDownloadUnique) ? $selFileDownloadUnique : new StatementSelect("FileDownload", "Id", "Carrier = <carrier_id> AND FileName = <filename>", null, 1);
		
		$mixResult	= $selFileDownloadUnique->Execute(array('carrier_id'=>$this->GetCarrier(), 'filename'=>$strFilename));
		if ($mixResult === false)
		{
			throw new Exception($selFileDownloadUnique->Error());
		}
		elseif ($mixResult)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}

?>
