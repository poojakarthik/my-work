<?php

//
// TODO: Remove the following once the next version of MDB2_Driver_pgsql is available.
// 
// The modeler code requires a patch that is not available in the latest
// version of the MDB2_Driver_pgsql PEAR library (version 1.5.0b1 (beta)).
// The bug has been fixed in the PEAR repository and should be in the 
// next release. Once this has been made available, the following line
// (and the file it points to) should be removed.
//
// Hadrian 28/10/2008
//
require_once "MDB2_Driver_Reverse_pgsql.php";

class DO_Modeler
{
	public $arrDataSourceConfig = null;
	public $strDataSourceName = null;
	
	public $arrDataSources = array();
	public $arrDataObjects = array();
	
	public $doDatabase = null;
	
	public static function getModelerForDataSource($strDataSourceName)
	{
		$strDataSourceClassName = "DO_Modeler_" . self::codifyName($strDataSourceName);
		if (file_exists(dirname(__FILE__)."/$strDataSourceClassName.php"))
		{
			return new $strDataSourceClassName($strDataSourceName);
		}
		else
		{
			return new DO_Modeler($strDataSourceName);
		} 
	}
	
	public function __construct($strDataSourceName)
	{
		$this->strDataSourceName = $strDataSourceName;
	}
	
	public function load()
	{
		$unwantedPortabilityOptions = MDB2_PORTABILITY_FIX_CASE;

		$options = array(
			'debug'       => 2,
			'portability' => ((MDB2_PORTABILITY_ALL | $unwantedPortabilityOptions) ^ $unwantedPortabilityOptions),
			'use_transactions' => TRUE,
		);

		$dbConnection = Data_Source::get($this->strDataSourceName, true, $options);

		if (PEAR::isError($dbConnection))
		{
			throw new Exception("Failed to connect to " . $this->arrDataSourceConfig['phptype'] . " database '" . $this->arrDataSourceConfig['database'] . "' on " . $this->arrDataSourceConfig['hostspec'] . " using username '" . $this->arrDataSourceConfig['username'] . "': " . $dbConnection->getMessage());
		}

		$this->doDatabase = $this->getDoDatabase($this->strDataSourceName, $dbConnection);
		$this->doDatabase->load();
	}
	
	public function getDoDatabase($strDataSourceName, $dbConnection)
	{
		return new DO_Database($strDataSourceName, $dbConnection);
	}
	
	public function prepare()
	{
		$this->doDatabase->prepare();
	}
	
	public function generateModelFiles($classesDir)
	{
		$this->doDatabase->generateModelFiles($classesDir);
	}

	public static function codifyName($str, $bolClassName=false)
	{
		$str = str_replace('_', ' ', $str);
		$str = preg_replace(array(
				"/([A-Z]+)([A-Z]{1,1}[a-z]+)/",
				"/([A-Za-z]+)([0-9]+)/",
				"/([0-9]+)([A-Za-z]+)/",
				"/([a-z]+)([A-Z]+)/",
			), "\${1} \${2}", $str);
		$str = str_replace(' ', '', ucwords(strtolower($str)));
		if ($str) $str[0] = ($bolClassName ? strtoupper($str[0]) : strtolower($str[0]));
		return $str;
	}

	public static function labelifyName($str)
	{
		$str = str_replace('_', ' ', $str);
		$str = preg_replace(array(
				"/([A-Z]+)([A-Z]{1,1}[a-z]+)/",
				"/([A-Za-z]+)([0-9]+)/",
				"/([0-9]+)([A-Za-z]+)/",
				"/([a-z]+)([A-Z]+)/",
			), "\${1} \${2}", $str);
		return ucwords($str);
	}
}

class DO_Database
{
	public $dbConnection = null;
	public $dataSourceName = null;
	
	public $arrTables = array();
	
	public function __construct($dataSourceName, $dbConnection)
	{
		$this->dataSourceName = $dataSourceName;
		$this->dbConnection = $dbConnection;
	}
	
	public function getDBConnection()
	{
		return $this->dbConnection;
	}
	
	public function getDataSourceName()
	{
		return $this->dataSourceName;
	}
	
	public function getDataSourcePathName()
	{
		return strtolower($this->getDataSourceClassName());
	}
	
	public function getDataSourceClassName()
	{
		return DO_Modeler::codifyName($this->getDataSourceName(), true);
	}
	
	public function load()
	{
		// List the tables in the database
		$this->dbConnection->loadModule('Manager');
		$this->dbConnection->loadModule('Reverse', null, true);
		
		$tables = $this->dbConnection->manager->listTables();

		if (PEAR::isError($tables))
		{
			throw new Exception(__CLASS__ . ' Failed to list tables in database. ' . $tables->getMessage());
		}

		foreach ($tables as $tableName)
		{
			$className = DO_Modeler::codifyName($tableName, true);
			$this->arrTables[$tableName] = $this->getDoTable($tableName, $className);
			$this->arrTables[$tableName]->load();
		}
	}
	
	public function getDoTable($tableName, $className)
	{
		return new DO_Table($this, $tableName, $className);
	}
	
	public function prepare()
	{
		foreach ($this->arrTables as $tableName => $doTable)
		{
			$this->arrTables[$tableName]->prepare();
		}
	}
	
	public function generateModelFiles($classesDir)
	{
		foreach ($this->arrTables as $tableName => $doTable)
		{
			$this->arrTables[$tableName]->generateModelFiles($classesDir);
		}
	}
}

