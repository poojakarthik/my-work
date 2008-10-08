<?php

class Flex_Data_Model
{
	private static $cache = array();

	private static function _getTableModelFilePath($tableName)
	{
		static $base;
		if (!isset($base))
		{
			$base = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'type' . DIRECTORY_SEPARATOR;
		}
		return $base . strtolower($tableName) . '.php';
	}

	public static function saveTableDataModel($tableDataModel)
	{
		// Cache the table data model to make accessible to all users
		self::$cache[$tableDataModel['Name']] = $tableDataModel;

		// Write the definition to file
		$filePath = self::_getTableModelFilePath($tableDataModel['Name']);
		$f = @fopen($filePath, 'w');
		if ($f === FALSE)
		{
			throw new Exception("Failed to open '$filePath' for writing.");
		}
		$strModel = str_replace('  ', "\t", var_export($tableDataModel, TRUE));
		$ok = @fwrite($f, "<?php\n\$modelDefinition = " . $strModel . ";\n?>");
		@fclose($f);
		if ($ok === FALSE)
		{
			throw new Exception("Failed writing to '$filePath'.");
		}
	}

	public static function generateDataModelForDatabaseTable($tableName, $strDataSource=FLEX_DATABASE_CONNECTION_FLEX, &$dataSource=NULL)
	{
		if ($dataSource === NULL)
		{
			$dataSource = Data_Source::get($strDataSource);
			$dataSource->loadModule('Manager');
			$dataSource->loadModule('Reverse');
		}

		$cols = $dataSource->manager->listTableFields($tableName);
		if (PEAR::isError($cols))
		{
			throw new Exception(__CLASS__ . ' Failed to list columns for the \'' . $tableName . '\' table in \'' . $strDataSource . '\' database. ' . $cols->getMessage());
		}

		$modelDef = array();
		$modelDef['Name']		= $tableName; 
		$modelDef['Type']		= 'InnoDB'; 
		//$modelDef['Id']			= 'Id'; 
		$modelDef['Index'][]	= ''; 
		$modelDef['Unique'][]	= ''; 

		foreach ($cols as $colName)
		{
			$col = $dataSource->reverse->getTableFieldDefinition($tableName, $colName);
			$col[0]['Field'] = $colName;
			self::_addColumnDefToModelDef($modelDef, $col[0], $tableName);
		}

		self::saveTableDataModel($modelDef);

		return $modelDef;
	}

	public static function generateDataModelForDatabase($strDataSource=FLEX_DATABASE_CONNECTION_FLEX)
	{
		$dataSource = Data_Source::get($strDataSource, false, true);

		$dataSource->loadModule('Manager');
		$dataSource->loadModule('Reverse');
		
		$tables = $dataSource->manager->listTables();

		if (PEAR::isError($tables))
		{
			throw new Exception(__CLASS__ . ' Failed to list tables for \'' . $strDataSource . '\' database. ' . $tables->getMessage());
		}

		foreach ($tables as $tableName)
		{
			self::generateDataModelForDatabaseTable($tableName, $strDataSource);
		}
	}

	public function __get($tableName)
	{
		return self::get($tableName);
	}

	public static function get($tableName)
	{
		$tableName = strtolower($tableName);
		if (!array_key_exists($tableName, self::$cache))
		{
			$filePath = self::_getTableModelFilePath($tableName);
			if (!file_exists($filePath))
			{
				return NULL;
			}
			$modelDefinition = NULL;
			require $filePath;
			self::$cache[$tableName] = $modelDefinition;
		}
		return self::$cache[$tableName];
	}
	
	// Retrieves details for all tables in the database
	public static function getAll()
	{
		$arrTableFiles = glob(self::_getTableModelFilePath("*"));
		
		foreach ($arrTableFiles as $strFile)
		{
			$strTableName = strtolower(str_replace(".php", "", str_replace(dirname($strFile). DIRECTORY_SEPARATOR, "", $strFile)));
			if (!array_key_exists($strTableName, self::$cache))
			{
				// The table isn't currently cached
				$modelDefinition = NULL;
				require $strFile;
				self::$cache[$strTableName] = $modelDefinition;
			}
		}
		return self::$cache;
	}

	// This is a shocker! Should be re-written!!
	// This version was lifted straight from the old 'import.php'.
	private static function _addColumnDefToModelDef(&$modelDef, $colDef, $tableName)
	{
		if (strtolower($colDef['Field']) != "id") 
		{
			if (preg_match ("/char/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataString";
			}

			if (preg_match ("/text/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataString";
			}

			if (preg_match ("/date/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataDate";
			}

			if (preg_match ("/time/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataTime";
			}

			if (preg_match ("/datetime/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataDatetime";
			}
			
			if (preg_match ("/timestamp/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataDatetime";
			}

			if (preg_match ("/int/", $colDef['nativetype'])) {
				$colDef['RefType'] = "i";
				$colDef['ObLib'] = "dataInteger";
			}

			if (preg_match ("/bigint/", $colDef['nativetype'])) {
				$colDef['RefType'] = "d";
				$colDef['ObLib'] = "dataInteger";
			}

			if (preg_match ("/tinyint\(1\)/", $colDef['nativetype']) || ($colDef['nativetype'] == 'tinyint' && $colDef['length'] == 1)) {
				$colDef['RefType'] = "i";
				$colDef['ObLib'] = "dataBoolean";
			}

			if (preg_match ("/float/", $colDef['nativetype'])) {
				$colDef['RefType'] = "d";
				$colDef['ObLib'] = "dataFloat";
			}

			if (preg_match ("/decimal/", $colDef['nativetype'])) {
				$colDef['RefType'] = "d";
				$colDef['ObLib'] = "dataFloat";
			}

			if (preg_match ("/blob/", $colDef['nativetype'])) {
				$colDef['RefType'] = "b";
				$colDef['ObLib'] = "";
			}

			if (preg_match ("/enum/", $colDef['nativetype'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataString";
			}

			if ($colDef['Field'] == "ABN") {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "ABN";
			}

			if ($colDef['Field'] == "ACN") {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "ACN";
			}

			if (strtolower($tableName) == "employee" && $colDef['Field'] == "Session") {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataString";
			}

			$modelDef['Column'][$colDef['Field']]['Type']		= $colDef['RefType'];
			$modelDef['Column'][$colDef['Field']]['SqlType']	= $colDef['nativetype'];
			$modelDef['Column'][$colDef['Field']]['Null']		= !$colDef['notnull'];
			$modelDef['Column'][$colDef['Field']]['Default']	= $colDef['default'] === null;
			$modelDef['Column'][$colDef['Field']]['ObLib']		= $colDef['ObLib'];
		}
		else
		{
			// This is the Id for the Table
			$modelDef['Id']	= $colDef['Field'];
		}
	}
}

?>
