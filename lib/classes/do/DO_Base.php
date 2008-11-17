<?php

abstract class DO_Base 
{

	protected $properties = array();

	protected $_bolSaved = false;

	protected $_bolUnsavedChanges = false;

	abstract protected function setDefaultProperties();

	abstract static function getPropertyNames();

	abstract static function getDataSourcePropertyMappings();

	abstract protected static function getDataSourceObjectName();

	abstract protected static function getDataSourceIdName();

	abstract function getValueForDataSource($propertyName, $bolQuoted=false);

	abstract protected function setValueFromDataSource($propertyName, $value);

	abstract static function getIdName();
	
	abstract protected function _isValidValue($property, $value);
	
	abstract function getObjectLabel();

	abstract function getPropertyLabel($property);

	abstract static function getPropertyDataSourceName($property);

	public function __construct($initialProperties=null, $fromDataSource=false)
	{
		$this->setDefaultProperties();
		if (is_array($initialProperties))
		{
			foreach ($initialProperties as $property => $value)
			{
				if ($fromDataSource)
				{
					$this->setValueFromDataSource($property, $value);
				}
				else
				{
					$this->{$property} = $value;
				}
			}
			$this->setSaved($fromDataSource);
		}
	}
	
	public function setSaved($bolSaved)
	{
		$this->_bolSaved = $bolSaved;
	}
	
	public function _populateWithValues($savedProperties)
	{
		
	}
	
/*
 *  These functions want to be here, but can't be until PHP 5.3.0.
 *  Until then there will need to be copies (slighly modified) of each
 *  in the DO_[DataSource]_Base_[ObjectType] class.
 
	protected static function getFor($strWhere=null, $multiple=false, $strSort=null, $strLimit=0, $strOffset=0)
	{
		$tableName = self::getDataSourceObjectName();
		
		$arrDsProps = self::getDataSourcePropertyMappings();
		$arrFields = array();
		foreach($arrDsProps as $dsName => $propName)
		{
			$arrFields[] = $tableName.'.'.$dsName . ' AS "' . $propName . '"';
		}
		$strFields = implode(', ', $arrFields);

		$strSQL = "SELECT $strFields FROM $tableName ";
		
		if ($strWhere)
		{
			$strSQL .= " WHERE $strWhere";
		}
		
		if ($strSort)
		{
			$strSQL .= " ORDER BY $strSort";
		}
		
		if ($strLimit)
		{
			$strSQL .= " LIMIT " . intval($strOffset) . ", " . intval($strLimit);
		}

		$dataSource = self::getDataSource();

		if (PEAR::isError($results = $dataSource->query($strSQL)))
		{
			throw new Exception('Failed to load records for ' . __CLASS__ . ' :: ' . $results->getMessage());
		}

		$details = $results->fetchAll(MDB2_FETCHMODE_ASSOC);

		$arrInstances = array();
		$matched = false;
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Correspondance($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
			$matched = true;
		}
		
		if (!$matched && !$multiple)
		{
			return null;
		}
		
		return $arrInstances;
	}
	
	protected static function countFor($strWhere, $strLimit=NULL, $strOffset=0)
	{
		$tableName = self::getDataSourceObjectName();
		
		$strSQL = "SELECT COUNT(" . self::getDataSourceIdName() . ") AS \"count\" FROM $tableName ";
		
		if ($strWhere)
		{
			$strSQL .= " WHERE $strWhere";
		}
		
		if ($strLimit)
		{
			$strSQL .= " LIMIT " . intval($strOffset) . ", " . intval($strLimit);
		}

		$dataSource = self::getDataSource();

		if (PEAR::isError($results = $dataSource->query($strSQL)))
		{
			throw new Exception('Failed to count records for ' . __CLASS__ . ' :: ' . $results->getMessage());
		}

		return $results->fetchOne();
	}
	
	public static function getForId($id)
	{
		return self::getFor(self::getDataSourceIdName() . " = " . intval($id), false, null, 0, 0);
	}
	
	public static function getDataSource()
	{
		return Data_Source::get(self::getDataSourceName());
	}
*/	
	protected final function _getValuesForDataSource()
	{
		$dsProps = $this->getDataSourcePropertyMappings();
		$arrValues = array();
		foreach ($dsProps as $dsName => $prop)
		{
			$arrValues[$dsName] = $this->getValueForDataSource($prop, true);
		}
		return $arrValues;
	}
	