//
// Each table will be mapped to a single class in the data access layer.
//
// Tables with the same names accross multiple databases (e.g. record_type) should be mapped to from the same class.
//
// Each datasource that supports a given table should create its own interface for mapping between the data object
// and the table in the database.
//
class DO_Table
{
	public $doDatabase = null;
	public $tableName = null;
	public $className = null;
	
	public $fields = array();
	public $propertyFields = array();
	public $fieldProperties = array();
	public $pk = array();
	public $fks = array();
	public $uks = array();
	
	public $intMaxPropertyNameLength = 0;
	public $intMaxDataSourceNameLength = 0;

	public static $tables = array();

	public function __construct($doDatabase, $tableName, $className)
	{
		$this->doDatabase = $doDatabase;
		$this->tableName = $tableName;
		$this->className = $className;
		self::$tables[$tableName] = $this;
	}

	public function getDataSourceName()
	{
		return $this->doDatabase->getDataSourceName();
	}
	
	public function getObjectLabel()
	{
		return DO_Modeler::labelifyName($this->tableName);
	}
	
	public static function getTable($tableName)
	{
		return self::$tables[$tableName];
	}
		
	public function load()
	{
		$constraints = $this->doDatabase->getDBConnection()->manager->listTableConstraints($this->tableName);

		if (PEAR::isError($constraints))
		{
			throw new Exception(__CLASS__ . ' Failed to list constraints for the \'' . $this->tableName . '\' table. ' . $constraints->getMessage());
		}

		foreach ($constraints as $constraint)
		{
			$con = $this->doDatabase->getDBConnection()->reverse->getTableConstraintDefinition($this->tableName, $constraint);
			$con[0]['Name'] = $constraint;
			
			if ($con['primary'])
			{
				$this->pk = array_keys($con['fields']);
			}
			
			else if ($con['foreign'])
			{
				$this->fks[$constraint] = $this->getDoForeignKey($constraint, $this->tableName, $con['fields'], $con['references']['table'], $con['references']['fields'], $con['references']['onupdate'], $con['references']['ondelete']);
			}
			
			else if ($con['unique'])
			{
				$this->uks[$constraint] = $this->getDoUniqueKey($constraint, $con['fields']);
			}
			
			else if ($con['check'])
			{
				// Checks aren't supported by mdb2 yet :(
			}
			
			// Unhandled constraint type!
			else 
			{
				echo $this->tableName . '.' . $constraint . "\n";
				var_dump($con);
			}
		}

		$cols = $this->doDatabase->getDBConnection()->manager->listTableFields($this->tableName);
		if (PEAR::isError($cols))
		{
			throw new Exception(__CLASS__ . ' Failed to list columns for the \'' . $this->tableName . '\' table. ' . $cols->getMessage());
		}

		foreach ($cols as $colName)
		{
			$col = $this->doDatabase->getDBConnection()->reverse->getTableFieldDefinition($this->tableName, $colName);
			$propName = DO_Modeler::codifyName($colName);
			$this->intMaxPropertyNameLength = max($this->intMaxPropertyNameLength, strlen($propName));
			$this->intMaxDataSourceNameLength = max($this->intMaxDataSourceNameLength, strlen($colName));
			$this->fields[$colName] = $this->getDoField($colName, $col[0], $propName);
			$this->propertyFields[$propName] = $colName;
			$this->fieldProperties[$colName] = $propName;
		}
		ksort($this->propertyFields);
	}
	
	public function getDoForeignKey($constraint, $fromTable, $fromFields, $toTable, $toFields, $onUpdate, $onDelete)
	{
		return new DO_Foreign_Key($constraint, $fromTable, $fromFields, $toTable, $toFields, $onUpdate, $onDelete);
	}
	
	public function getDoUniqueKey($name, $fields)
	{
		return new DO_Unique_Key($this, $name, $fields);
	}
	
	public function getDoField($colName, $attributes, $propName)
	{
		return new DO_Field($this, $colName, $attributes, $propName);
	}
	
	public function getFieldForProperty($propName)
	{
		$fieldName = $this->propertyFields[$propName];
		return $this->fields[$fieldName];
	}

	public function getFieldForFieldName($fieldName)
	{
		return $this->fields[$fieldName];
	}

	public function getPropertyNames()
	{
		$props = array_values($this->fieldProperties);
		asort($props);
		return $props;
	}

	public function getPropertyMappings()
	{
		return $this->propertyFields;
	}

	public function prepare()
	{
		foreach ($this->fields as $colName => $doField)
		{
			$doField->prepare();
		}
		if ($this->pk)
		{
			//$this->pk->prepare();
		}
		foreach ($this->fks as $conName => $doFK)
		{
			$doFK->prepare();
		}
	}
	
	public function generateModelFiles($classesDir)
	{
		$classFile = new DO_File_Class($this);
		$classFile->generateModelFiles($classesDir);
	}
	
	public function getDODatabase()
	{
		return $this->doDatabase;
	}
	
	public function getTableName()
	{
		return $this->tableName;
	}

	public function getClassName()
	{
		return $this->className;
	}
}

class DO_Unique_Key
{
	public $doTable;
	public $name;
	public $fields;

	public function __construct($doTable, $name, $fields)
	{
		$this->doTable = $doTable;
		$this->name = $name;
		$this->fields = $fields;
	}
}

