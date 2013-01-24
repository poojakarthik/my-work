<?php
class File_Import extends ORM_Cached {
	protected $_strTableName = "FileImport";
	protected static $_strStaticTableName = "FileImport";

	public function __construct($arrProperties=array(), $bolLoadById=false) {
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}

	public static function import($strFilePath, $intFileType, $intCarrier, $strNewFileName=null, $strUniqueness = "FileName = <FileName> AND SHA1 = <SHA1>", $intFileDownload=null) {
		$objFileImport = new File_Import();

		// Set default Properties
		$objFileImport->Status = FILE_COLLECTED;
		$objFileImport->compression_algorithm_id = COMPRESSION_ALGORITHM_NONE;

		// Determine File Type
		if (GetConstantName($intFileType, 'resource_type') === false) {
			// Unknown File Type
			$objFileImport->Status = FILE_UNKNOWN_TYPE;
		} else {
			// Copy to final location
			$strDestination = FILES_BASE_PATH."import/".$intCarrier.'/'.GetConstantName($intFileType, 'resource_type').'/';
			@mkdir($strDestination, 0777, true);
			if (!$strNewFileName) {
				$strNewFileName = basename($strFilePath);
				$strNewFileName .= date("_Ymdhis");
			}
			$strDestination .= $strNewFileName;
			if (!copy($strFilePath, $strDestination)) {
				// Unable to copy
				$objFileImport->Status = FILE_MOVE_FAILED;
			} else {
				// Check uniqueness
				$arrWhere = array();
				$arrWhere['SHA1'] = sha1_file($strDestination);
				$arrWhere['FileName'] = basename($strDestination);
				$selFileUnique = new StatementSelect("FileImport", "*", $strUniqueness);
				if ($selFileUnique->Execute($arrWhere)) {
					// Not Unique
					$objFileImport->Status = FILE_NOT_UNIQUE;
				}

				// Compress the Imported File using the BZ2 algorithm
				if (file_put_contents("compress.bzip2://{$strDestination}.bz2", file_get_contents($strDestination))) {
					// Success, remove the uncompressed file
					unlink($strDestination);

					$strDestination .= '.bz2';
					$objFileImport->compression_algorithm_id = COMPRESSION_ALGORITHM_BZIP2;
				} else {
					// Failure, keep the old file, and continue as if nothing went wrong
					$objFileImport->compression_algorithm_id = COMPRESSION_ALGORITHM_NONE;
				}
			}
		}

		// Insert into FileImport
		$objFileImport->FileName = $strNewFileName;//basename($strFilePath);
		$objFileImport->Location = ($strDestination) ? $strDestination : $strFilePath;
		$objFileImport->Carrier = $intCarrier;
		$objFileImport->ImportedOn = date("Y-m-d H:i:s");
		$objFileImport->FileType = $intFileType;
		$objFileImport->SHA1 = sha1_file($strFilePath);
		$objFileImport->file_download = $intFileDownload;
		$objFileImport->_save();

		// Return the File_Import object
		return $objFileImport;
	}

	public function fopen($strMode='r') {
		$this->_resFile = @fopen($this->getPHPStreamWrapper().$this->Location, $strMode);
		return $this->_resFile;
	}

	public function getPHPStreamWrapper() {
		$arrCompressionAlgorithm = Compression_Algorithm::getForId($this->compression_algorithm_id, true);
		$strStreamWrapper = ($arrCompressionAlgorithm['php_stream_wrapper']) ? $arrCompressionAlgorithm['php_stream_wrapper'] : '';
		return $strStreamWrapper;
	}

	public function getWrappedLocation() {
		return $this->getPHPStreamWrapper().$this->Location;
	}

	public function save() {
		if ($this->id) {
			parent::save();
		} else {
			throw new Exception("Unable to Insert File_Import record from public scope; Use File_Import::import() instead!");
		}
	}

	protected function _save() {
		// Pass through to ORM's save() function
		parent::save();
	}

	protected static function getCacheName() {
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName)) {
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize() {
		return 100;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	// START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects) {
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false) {
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false) {
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function getForFileName($sFileName) {
		$oSelect = self::_preparedStatement('selByFileName');
		$oSelect->Execute(array('file_name' => $sFileName));
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
		foreach ($aResults as $aResult) {
			$aObjects[]= new self($aResult);
		}
		return $aObjects;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	// END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = Array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selByFileName':
					$arrPreparedStatements[$strStatement] = new StatementSelect( "FileImport", "*", "FileName = <file_name>");
					break;
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect( "FileImport", "*", "Id = <Id>", null, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "name ASC");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement] = new StatementInsert("FileImport");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement] = new StatementUpdateById("FileImport");
					break;

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}