<?php

class Ticketing_Config extends ORM_Cached {
	protected $_strTableName = "ticketing_config";
	protected static $_strStaticTableName = "ticketing_config";
	
	public function getSourceDirectory($bValidate=true) {
		return ($bValidate ? $this->_getRealDir($this->host) : $this->host);
	}

	public function setSourceDirectory($sPath) {
		$this->host = $sPath;
	}

	public function getBackupDirectory($bValidate=true) {
		return ($bValidate ? $this->_getRealDir($this->username) : $this->username);
	}

	public function setBackupDirectory($sPath) {
		$this->username = $sPath;
	}

	public function getJunkDirectory($bValidate=true) {
		return ($bValidate ? $this->_getRealDir($this->password) : $this->password);
	}

	public function setJunkDirectory($sPath) {
		$this->password = $sPath;
	}

	private function _getRealDir($sPath) {
		if ($sPath && file_exists($sPath) && is_dir($sPath)) {
			$sPath = realpath($sPath);
			if ($sPath[strlen($sPath) - 1] != '/') {
				$sPath .= '/';
			}
		} else {
			$sPath = null;
		}
		return $sPath;
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
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mObjects) {
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false) {
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false) {
		return parent::getAll($bForceReload, __CLASS__);
	}
	
	public static function importResult($aResultSet) {
		return parent::importResult($aResultSet, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$sStatement						Name of the statement
	 *
	 * @return	Statement									The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($sStatement) {
		static $arrPreparedStatements = Array();
		if (isset($arrPreparedStatements[$sStatement])) {
			return $arrPreparedStatements[$sStatement];
		} else {
			switch ($sStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement] = new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$sStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement] = new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement] = new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}

/*
class Ticketing_Config {
	private static $objInstance = NULL;
	private $arrProperties = array();
	private $_saved = FALSE;
	
	protected function __construct() {
		// Load the config from the database
		$arrColumns = self::getColumns();
		$selConfig = new StatementSelect('ticketing_config', $arrColumns, NULL, 'id DESC', '0,1');
		if (!($outcome = $selConfig->Execute())) {
			throw new Exception_Database("Failed to load ticketing configuration. " . ($outcome === FALSE ? $qryQuery->Error() : 'Configuration not defined.'));
		}

		if ($outcome) {
			$this->arrProperties = $selConfig->Fetch();
			$this->_saved = TRUE;
		}
	}

	protected static function getColumns() {
		return array(
			'id', 
			'protocol', 
			'host',
			'port', 
			'username', 
			'password', 
			'use_ssl', 
			'archive_folder_name'
		);
	}

	public static function load() {
		if (self::$objInstance == NULL) {
			self::$objInstance = new Ticketing_Config();
		}
		return self::$objInstance;
	}

	public function getSourceDirectory($validate=TRUE) {
		return ($validate ? $this->getRealDir($this->host) : $this->host);
	}

	public function setSourceDirectory($path) {
		$this->host = $path;
		$this->_saved = FALSE;
	}

	public function getBackupDirectory($validate=TRUE) {
		return ($validate ? $this->getRealDir($this->username) : $this->username);
	}

	public function setBackupDirectory($path) {
		$this->username = $path;
		$this->_saved = FALSE;
	}

	public function getJunkDirectory($validate=TRUE) {
		return ($validate ? $this->getRealDir($this->password) : $this->password);
	}

	public function setJunkDirectory($path) {
		$this->password = $path;
		$this->_saved = FALSE;
	}

	private function getRealDir($path) {
		if ($path && file_exists($path) && is_dir($path)) {
			$path = realpath($path);
			if ($path[strlen($path) - 1] != '/') {
				$path .= '/';
			}
		} else {
			$path = NULL;
		}
		return $path;
	}

	public function __get($property) {
		if (array_key_exists($property, $this->arrProperties)) {
			return $this->arrProperties[$property];
		}
		return NULL;
	}

	public function __set($property, $value) {
		if (array_key_exists($property, $this->arrProperties)) {
			$this->arrProperties[$property] = $value;
		}
	}

	protected function getValuesToSave() {
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach ($arrColumns as $strColumn) {
			if ($strColumn == 'id')  {
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	}

	public function save() {
		if ($this->_saved) {
			// Nothing to save
			return TRUE;
		}
		$arrValues = $this->getValuesToSave();

		// No id means that this must be a new record
		if (!$this->id) {
			$statement = new StatementInsert(strtolower(__CLASS__), $arrValues);
		} else {
			// This must be an update
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById(strtolower(__CLASS__), $arrValues);
		}

		if (($outcome = $statement->Execute($arrValues)) === FALSE) {
			throw new Exception_Database('Failed to save ticketing config details: ' . $statement->Error());
		}

		if (!$this->id) {
			$this->id = $outcome;
		}

		$this->_saved = TRUE;
		return TRUE;
	}
}
*/
?>