class DO_Foreign_Key
{
	public $name;
	public $from;
	public $to;
	public $map;
	public $onUpdate;
	public $onDelete;
	
	public static $targets = array();
	public static $sources = array();
	
	public function __construct($name, $from, $fromFields, $to, $toFields, $onUpdate, $onDelete)
	{
		$this->name = $name;
		$this->from = $from;
		$this->to = $to;
		$this->map = array();
		//echo "$name FROM $from TO $to: ";
		$fromFields = array_keys($fromFields);
		$toFields = array_keys($toFields);
		for ($i = 0, $l = count($fromFields); $i < $l; $i++)
		{
			$this->map[$fromFields[$i]] = $toFields[$i];
		}
		//var_dump($this->map);
		$this->onUpdate = $onUpdate;
		$this->onDelete = $onDelete;
		
		self::$sources[$from][] = $this;
		self::$targets[$to][] = $this;
	}

	public function prepare()
	{
	}

	public function determineName()
	{
		return $this->name;
	}

	public function getFunction()
	{
		$to = DO_Table::getTable($this->to);
		$from = DO_Table::getTable($this->from);
		$fromFields = array_keys($this->map);
		$funcName = DO_Modeler::codifyName('get_'.$this->determineName());
		$strTargetClass = "DO_" . $to->getDODatabase()->getDataSourceClassName() . "_" . DO_Modeler::codifyName($this->to, true);
		$strForeignKeyFunction = "

	/**
 	 * public $funcName()
	 *
	 * Retreives an instance of $strTargetClass, the 
	 * target ('to-one' end) of the foreign key $this->name
	 * between tables $this->from and $this->to.
	 *
	 * @param void
	 * @return {$strTargetClass} instance or null
 	 */
	public function $funcName()
	{
		return {$strTargetClass}::getForId(\$this->".$from->getFieldForFieldName($fromFields[0])->getPropertyName().");
	}";
	
		return $strForeignKeyFunction;
	}
	
	public function listFunction()
	{
		$to = DO_Table::getTable($this->to);
		$from = DO_Table::getTable($this->from);
		$fromFields = array_keys($this->map);
		$funcName = DO_Modeler::codifyName('list_for_'.$this->determineName());
		$strTargetClass = "DO_" . $from->getDODatabase()->getDataSourceClassName() . "_" . DO_Modeler::codifyName($this->from, true);
		$strSourceClass = "DO_" . $to->getDODatabase()->getDataSourceClassName() . "_Base_" . DO_Modeler::codifyName($this->to, true);
		$strForeignKeyFunction = "

	/**
 	 * public $funcName()
	 *
	 * Retreives all instances of $strTargetClass, the 
	 * source ('to-many' end) of the foreign key $this->name
	 * between tables $this->from and $this->to.
	 *
	 * @param \$do $strSourceClass instance to retreive matching records for 
	 * @return array({$strTargetClass}) instances (empty array if none match)
 	 */
	public static function $funcName($strSourceClass \$do, \$strSort=NULL, \$strLimit=0, \$strOffset=0)
	{
		return {$strTargetClass}::getFor(\"".$fromFields[0]." = \" . \$do->".$to->getFieldForFieldName($this->map[$fromFields[0]])->getPropertyName().", true, \$strSort, \$strLimit, \$strOffset);
	}";
	
		return $strForeignKeyFunction;
	}
}

class DO_Field
{
	public $propertyName;
	public $fieldName;
	public $properties;
	public $doTable;
	
	public function __construct($doTable, $fieldName, $properties, $propertyName)
	{//echo "\t$name => $propertyName\n";
		$this->doTable = $doTable;
		$this->properties = $properties;
		$this->fieldName = $fieldName;
		$this->propertyName = $propertyName;
		//echo "\n\n{$doTable->tableName}::$fieldName"; var_dump($properties); echo "\n\n";
	}
	
	public function getPropertyName()
	{
		return $this->propertyName;
	}
	
	public function getPropertyLabel()
	{
		return DO_Modeler::labelifyName($this->fieldName);
	}
	
	public function getFieldName()
	{
		return $this->fieldName;
	}
	
	public function getDefaultValue()
	{
		$value = $this->properties['default'];
		$code = $this->getConversionCode(true);
		eval($code);
		return $value;
	}
	
	public function getSystemType()
	{
		$nativeType = $this->properties['nativetype'];
		$length = $this->properties['length'];
		
		if (preg_match("/(?:blob)/i", $nativeType))
		{
			return 'file';
		}
		
		if (preg_match("/(?:char|text|enum)/i", $nativeType))
		{
			return 'string';
		}
		
		if (preg_match("/(?:datetime|timestamp)/i", $nativeType))
		{
			return 'datetime';
		}
		
		if (preg_match("/(?:date)/i", $nativeType))
		{
			return 'date';
		}
		
		if (preg_match("/(?:time)/i", $nativeType))
		{
			return 'time';
		}
		
		if (preg_match("/(?:bool)/i", $nativeType) || preg_match("/(?:tinyint\(1\))/i", $nativeType) || (preg_match("/(?:tinyint)/i", $nativeType) && $this->properties['length'] == 1))
		{
			return 'boolean';
		}
		
		if (preg_match("/(?:int)/i", $nativeType))
		{
			return 'integer';
		}
		
		if (preg_match("/(?:float|decimal)/i", $nativeType))
		{
			return 'float';
		}
		
		return 'string';
	}
	
