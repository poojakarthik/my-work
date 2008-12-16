<?php
//----------------------------------------------------------------------------//
// File_Import
//----------------------------------------------------------------------------//
/**
 * File_Import
 *
 * Models a record of the FileImport table
 *
 * Models a record of the FileImport table
 *
 * @class	File_Import
 */
class File_Import extends ORM
{
	protected	$_strTableName	= "FileImport";
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the object with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * import()
	 *
	 * Imports a File into Flex
	 * 
	 * @return	File_Import							Imported File
	 *
	 * @method
	 */
	public static function import($strFilePath, $intFileType, $intCarrier, $strUniqueness = "FileName = <FileName> AND SHA1 = <SHA1>", $intFileDownload = NULL)
	{
		$objFileImport	= new File_Import();
		
		// Set default Properties
		$objFileImport->Status						= FILE_COLLECTED;
		$objFileImport->compression_algorithm_id	= COMPRESSION_ALGORITHM_NONE;
		
		// Determine File Type
		if (GetConstantName($intFileType, 'resource_type') === FALSE)
		{
			// Unknown File Type
			$objFileImport->Status	= FILE_UNKNOWN_TYPE;
		}
		else
		{
			// Copy to final location
			$strDestination	= FILES_BASE_PATH."import/".GetConstantDescription($intCarrier, 'Carrier').'/'.GetConstantName($intFileType, 'FileImport').'/';
			@mkdir($strDestination, 0777, TRUE);
			$strNewFileName	= basename($strFilePath);
			$strNewFileName	.= date("_Ymdhis");
			$strDestination	.= $strNewFileName;
			if (!copy($strFilePath, $strDestination))
			{
				// Unable to copy
				$objFileImport->Status	= FILE_MOVE_FAILED;
			}
			else
			{
				// Check uniqueness
				$arrWhere				= array();
				$arrWhere['SHA1']		= sha1_file($strFilePath);
				$arrWhere['FileName']	= basename($strFilePath);
				$selFileUnique	= new StatementSelect("FileImport", "*", $strUniqueness);
				if ($selFileUnique->Execute($arrWhere))
				{
					// Not Unique
					$objFileImport->Status	= FILE_NOT_UNIQUE;
				}
			}
			
			// Compress the Imported File using the BZ2 algorithm
			if (file_put_contents("compress.bzip2://{$strDestination}.bz2", file_get_contents($strDestination)))
			{
				// Success, remove the uncompressed file
				unlink($strDestination);
				
				$strDestination								.= '.bz2';
				$objFileImport->compression_algorithm_id	= COMPRESSION_ALGORITHM_BZIP2;
			}
			else
			{
				// Failure, keep the old file, and continue as if nothing went wrong
				$objFileImport->compression_algorithm_id	= COMPRESSION_ALGORITHM_NONE;
			}
		}
		
		// Insert into FileImport
		$objFileImport->FileName		= basename($strFilePath);
		$objFileImport->Location		= ($strDestination) ? $strDestination : $strFilePath;
		$objFileImport->Carrier			= $intCarrier;
		$objFileImport->ImportedOn		= date("Y-m-d H:i:s");
		$objFileImport->FileType		= $intFileType;
		$objFileImport->SHA1			= sha1_file($strFilePath);
		$objFileImport->file_download	= $intFileDownload;
		$objFileImport->save();
	}
	
	/**
	 * fopen()
	 *
	 * Opens this file and returns a PHP file resource for use with the f*() functions
	 * 
	 * @return	void
	 *
	 * @method
	 */
	public function fopen($strMode='r')
	{
		$arrCompressionAlgorithm	= Compression_Algorithm::getForId($this->compression_algorithm_id, true);
		$strStreamWrapper			= ($arrCompressionAlgorithm['php_stream_wrapper']) ? $arrCompressionAlgorithm['php_stream_wrapper'] : '';
		$this->_resFile				= @fopen($strStreamWrapper.$this->Location, $strMode);
		return $this->_resFile;
	}
	
	/**
	 * getPHPStreamWrapper()
	 *
	 * Gets the PHP Stream Wrapper for this File
	 * 
	 * @return	string
	 *
	 * @method
	 */
	public function getPHPStreamWrapper()
	{
		
	}
	
	/**
	 * save()
	 *
	 * Updates the Record for this instance
	 * 
	 * @return	void
	 *
	 * @method
	 */
	public function save()
	{
		if ($this->id)
		{
			parent::save();
		}
		else
		{
			throw new Exception("Unable to Insert File_Import record from public scope; Use File_Import::import() instead!");
		}
	}
	
	/**
	 * _save()
	 *
	 * Inserts or Updates the Record for this instance
	 * 
	 * @return	void
	 *
	 * @method
	 */
	protected function _save()
	{
		// Pass through to ORM's save() function
		parent::save();
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"FileImport", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("FileImport");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("FileImport");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>