	public function save()
	{
		if ($this->isSaved())
		{
			// Nothing to save
			return true;
		}
		
		$dataSource = $this->getDataSource();
		$arrValues = $this->_getValuesForDataSource();

		$idName = $this->getIdName(); // This is a cludge! Really want to use static:: instead of $this->, but that is a PHP 5.3.0+ feature :(

		// WIP This assumes that there is an auto-incrementing primary key

		// No id means that this must be a new record
		if ($this->{$idName} === null)
		{
			unset($arrValues[$this->getDataSourceIdName()]); // This is a cludge! Really want to use static:: instead of $this->, but that is a PHP 5.3.0+ feature :(
			$cols = implode(', ', array_keys($arrValues));
			$vals = implode(', ', $arrValues);
			$strSQL = 'INSERT INTO ' . $this->getDataSourceObjectName() . ' (' .$cols. ') VALUES (' .$vals. ')';
			//echo "/*\n\n$strSQL\n\n*/";
		}
		// This must be an update
		else
		{
			unset($arrValues[$this->getDataSourceIdName()]);
			$updates = array();
			foreach ($arrValues as $field => $value)
			{
				$updates[] = "$field = $value";
			}
			$updates = implode(', ', $updates);
			$strSQL = 'UPDATE ' . $this->getDataSourceObjectName() . ' SET ' . $updates . ' WHERE ' . $this->getDataSourceIdName() . ' = ' . $this->{$idName};
		}

		if (PEAR::isError($outcome = $dataSource->query($strSQL)))
		{
			throw new Exception('Failed to save ' . __CLASS__ . ' details: ' . $outcome->getMessage());
		}
		if ($this->{$idName} === null)
		{
			$this->{$idName} = $dataSource->lastInsertID($this->getDataSourceObjectName(), $idName);
		}
		$this->setSaved(true);

		return true;
	}

	public function delete()
	{
		// WIP - This should be changed to work with composite primary keys
		// We can only delete a record if it has a primary key (i.e. if it exists in the database already)
		$idName = $this->getIdName();
		if ($this->{$idName} === null) return;
		
		$table = $this->getDataSourceObjectName();
		
		$strSQL = 'DELETE FROM ' . $this->getDataSourceObjectName() . ' WHERE ' . $this->getDataSourceIdName() . ' = ' . $this->{$idName};
		
		$dataSource = $this->getDataSource();
		
		if (PEAR::isError($result = $dataSource->query($strSQL)))
		{
			 // This is a cludge! Really want to use static::getDataSourceName instead of $this->getDataSourceName, but that is a PHP 5.3.0+ feature :(
			 // well, actually we want to revisit this so that we can change the dataSourceName on the instance!
			throw new Exception("Failed to delete from ".$this->getDataSourceName().".$table where $idName = " . $this->{$idName} . ' :: ' . $result->getMessage());
		}

		unset($this->{$idName});

		$this->setSaved(false);
	}
	
	public function isValid($bolThrowException=false)
	{
		$props = $this->getPropertyNames();
		$errors = array();
		for ($i = 0, $l = count($props); $i < $l; $i++)
		{
			if (!$this->_isValidValue($props[$i], $this->properties[$props[$i]]))
			{
				if (!$bolThrowException) 
				{
					return false;
				}
				$errors[] = "Invalid value specified for '" . $this->getPropertyLabel($props[$i]) . "'.";// . $this->properties[$props[$i]];
			}
		}
		if (count($errors))
		{
			throw new DO_Validation_Exception($this->getObjectLabel() . " is invalid:\n\t" . implode("\n\t", $errors));
		}
		return true;
	}

	public function isSaved()
	{
		return $this->_bolSaved;
	}
	
	public function hasUnsavedChanges()
	{
		return !$this->_bolUnsavedChanges;
	}
	
	public function __get($name)
	{
		// Return an existing value if there is one...
		if (array_key_exists($name, $this->properties))
		{
			return $this->properties[$name];
		}
		// ... otherwise return null
		return null;
	}
	
	public function __set($name, $value)
	{
		// Get the current value of the property
		$currentValue = $this->__get($name);
		
		// If this is a data source property...
		$propertyNames = $this->getDataSourcePropertyMappings();
		if (array_search($name, $propertyNames) !== false)
		{
			// ... record whether or not it has changed
			$this->_bolUnsavedChanges = $this->_bolUnsavedChanges && ($currentValue === $value);
		}

		// Store the new value of the property
		$this->properties[$name] = $value;
	}
	
	public function __isset($name)
	{
		return array_key_exists($name, $this->properties);
	}
	
	public function __unset($name)
	{
		if (array_key_exists($name, $this->properties))
		{
			unset($this->properties[$name]);
		}
	}

	
	// Not yet implemented - may do stuff in future though...
	/*
	public function __call($name, $args)
	{
		
	}
	*/
}


class DO_Validation_Exception extends Exception
{
	
}

?>