	public function getInternalType()
	{
		$nativeType = $this->properties['nativetype'];
		$length = $this->properties['length'];
		
		if (preg_match("/(?:date|time|char|text|enum|blob)/i", $nativeType))
		{
			return 'string';
		}
		
		if (preg_match("/(?:bool)/i", $nativeType) || preg_match("/(?:tinyint\(1\))/i", $nativeType) || (preg_match("/(?:tinyint)/i", $nativeType) && $this->properties['length'] == 1))
		{
			return 'boolean';
		}
		
		if (preg_match("/(?:int)/i", $nativeType))
		{
			return 'integer';
		}
		
		if (preg_match("/(?:float|decimal)/i", $nativeType))
		{
			return 'float';
		}
		
		return 'string';
	}
	
	public function getSetConversion()
	{
		$nullable = array_key_exists('notnull', $this->properties) ? !$this->properties['notnull'] : false;
		$autoIncrement = array_key_exists('autoincrement', $this->properties) ? $this->properties['autoincrement'] : false;
		$nativeType = $this->properties['nativetype'];
		$length = $this->properties['length'];
		$value = array_key_exists('default', $this->properties) ? $this->properties['default'] : null;
		$fixed = array_key_exists('fixed', $this->properties) ? $this->properties['fixed'] : false;

		switch ($this->getInternalType())
		{
				case 'string':
				
					switch ($this->getExternalType())
					{
							
						case 'string':
							
							if ($nullable) return "if (trim(\$value) == '') \$value = null;";							
					
					}
			
		}
		
		return false;
	}

	public function getQuotedDefaultValue()
	{
		$nullable = array_key_exists('notnull', $this->properties) ? !$this->properties['notnull'] : false;
		$autoIncrement = array_key_exists('autoincrement', $this->properties) ? $this->properties['autoincrement'] : false;
		$nativeType = $this->properties['nativetype'];
		$length = $this->properties['length'];
		$value = array_key_exists('default', $this->properties) ? $this->properties['default'] : null;

		$comment = "\n\t\t// Property: ".$this->propertyName."\n\t\t// Internal type: ".$this->getInternalType() . "\n\t\t// Native type: " . $nativeType . ($length ? "[$length]" : '') . ($autoIncrement ? ' autoincrement' : '') . ($nullable ? ' nullable' : ' not-null') . ((!$nullable && $value === null) ? ' [no default]' : ($value === null ? ' [default: null]' : "[default: $value]"));

		if ($value === null)
		{
			if (!$nullable)
			{
				return $comment . "\n";
			}
			$returnValue = 'null';
		}
		else
		{
			switch ($this->getInternalType())
			{
				case 'integer':
				
					if ($autoIncrement)
					{
						$value = null;
						break;
					}
					
					switch ($this->getExternalType())
					{
	
						case 'integer':
						default:
							if ($value === '') $value = 0;
							break;
	
					}
					
					break;
	
				case 'float':
				
					if ($value === '')
					{
						$value = 0.0;
					}
					
					break;
					
				case 'boolean':
				
					if (!is_bool($value))
					{
						$value = false;
					}
					$returnValue = $value ? 'TRUE' : 'FALSE';
					
					break;
					
				case 'string':
				
					switch ($this->getExternalType())
					{
	
						case 'date':
							if ($value === '') $value = 'date("Y-m-d", 0)';
							elseif ($value === 'CURRENT_TIMESTAMP' || $value === 'now()') $returnValue = 'date("Y-m-d")';
							break;
	
						case 'datetime':
							if ($value === '') $value = 'date("Y-m-d H:i:s", 0)';
							elseif ($value === 'CURRENT_TIMESTAMP' || $value === 'now()') $returnValue = 'date("Y-m-d H:i:s")';
							break;
	
						case 'time':
							if ($value === '') $value = 'date("H:i:s", 0)';
							elseif ($value === 'CURRENT_TIMESTAMP' || $value === 'now()') $returnValue = 'date("H:i:s")';
							break;
						
						case 'string':
						default:
							if ($value === '') $value = strval($value);
							break;
					}
				
					break;
					
			}
		
			$conversionCode = $this->getConversionCode(true);
			eval($conversionCode);

		
			if ($value === null)
			{
				$returnValue = 'null';
			}
			else if (!$returnValue)
			{
				switch ($this->getInternalType())
				{
					case 'string':

						$value = '"' . addslashes($value) . '"';
						break;

					case 'boolean':

						$value = $value ? 'true' : 'false';
						break;

				}

				$returnValue = strval($value);
			}
		}

		return "$comment\n\t\t\$this->{$this->propertyName} = $returnValue;\n";
	}
	
