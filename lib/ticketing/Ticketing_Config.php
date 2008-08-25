<?php

class Ticketing_Config
{
	private static $objInstance = NULL;
	private $arrProperties = array();
	private $_saved = FALSE;
	
	protected function __construct()
	{
		// Load the config from the database
		$arrColumns = self::getColumns();
		$selConfig = new StatementSelect('ticketing_config', $arrColumns, NULL, 'id DESC', '0,1');
		if (!($outcome = $selConfig->Execute()))
		{
			throw new Exception("Failed to load ticketing configuration. " . ($outcome === FALSE ? $qryQuery->Error() : 'Configuration not defined.'));
		}
		if ($outcome)
		{
			$this->arrProperties = $selConfig->Fetch();
			$this->_saved = TRUE;
		}
		else
		{
			$this->protocol = 'XML';
			$this->_saved = FALSE;
			$this->save();
		}
	}

	protected static function getColumns()
	{
		return array(
			'id', 
			'protocol', 
			'host',
			'port', 
			'username', 
			'password',
		);
	}

	public static function load()
	{
		if (self::$objInstance == NULL)
		{
			self::$objInstance = new Ticketing_Config();
		}
		return self::$objInstance;
	}

	public function getSourceDirectory($validate=TRUE)
	{
		return ($validate ? $this->getRealDir($this->host) : $this->host);
	}

	public function setSourceDirectory($path)
	{
		$this->host = $path;
		$this->_saved = FALSE;
	}

	public function getBackupDirectory($validate=TRUE)
	{
		return ($validate ? $this->getRealDir($this->username) : $this->username);
	}

	public function setBackupDirectory($path)
	{
		$this->username = $path;
		$this->_saved = FALSE;
	}

	public function getJunkDirectory($validate=TRUE)
	{
		return ($validate ? $this->getRealDir($this->password) : $this->password);
	}

	public function setJunkDirectory($path)
	{
		$this->password = $path;
		$this->_saved = FALSE;
	}

	private function getRealDir($path)
	{
		if ($path && file_exists($path) && is_dir($path))
		{
			$path = realpath($path);
			if ($path[strlen($path) - 1] != DIRECTORY_SEPARATOR)
			{
				$path .= DIRECTORY_SEPARATOR;
			}
		}
		else
		{
			$path = NULL;
		}
		return $path;
	}

	public function __get($property)
	{
		if (array_key_exists($property, $this->arrProperties))
		{
			return $this->arrProperties[$property];
		}
		return NULL;
	}

	public function __set($property, $value)
	{
		if (array_key_exists($property, $this->arrProperties))
		{
			$this->arrProperties[$property] = $value;
		}
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach ($arrColumns as $strColumn)
		{
			if ($strColumn == 'id') 
			{
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	}

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = $this->getValuesToSave();

		// No id means that this must be a new record
		if (!$this->id)
		{
			$statement = new StatementInsert(strtolower(__CLASS__), $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById(strtolower(__CLASS__), $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save ticketing config details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;

		return TRUE;
	}

}


?>