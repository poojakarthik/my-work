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

	public static function generateDataModelForDatabaseTable($tableName, $strDataSource=FLEX_DATABASE_CONNECTION_FLEX)
	{
		$qryQuery = new Query($strDataSource);

		$strSQL = 'SHOW COLUMNS FROM ' . $tableName;
		$cols = $qryQuery->Execute($strSQL);
		if (!$cols)
		{
			throw new Exception(__CLASS__ . ' Failed to list columns for the \'' . $tableName . '\' table in \'' . $strDataSource . '\' database. ' . mysqli_errno() . '::' . mysqli_error());
		}

		$modelDef = array();
		$modelDef['Name']		= $tableName; 
		$modelDef['Type']		= 'InnoDB'; 
		//$modelDef['Id']			= 'Id'; 
		$modelDef['Index'][]	= ''; 
		$modelDef['Unique'][]	= ''; 

		while ($col = $cols->fetch_assoc())
		{
			self::_addColumnDefToModelDef($modelDef, $col, $tableName);
		}

		self::saveTableDataModel($modelDef);

		return $modelDef;
	}

	public static function generateDataModelForDatabase($strDataSource=FLEX_DATABASE_CONNECTION_FLEX)
	{
		$qryQuery = new Query($strDataSource);

		$strSQL = 'SHOW TABLES';
		$tables = $qryQuery->Execute($strSQL);
		if (!$tables)
		{
			throw new Exception(__CLASS__ . ' Failed to list tables for \'' . $strDataSource . '\' database. ' . mysqli_errno() . '::' . mysqli_error());
		}

		$key = NULL;
		while ($table = $tables->fetch_assoc())
		{
			if ($key === NULL)
			{
				$keys = array_keys($table);
				$key =$keys[0];
			}
			$tableName = $table[$key];
			$dataModel = self::generateDataModelForDatabaseTable($tableName, $strDataSource);
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

	// This is a shocker! Should be re-written!!
	// This version was lifted straight from the old 'import.php'.
	private static function _addColumnDefToModelDef(&$modelDef, $colDef, $tableName)
	{
		if (strtolower($colDef['Field']) != strtolower("Id")) 
		{
			if (preg_match ("/char/", $colDef['Type'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataString";
			}

			if (preg_match ("/text/", $colDef['Type'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataString";
			}

			if (preg_match ("/date/", $colDef['Type'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataDate";
			}

			if (preg_match ("/time/", $colDef['Type'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataTime";
			}

			if (preg_match ("/datetime/", $colDef['Type'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataDatetime";
			}
			
			if (preg_match ("/timestamp/", $colDef['Type'])) {
				$colDef['RefType'] = "s";
				$colDef['ObLib'] = "dataDatetime";
			}

			if (preg_match ("/int/", $colDef['Type'])) {
				$colDef['RefType'] = "i";
				$colDef['ObLib'] = "dataInteger";
			}

			if (preg_match ("/bigint/", $colDef['Type'])) {
				$colDef['RefType'] = "d";
				$colDef['ObLib'] = "dataInteger";
			}

			if (preg_match ("/tinyint\(1\)/", $colDef['Type'])) {
				$colDef['RefType'] = "i";
				$colDef['ObLib'] = "dataBoolean";
			}

			if (preg_match ("/float/", $colDef['Type'])) {
				$colDef['RefType'] = "d";
				$colDef['ObLib'] = "dataFloat";
			}

			if (preg_match ("/decimal/", $colDef['Type'])) {
				$colDef['RefType'] = "d";
				$colDef['ObLib'] = "dataFloat";
			}

			if (preg_match ("/blob/", $colDef['Type'])) {
				$colDef['RefType'] = "b";
				$colDef['ObLib'] = "";
			}

			if (preg_match ("/enum/", $colDef['Type'])) {
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
			$modelDef['Column'][$colDef['Field']]['SqlType']	= $colDef['Type'];
			$modelDef['Column'][$colDef['Field']]['Null']		= $colDef['Null'] === "YES";
			$modelDef['Column'][$colDef['Field']]['Default']	= $colDef['Default'] === null;
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