	public function getValidationCode()
	{
		$nativeType = $this->properties['nativetype'];
		$length = array_key_exists('length', $this->properties) ? $this->properties['length'] : 0;
		$fixed = array_key_exists('fixed', $this->properties) ? $this->properties['fixed'] : false;
		$nullable = array_key_exists('notnull', $this->properties) ? !$this->properties['notnull'] : false;
		$unsigned = array_key_exists('unsigned', $this->properties) ? $this->properties['unsigned'] : false;
		$default = array_key_exists('default', $this->properties) ? $this->properties['default'] : null;
		$autoIncrement = array_key_exists('autoincrement', $this->properties) ? $this->properties['autoincrement'] : false;

		if (preg_match("/(?:char|text|enum)/i", $nativeType))
		{
			return ($nullable ? '($value === null) || ' : '') . '(is_string($value)' . ($nullable ? '' : ' && trim($value) ') . ($length ? ($fixed ? (' && strlen(trim($value)) == ' . $length) : ' && strlen($value) <= ' . $length) : '') . ')';
		}
		
		if (preg_match("/(?:datetime|timestamp)/i", $nativeType))
		{
			return ($nullable ? '($value === null) || ' : '') . '(preg_match("/^(2[0-1]|19)[0-9]{2,2}\-((0[469]|11)\-(0[1-9]|[12][0-9]|30)|(0[13578]|1[02])\-(0[1-9]|[12][0-9]|3[01])|02\-(0[1-9]|[12][0-9])) (?:[01][0-9]|2[0-3])\:[0-5][0-9](?:|\:[0-5][0-9](?:|\.[0-9]{1,6}))$/", $value) && (substr($value, 5, 2) != "02" || substr($value, 8, 2) != "29" || date("L", mktime(0,0,0,1,1,substr($value, 0, 4))) == "1"))';
		}
		
		if (preg_match("/(?:date)/i", $nativeType))
		{
			return ($nullable ? '($value === null) || ' : '') . '(preg_match("/^(2[0-1]|19)[0-9]{2,2}\-((0[469]|11)\-(0[1-9]|[12][0-9]|30)|(0[13578]|1[02])\-(0[1-9]|[12][0-9]|3[01])|02\-(0[1-9]|[12][0-9]))$/", $value) && (substr($value, 5, 2) != "02" || substr($value, 8, 2) != "29" || date("L", mktime(0,0,0,1,1,substr($value, 0, 4))) == "1"))';
		}
		
		if (preg_match("/(?:time)/i", $nativeType))
		{
			return ($nullable ? '($value === null) || ' : '') . 'preg_match("/^(?:[01][0-9]|2[0-3])\:[0-5][0-9](?:|\:[0-5][0-9](?:|\.[0-9]{1,6}))$/", $value)';
		}
		
		if (preg_match("/(?:boolean)/i", $nativeType))
		{
			return 'is_bool($value)';
		}
		
		if (preg_match("/(?:int)/i", $nativeType))
		{
			return (($nullable || $autoIncrement) ? '($value === null) || ' : '') . ($unsigned ? 'preg_match("/^[0-9]+$/", "$value")' : 'preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value")');
		}
		
		if (preg_match("/(?:float|decimal)/i", $nativeType))
		{
			return ($nullable ? '($value === null) || ' : '') . '(is_float($value)' . ($unsigned ? ' && $value >= 0.0' : '') . ')';
		}
		
		if (preg_match("/(?:blob)/i", $nativeType))
		{
			return ($nullable ? '($value === null) || ' : '') . '(is_string($value)' . ($length ? ($fixed ? (' && strlen($value) == ' . $length) : ' && strlen($value) <= ' . $length) : '') . ')';
		}
		
		return 'true';
	}
	
	public function getExternalType()
	{
		$nativeType = $this->properties['nativetype'];
		$length = $this->properties['length'];
		
		if (preg_match("/(?:char|text|enum)/i", $nativeType))
		{
			return 'string';
		}
		
		if (preg_match("/(?:datetime|timestamp)/i", $nativeType))
		{
			return 'datetime';
		}
		
		if (preg_match("/(?:date)/i", $nativeType))
		{
			return 'date';
		}
		
		if (preg_match("/(?:time)/i", $nativeType))
		{
			return 'time';
		}
		
		if (preg_match("/(?:bool)/i", $nativeType))
		{
			return 'boolean';
		}
		
		if (preg_match("/(?:int)/i", $nativeType))
		{
			return 'integer';
		}
		
		if (preg_match("/(?:float|decimal)/i", $nativeType))
		{
			return 'float';
		}
		
		if (preg_match("/(?:blob)/i", $nativeType))
		{
			return 'blob';
		}
		
		return 'string';
	}
	
	public function getConversionCode($bolInbound=true)
	{
		switch ($this->getInternalType())
		{
			case 'integer':
				
				switch ($this->getExternalType())
				{
					case 'integer':
						if ($bolInbound)
						{
							return '// No conversion needed (integer to integer)';
						}
						else
						{
							return '$value = ($value === null) ? null : ($value == "" ? 0 : $value);';
						}
						break;

					case 'date':
						if ($bolInbound)
						{
							return '$value = ($value === null) ? null : ($value ? strtotime($value . " 00:00:00 UTC") : 0); // YYYY-mm-dd to timestamp';
						}
						else
						{
							return '$value = ($value === null) ? null : date("Y-m-d", intval($value)); // timestamp to YYYY-mm-dd';
						}
						break;

					case 'datetime':
						if ($bolInbound)
						{
							return '$value = ($value === null) ? null : ($value ? strtotime($value . "UTC") : 0); // YYYY-mm-dd HH:ii:ss to timestamp';
						}
						else
						{
							return '$value = ($value === null) ? null : date("Y-m-d H:i:s", intval($value)); // timestamp to YYYY-mm-dd HH:ii:ss';
						}
						break;

					case 'time':
						if ($bolInbound)
						{
							return '$value = ($value === null) ? null : strtotime("' . date("Y-m-d", 0) . '" . $value . " UTC"); // HH:ii:ss to timestamp';
						}
						else
						{
							return '$value = ($value === null) ? null : date("H:i:s", intval($value)); // timestamp to HH:ii:ss';
						}
						break;
				}
				
				break;
			
			case 'float':
			
				switch ($this->getExternalType())
				{
					case 'float':
						if ($bolInbound)
						{
							return '// No conversion needed (float to float)';
						}
						else
						{
							return '// No conversion needed (float to float)';
						}
						break;
				}
				break;
				
			case 'boolean':
			
				switch ($this->getExternalType())
				{
					case 'integer':
						if ($bolInbound)
						{
							return '$value = $value === 1; // integer to boolean (1 = true, 0 = false)';
						}
						else
						{
							return '$value = $value ? 1 : 0; // boolean to integer (true = 1, false = 0)';
						}
						break;

					case 'boolean':
						if ($bolInbound)
						{
							return '// No conversion needed (boolean to boolean)';
						}
						else
						{
							return 'return $value ? "true" : "false"; // boolean to boolean string (true = "TRUE", false = "FALSE")';
						}
						break;
						
					default:
						return '// Boolean conversion??? ' . $this->getInternalType()  . ' => ' . $this->getExternalType();
				}
				break;
				
			case 'string':
			
				$length = array_key_exists('length', $this->properties) ? $this->properties['length'] : 0;
				$fixed = array_key_exists('fixed', $this->properties) ? $this->properties['fixed'] : false;
				$nullable = array_key_exists('notnull', $this->properties) ? !$this->properties['notnull'] : false;

				switch ($this->getExternalType())
				{
					case 'string':
					case 'datetime':
					case 'date':
					case 'time':
					default:
						if ($bolInbound)
						{
							return '// No conversion needed (string to string)';
						}
						else
						{
							return '// No conversion needed (string to string)';
						}
						break;
				}
				break;
				
		}	
	}

	public function prepare()
	{
	}
}

class DO_File_Class
{
	public $doTable = null;
	
	public function __construct($doTable)
	{
		// Creates a class file
		// classes/do/{data_source}/DO_{data_source}_{ClassName} extends DO_{data_source}_Base_{ClassName}
		$this->doTable = $doTable;
	}
	
	public function generateModelFiles($classesDir)
	{
		$baseClassFile = new DO_File_Base_Class($this->doTable);
		$baseClassFile->generateModelFiles($classesDir);
		
		// Generate the object class...
		$obBaseClassName = "DO_" . $this->doTable->getDODatabase()->getDataSourceClassName() . "_Base_" . $this->doTable->getClassName();
		$obClassName = "DO_" . $this->doTable->getDODatabase()->getDataSourceClassName() . "_" . $this->doTable->getClassName();
		$dsnClassName = "DO_" . $this->doTable->getDODatabase()->getDataSourceClassName();
		$dsnDirName = $this->doTable->getDODatabase()->getDataSourcePathName();
		
		$path = "$classesDir/do/$dsnDirName/$obClassName.php";
		
		// Don't overwrite an existing object class - it could have been customised
		if (file_exists($path))
		{
			return;
		}
		
		$f = fopen($path, "w");
		
		fwrite($f, '<?php

class '.$obClassName.' extends '.$obBaseClassName.'
{
}

?>');
		
		fclose($f);
	}
}

class DO_File_Base_Class
{
	public function __construct($doTable)
	{
		// Creates a base class file
		// classes/do/{data_source}/base/DO_{data_source}_Base_{ClassName} extends DO_{data_source}
		$this->doTable = $doTable;
	}

	public function generateModelFiles($classesDir)
	{
		$baseClassFile = new DO_File_Data_Source_Base_Class($this->doTable->getDODatabase());
		$baseClassFile->generateModelFiles($classesDir);
		
		// Generate the base class...
		$obBaseClassName = "DO_" . $this->doTable->getDODatabase()->getDataSourceClassName() . "_Base_" . $this->doTable->getClassName();
		$obClassName = "DO_" . $this->doTable->getDODatabase()->getDataSourceClassName() . "_" . $this->doTable->getClassName();
		$dsnClassName = "DO_" . $this->doTable->getDODatabase()->getDataSourceClassName();
		$dsnDirName = $this->doTable->getDODatabase()->getDataSourcePathName();
		
		$strObjectName = $this->doTable->getObjectLabel();
		
		if (!file_exists("$classesDir/do/$dsnDirName/base"))
		{
			mkdir("$classesDir/do/$dsnDirName/base");
		}
		
		$path = "$classesDir/do/$dsnDirName/base/$obBaseClassName.php";
		
		$f = fopen($path, "w");
		
		$propNames = $this->doTable->getPropertyNames();
		$propNames = count($propNames) ? ("\n\t\t\t'" . implode("',\n\t\t\t'", $propNames) . "',\n\t\t") : '';
		
		$mapping = $this->doTable->getPropertyMappings();
		$strSrcPropMapping = '';
		$strPropSrcMapping = '';
		$strDataSourceToInternalCases = '';
		$strInternalToDataSourceCases = '';
		$strInternalValidationCases = '';
		$strPropertyNameCases = '';
		$strDefaultValues = '';
		$strSetCases = '';
		$this->doTable->intMaxPropertyNameLength;
		$this->doTable->intMaxDataSourceNameLength;
		if (count($mapping))
		{
			foreach ($mapping as $prop => $field)
			{
				$strSrcPropMapping .= $strSrcPropMapping ? "\n\t\t\t" : "\n\t\t\t";
				$strPropSrcMapping .= $strPropSrcMapping ? "\n\t\t\t" : "\n\t\t\t";
				
				$m = $this->doTable->intMaxDataSourceNameLength + 2;
				$v = (ceil(floatval($m) / 4) * 4) + (($m % 4) ? 0 : 4);
				$x = ceil(floatval($v - (strlen($field) + 2)) / 4);
				
				$tabs = str_repeat("\t", $x);
				$strSrcPropMapping .= "'$field'$tabs=> '$prop',";
				
				$m = $this->doTable->intMaxPropertyNameLength + 2;
				$v = (ceil(floatval($m) / 4) * 4) + (($m % 4) ? 0 : 4);
				$x = ceil(floatval($v - (strlen($prop) + 2)) / 4);
				
				$tabs = str_repeat("\t", $x);
				$strPropSrcMapping .= "'$prop'$tabs=> '$field',";
				
				$obField = $this->doTable->getFieldForProperty($prop);
				
				$strDataSourceToInternalCases .= "\n\n\t\t\tcase '$prop':\n\t\t\t\t" . $obField->getConversionCode(true) . "\n\t\t\t\t\$this->{\$propertyName} = \$value;\n\t\t\t\tbreak;";
				$strInternalToDataSourceCases .= "\n\n\t\t\tcase '$prop':\n\t\t\t\t" . $obField->getConversionCode(false) . "\n\t\t\t\tbreak;";
				$strInternalValidationCases .= "\n\n\t\t\tcase '$prop':\n\t\t\t\treturn " . $obField->getValidationCode() . ';';
				$strPropertyNameCases .= "\n\n\t\t\tcase '$prop':\n\t\t\t\treturn '" . $obField->getPropertyLabel() . '\';';
				
				$strSetCase = $obField->getSetConversion();
				if ($strSetCase)
				{
					$strSetCases .= "\n\n\t\t\tcase '$prop':\n\t\t\t\t$strSetCase";
				}
				
				$strDefaultValues .= $obField->getQuotedDefaultValue();
			}
			$strSrcPropMapping .= "\n\t\t";
			$strPropSrcMapping .= "\n\t\t";
		}
		
		// TODO - This only allows for a single column PK - Extend to support composite PKs!
		$strDataSourceIdName = count($this->doTable->pk) ? $this->doTable->pk[0] : '';
		//echo "\n$strDataSourceIdName\n";
		$strIdName = $strDataSourceIdName ? $this->doTable->getFieldForFieldName($strDataSourceIdName)->getPropertyName() : '';
		$strTableName = $this->doTable->getTableName();
		
		$strForeignKeyFunctions = "";
		$fks = $this->doTable->fks;
		foreach ($fks as $constraint => $doFK)
		{
			$fields = array_keys($doFK->map);
			//echo 'from ' . $doFK->from . ' to ' . $doFK->to . "\n";
			$strForeignKeyFunctions .= $doFK->getFunction();
			$strForeignKeyFunctions .= $doFK->listFunction();
		}
		
		
		fwrite($f, '<?php

abstract class '.$obBaseClassName.' extends '.$dsnClassName.'
{
	protected function setDefaultProperties($arrProperties=null)
	{'.$strDefaultValues.'
	}
	
	public static function getPropertyNames()
	{
		return array('.$propNames.');
	}
	
	public function getObjectLabel()
	{
		return \''.$strObjectName.'\';
	}

	public function getPropertyLabel($propertyName)
	{
		switch ($propertyName)
		{'.$strPropertyNameCases.'

			default:
				// Not recognised, so just return property name
				return $propertyName;
		}
	}

	public function getPropertyDataSourceName($propertyName)
	{
		$dsNames = $this->getPropertyDataSourceMappings();
		return $dsNames[$propertyName];
	}

	public static function getPropertyDataSourceMappings()
	{
		return array('.$strPropSrcMapping.');
	}
	
	public static function getDataSourcePropertyMappings()
	{
		return array('.$strSrcPropMapping.');
	}

	public static function getDataSourceObjectName()
	{
		return \''.$strTableName.'\';
	}

	public static function getIdName()
	{
		return \''.$strIdName.'\';
	}

	public static function getDataSourceIdName()
	{
		return \''.$strDataSourceIdName.'\';
	}

	public function getValueForDataSource($propertyName, $bolQuoted=true)
	{
		$value = array_key_exists($propertyName, $this->properties) ? $this->properties[$propertyName] : null;
		switch ($propertyName)
		{'.$strInternalToDataSourceCases.'

			default:
				// No conversion - assume is correct or irrelevant

		}
		if ($bolQuoted)
		{
			if ($value === null)
			{
				$value = "NULL";
			}
			else
			{
				$dataSource = $this->getDataSource();
				$value = \'\\\'\' . $dataSource->escape($value, true) . \'\\\'\';
			}
		}
		return $value;
	}

	protected function setValueFromDataSource($propertyName, $value)
	{
		switch ($propertyName)
		{'.$strDataSourceToInternalCases.'

			default:
				// No conversion - assume is correct already or not really from data source
				$this->{$propertyName} = $value;

		}
	}

	protected function _isValidValue($propertyName, $value)
	{
		switch ($propertyName)
		{'.$strInternalValidationCases.'

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{'.$strSetCases.'
			
		}

		parent::__set($name, $value);
	}



'.$strForeignKeyFunctions.'



	//==============================================================================//
	// THE FOLLOWING FUNCTIONS SHOULD BE MOVED TO DO_Base WHEN WE MOVE TO PHP 5.3.0 //
	//==============================================================================//


'.$this->getDoBaseFunctions($obClassName).'


}

?>');
		
		fclose($f);
	}
	
	
	
	
	
	public function getDoBaseFunctions($obClassName)
	{
		return '
	protected static function whereArrayToString($arrWhere)
	{
		$arrDsProps = '.$obClassName.'::getDataSourcePropertyMappings();
		$dataSource = '.$obClassName.'::getDataSource();
		$arrMatches = array();
		foreach($arrWhere as $propertyName => $value)
		{
			$fieldName = array_search($propertyName, $arrDsProps);
			$value = $dataSource->escape($value);
			$arrMatches[] = "$fieldName = \'$value\'";
		}
		return implode(\' AND \', $arrMatches);
	}

	protected static function getFor($mxdWhere=null, $multiple=false, $strSort=null, $strLimit=0, $strOffset=0)
	{
		$tableName = '.$obClassName.'::getDataSourceObjectName();

		$arrDsProps = '.$obClassName.'::getDataSourcePropertyMappings();

		if (is_array($mxdWhere))
		{
			$strWhere = '.$obClassName.'::whereArrayToString($mxdWhere);
		}
		else
		{
			$strWhere = $mxdWhere;
		}
		
		$arrFields = array();
		foreach($arrDsProps as $dsName => $propName)
		{
			$arrFields[] = $tableName.\'.\'.$dsName . \' AS "\' . $propName . \'"\';
		}
		$strFields = implode(\', \', $arrFields);

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
			$strSQL .= " LIMIT " . intval($strLimit) . " OFFSET " . intval($strOffset);
		}

		$dataSource = '.$obClassName.'::getDataSource();

		if (PEAR::isError($results = $dataSource->query($strSQL)))
		{
			throw new Exception(\'Failed to load records for \' . __CLASS__ . \' :: \' . $results->getMessage());
		}

		$details = $results->fetchAll(MDB2_FETCHMODE_ASSOC);

		$arrInstances = array();
		$matched = false;
		foreach($details as $detail)
		{
			$instance = new '.$obClassName.'($detail);
			$instance->setSaved(true);
			$arrInstances[] = $instance;
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
	
	protected static function countFor($mxdWhere, $strLimit=NULL, $strOffset=0)
	{
		$tableName = '.$obClassName.'::getDataSourceObjectName();

		if (is_array($mxdWhere))
		{
			$strWhere = '.$obClassName.'::whereArrayToString($mxdWhere);
		}
		else
		{
			$strWhere = $mxdWhere;
		}
		
		$strSQL = "SELECT COUNT(" . '.$obClassName.'::getDataSourceIdName() . ") AS \"count\" FROM $tableName ";
		
		if ($strWhere)
		{
			$strSQL .= " WHERE $strWhere";
		}
		
		if ($strLimit)
		{
			$strSQL .= " LIMIT " . intval($strLimit) . " OFFSET " . intval($strOffset);
		}

		$dataSource = '.$obClassName.'::getDataSource();

		if (PEAR::isError($results = $dataSource->query($strSQL)))
		{
			throw new Exception(\'Failed to count records for \' . __CLASS__ . \' :: \' . $results->getMessage());
		}

		return $results->fetchOne();
	}
	
	public static function getForId($id)
	{
		return '.$obClassName.'::getFor('.$obClassName.'::getDataSourceIdName() . " = " . intval($id), false, null, 0, 0);
	}
	
	public static function getDataSource()
	{
		return Data_Source::get('.$obClassName.'::getDataSourceName());
	}

';
	} 
}

class DO_File_Data_Source_Base_Class
{
	public $doDatabase = null;

	public function __construct($doDatabase)
	{
		// Creates a base class file
		// classes/do/{data_source}/DO_{data_source}
		$this->doDatabase = $doDatabase;
	}

	public function generateModelFiles($classesDir)
	{
		$dsnName = $this->doDatabase->getDataSourceName();
		$dsnDirName = $this->doDatabase->getDataSourcePathName();
		$dsnClassName = "DO_" . $this->doDatabase->getDataSourceClassName();
		$path = "$classesDir/do/$dsnDirName/$dsnClassName.php";
		
		// Do not replace the base class - it should never change UNLESS done so manually to point to another data source (via some devious means!).
		if (file_exists($path))
		{
			return;
		}
		
		if (!file_exists("$classesDir/do"))
		{
			mkdir("$classesDir/do");
		}
		
		if (!file_exists("$classesDir/do/$dsnDirName"))
		{
			mkdir("$classesDir/do/$dsnDirName");
		}
		
		$f = fopen($path, "w");
		
		fwrite($f, '<?php

abstract class '.$dsnClassName.' extends DO_Base
{
	const DEFAULT_DATA_SOURCE_NAME = \''.$dsnName.'\';

	protected static function getDataSourceName()
	{
		return self::DEFAULT_DATA_SOURCE_NAME;
	}
}

?>');
		
		fclose($f);
	}
}

?>
