<?php
class MDB2_Driver_pgsql extends MDB2_Driver {

	private $_oPDO;
	public $aPortabilityOptions;

	private $_bInTransaction = false;

	function __construct($aDSN, $aOptions=false) {
		$this->_oPDO = new PDO("pgsql:dbname={$aDSN['database']};host={$aDSN['hostspec']}", $aDSN['username'], $aDSN['password']);
		$this->_oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setFetchMode(MDB2_FETCHMODE_DEFAULT);
		$this->aPortabilityOptions = $aOptions;

		$aValidationErrors = $this->_validatePortabilityOptions();
		if (!empty($aValidationErrors)) {
			throw new Exception("Error in method __construct(), unsupported portability option/s: " . implode(", ", $aValidationErrors));
		}
	}

	public function escape($sValue, $escape_wildcards=false) {
		if ($escape_wildcards) {
			throw new Exception("Error in method escape(), unsupported/unimplemented parameter escape_wildcards: " . var_export($escape_wildcards, true));
		}
		return @pg_escape_string($sValue);
	}

	public function quote($sValue, $sType=null, $bQuote=true, $bEscapeWildcards=false) {
		return (gettype($sValue) === 'string') ? $this->_oPDO->quote($sValue) : $sValue;
	}

	public function queryOne($sQuery, $sType=null, $mColnum=0){
		$oStatement = $this->_oPDO->query($sQuery);
		if (gettype($mColnum) === 'integer') {
			$aRow = $oStatement->fetch(PDO::FETCH_NUM);
		} elseif (gettype($mColnum) === 'string') {
			$aRow = $oStatement->fetch(PDO::FETCH_ASSOC);
		} else {
			throw new Exception("Error in method queryOne(), unsupported type: " . gettype($sType));
		}
		return (isset($aRow[$mColnum])) ? $aRow[$mColnum] : new MDB2_Error();
	}

	function listTableConstraints($table)
	{
		$db = $this->_oPDO;

		list($schema, $table) = $this->splitTableSchema($table);

		$table = $this->quote($table, 'text');
		$query = 'SELECT conname
					FROM pg_constraint
			   LEFT JOIN pg_class ON pg_constraint.conrelid = pg_class.oid
			   LEFT JOIN pg_namespace ON pg_class.relnamespace = pg_namespace.oid
				   WHERE relname = ' .$table;
		if (!empty($schema)) {
			$query .= ' AND pg_namespace.nspname = ' . $this->quote($schema, 'text');
		}
		$query .= '
				   UNION DISTINCT
				  SELECT relname
					FROM pg_class
				   WHERE oid IN (
						 SELECT indexrelid
						   FROM pg_index
					  LEFT JOIN pg_class ON pg_class.oid = pg_index.indrelid
					  LEFT JOIN pg_namespace ON pg_class.relnamespace = pg_namespace.oid
						  WHERE pg_class.relname = '.$table.'
							AND indisunique = \'t\'';
		if (!empty($schema)) {
			$query .= ' AND pg_namespace.nspname = ' . $this->quote($schema, 'text');
		}
		$query .= ')';
		$constraints = $this->queryCol($query);

		if (MDB2::isError($constraints)) {
			return $constraints;
		}

		$result = array();
		foreach ($constraints as $constraint) {
			$constraint = $this->fixIndexName($constraint);
			if (!empty($constraint)) {
				$result[$constraint] = true;
			}
		}
		return array_keys($result);
	}

	function getTableConstraintDefinition($table_name, $constraint_name)
	{
		$db = $this->_oPDO;
		list($schema, $table) = $this->splitTableSchema($table_name);
		$query = "SELECT c.oid,
						 c.conname AS constraint_name,
						 c.contype AS constraint_type,
						 CASE WHEN c.contype = 'c' THEN 1 ELSE 0 END AS \"check\",
						 CASE WHEN c.contype = 'f' THEN 1 ELSE 0 END AS \"foreign\",
						 CASE WHEN c.contype = 'p' THEN 1 ELSE 0 END AS \"primary\",
						 CASE WHEN c.contype = 'u' THEN 1 ELSE 0 END AS \"unique\",
						 CASE WHEN c.condeferrable = 'f' THEN 0 ELSE 1 END AS deferrable,
						 CASE WHEN c.condeferred = 'f' THEN 0 ELSE 1 END AS initiallydeferred,
						 --array_to_string(c.conkey, ' ') AS constraint_key,
						 t.relname AS table_name,
						 t2.relname AS references_table,
						 CASE confupdtype
						   WHEN 'a' THEN 'NO ACTION'
						   WHEN 'r' THEN 'RESTRICT'
						   WHEN 'c' THEN 'CASCADE'
						   WHEN 'n' THEN 'SET NULL'
						   WHEN 'd' THEN 'SET DEFAULT'
						 END AS onupdate,
						 CASE confdeltype
						   WHEN 'a' THEN 'NO ACTION'
						   WHEN 'r' THEN 'RESTRICT'
						   WHEN 'c' THEN 'CASCADE'
						   WHEN 'n' THEN 'SET NULL'
						   WHEN 'd' THEN 'SET DEFAULT'
						 END AS ondelete,
						 CASE confmatchtype
						   WHEN 'u' THEN 'UNSPECIFIED'
						   WHEN 'f' THEN 'FULL'
						   WHEN 'p' THEN 'PARTIAL'
						 END AS match,
						 --array_to_string(c.confkey, ' ') AS fk_constraint_key,
						 consrc
					FROM pg_constraint c
			   LEFT JOIN pg_class t  ON c.conrelid  = t.oid
			   LEFT JOIN pg_class t2 ON c.confrelid = t2.oid
				   WHERE c.conname = %s
					 AND t.relname = " . $this->quote($table, 'text');
		//$constraint_name_mdb2 = $this->getIndexName($constraint_name);
		$constraint_name_mdb2 = $constraint_name;
		$row = $this->queryRow(sprintf($query, $this->quote($constraint_name_mdb2, 'text')), null, MDB2_FETCHMODE_ASSOC);
		if (MDB2::isError($row) || empty($row)) {
			// fallback to the given $index_name, without transformation
			$constraint_name_mdb2 = $constraint_name;
			$row = $this->queryRow(sprintf($query, $this->quote($constraint_name_mdb2, 'text')), null, MDB2_FETCHMODE_ASSOC);
		}
		if (MDB2::isError($row)) {
			return $row;
		}
		$uniqueIndex = false;
		if (empty($row)) {
			// We might be looking for a UNIQUE index that was not created
			// as a constraint but should be treated as such.
			$query = 'SELECT relname AS constraint_name,
							 indkey,
							 0 AS "check",
							 0 AS "foreign",
							 0 AS "primary",
							 1 AS "unique",
							 0 AS deferrable,
							 0 AS initiallydeferred,
							 NULL AS references_table,
							 NULL AS onupdate,
							 NULL AS ondelete,
							 NULL AS match
						FROM pg_index, pg_class
					   WHERE pg_class.oid = pg_index.indexrelid
						 AND indisunique = \'t\'
						 AND pg_class.relname = %s';
			$constraint_name_mdb2 = $this->getIndexName($constraint_name);
			$row = $this->queryRow(sprintf($query, $this->quote($constraint_name_mdb2, 'text')), null, MDB2_FETCHMODE_ASSOC);;
			if (MDB2::isError($row) || empty($row)) {
				// fallback to the given $index_name, without transformation
				$constraint_name_mdb2 = $constraint_name;
				$row = $this->queryRow(sprintf($query, $this->quote($constraint_name_mdb2, 'text')), null, MDB2_FETCHMODE_ASSOC);
			}
			if (MDB2::isError($row)) {
				return $row;
			}
			if (empty($row)) {
				return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null, $constraint_name . ' is not an existing table constraint', __FUNCTION__);
			}
			$uniqueIndex = true;
		}
		$row = array_change_key_case($row, CASE_LOWER);

		$sConstraintType = (isset($row['constraint_type'])) ? $row['constraint_type'] : null;
		$definition = array(
			'constraint_type' => $sConstraintType,
			'primary' => (boolean)$row['primary'],
			'unique'  => (boolean)$row['unique'],
			'foreign' => (boolean)$row['foreign'],
			'check'   => (boolean)$row['check'],
			'fields'  => array(),
			'references' => array(
				'table'  => $row['references_table'],
				'fields' => array(),
			),
			'deferrable' => (boolean)$row['deferrable'],
			'initiallydeferred' => (boolean)$row['initiallydeferred'],
			'onupdate' => $row['onupdate'],
			'ondelete' => $row['ondelete'],
			'match'    => $row['match'],
		);

		if ($uniqueIndex) {
			$columns = $this->listTableFields($table_name);
			$index_column_numbers = explode(' ', $row['indkey']);
			$colpos = 1;
			foreach ($index_column_numbers as $number) {
				$definition['fields'][$columns[($number - 1)]] = array(
					'position' => $colpos++,
					'sorting'  => 'ascending',
				);
			}
			return $definition;
		}

		$query = 'SELECT a.attname
					FROM pg_constraint c
			   LEFT JOIN pg_class t  ON c.conrelid  = t.oid
			   LEFT JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(c.conkey)
				   WHERE c.conname = %s
					 AND t.relname = ' . $this->quote($table, 'text');
		$fields = $this->queryCol(sprintf($query, $this->quote($constraint_name_mdb2, 'text')), null);
		if (MDB2::isError($fields)) {
			return $fields;
		}
		$colpos = 1;
		foreach ($fields as $field) {
			$definition['fields'][$field] = array(
				'position' => $colpos++,
				'sorting' => 'ascending',
			);
		}

		if ($definition['foreign']) {
			$query = 'SELECT a.attname
						FROM pg_constraint c
				   LEFT JOIN pg_class t  ON c.confrelid  = t.oid
				   LEFT JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(c.confkey)
					   WHERE c.conname = %s
						 AND t.relname = ' . $this->quote($definition['references']['table'], 'text');
			$foreign_fields = $this->queryCol(sprintf($query, $this->quote($constraint_name_mdb2, 'text')), null);
			if (MDB2::isError($foreign_fields)) {
				return $foreign_fields;
			}
			$colpos = 1;
			foreach ($foreign_fields as $foreign_field) {
				$definition['references']['fields'][$foreign_field] = array(
					'position' => $colpos++,
				);
			}
		}

		if ($definition['check']) {
			//$check_def = $this->queryOne("SELECT pg_get_constraintdef(" . $row['oid'] . ", 't')");
			// ...
		}
		return $definition;
	}


	public function listTables() {
		try {
			$oStatement = $this->_oPDO->query("SELECT tablename FROM pg_catalog.pg_tables WHERE tableowner != 'postgres'");
			$aTables = $oStatement->fetchAll();
			$aResult = array();
			for ($i=0; $i<count($aTables); $i++) {
				$aResult[] = array_shift(array_slice($aTables[$i], 0, 1));
			}
			return $aResult;
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	function listTableFields($sTableName) {
		list($schema, $table) = $this->splitTableSchema($sTableName);
		//echo "%%%%%%%%%%%%%%%%%{$table}%%%%%%%%%%%%%%";
		return $this->getColumnNames($table);
	}

	function getColumnNames($sTableName) {
		$columns = array();
		$oResult = $this->_oPDO->query("
			select		column_name
			from		information_schema.columns
			where		table_name='{$sTableName}';");
		$aResultset = $oResult->fetchAll();
		//var_dump($aResultset);
		$aColumns = array();
		foreach($aResultset as $aResult) {
			$aColumns[] = $aResult['column_name'];
		}
		return $aColumns;
	}

	function getTableFieldDefinition($table_name, $field_name) {
		//echo "------{$field_name}-------";
		$db = $this->_oPDO;
		list($schema, $table) = $this->splitTableSchema($table_name);
		$query = "SELECT a.attname AS name,
						 t.typname AS type,
						 CASE a.attlen
						   WHEN -1 THEN
							 CASE t.typname
							   WHEN 'numeric' THEN (a.atttypmod / 65536)
							   WHEN 'decimal' THEN (a.atttypmod / 65536)
							   WHEN 'money'   THEN (a.atttypmod / 65536)
							   ELSE CASE a.atttypmod
								 WHEN -1 THEN NULL
								 ELSE a.atttypmod - 4
							   END
							 END
						   ELSE a.attlen
						 END AS length,
						 CASE t.typname
						   WHEN 'numeric' THEN (a.atttypmod % 65536) - 4
						   WHEN 'decimal' THEN (a.atttypmod % 65536) - 4
						   WHEN 'money'   THEN (a.atttypmod % 65536) - 4
						   ELSE 0
						 END AS scale,
						 a.attnotnull,
						 a.atttypmod,
						 a.atthasdef,
						 (SELECT substring(pg_get_expr(d.adbin, d.adrelid) for 128)
							FROM pg_attrdef d
						   WHERE d.adrelid = a.attrelid
							 AND d.adnum = a.attnum
							 AND a.atthasdef
						 ) as default
					FROM pg_attribute a,
						 pg_class c,
						 pg_type t
				   WHERE c.relname = ".$this->quote($table, 'text')."
					 AND a.atttypid = t.oid
					 AND c.oid = a.attrelid
					 AND NOT a.attisdropped
					 AND a.attnum > 0
					 AND a.attname = ".$this->quote($field_name, 'text')."
				ORDER BY a.attnum";


		$column = $this->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
		if (MDB2::isError($column)) {
			return $column;
		}

		$column = array_change_key_case($column, CASE_LOWER);
		//$mapped_datatype = $this->_getMDB2DataType($column['type']);
		$mapped_datatype = $this->mapNativeDatatype($column);
		if (MDB2::isError($mapped_datatype)) {
			return $mapped_datatype;
		}
		list($types, $length, $unsigned, $fixed) = $mapped_datatype;
		$notnull = false;
		if (!empty($column['attnotnull']) && $column['attnotnull'] == 't') {
			$notnull = true;
		}
		$default = null;
		if ($column['atthasdef'] === 't'
			&& strpos($column['default'], 'NULL') !== 0
			&& !preg_match("/nextval\('([^']+)'/", $column['default'])
		) {
			$pattern = '/^\'(.*)\'::[\w ]+$/i';
			$default = $column['default'];#substr($column['adsrc'], 1, -1);
			if ((null === $default) && $notnull) {
				$default = '';
			} elseif (!empty($default) && preg_match($pattern, $default)) {
				//remove data type cast
				$default = preg_replace ($pattern, '\\1', $default);
			}
		}
		$autoincrement = false;
		if (preg_match("/nextval\('([^']+)'/", $column['default'], $nextvals)) {
			$autoincrement = true;
		}
		$definition[0] = array('notnull' => $notnull, 'nativetype' => $column['type']);
		if (null !== $length) {
			$definition[0]['length'] = $length;
		}
		if (null !== $unsigned) {
			$definition[0]['unsigned'] = $unsigned;
		}
		if (null !== $fixed) {
			$definition[0]['fixed'] = $fixed;
		}
		if ($default !== false) {
			$definition[0]['default'] = $default;
		}
		if ($autoincrement !== false) {
			$definition[0]['autoincrement'] = $autoincrement;
		}
		foreach ($types as $key => $type) {
			$definition[$key] = $definition[0];
			if ($type == 'clob' || $type == 'blob') {
				unset($definition[$key]['default']);
			}
			$definition[$key]['type'] = $type;
			$definition[$key]['mdb2type'] = $type;
		}
		return $definition;
	}


	function mapNativeDatatype($field) {
		return $this->_mapNativeDatatype($field);
	}

	function _mapNativeDatatype($field)
	{
		$db_type = strtolower($field['type']);
		$length = $field['length'];
		$type = array();
		$unsigned = $fixed = null;
		switch ($db_type) {
		case 'smallint':
		case 'int2':
			$type[] = 'integer';
			$unsigned = false;
			$length = 2;
			if ($length == '2') {
				$type[] = 'boolean';
				if (preg_match('/^(is|has)/', $field['name'])) {
					$type = array_reverse($type);
				}
			}
			break;
		case 'int':
		case 'int4':
		case 'integer':
		case 'serial':
		case 'serial4':
			$type[] = 'integer';
			$unsigned = false;
			$length = 4;
			break;
		case 'bigint':
		case 'int8':
		case 'bigserial':
		case 'serial8':
			$type[] = 'integer';
			$unsigned = false;
			$length = 8;
			break;
		case 'bool':
		case 'boolean':
			$type[] = 'boolean';
			$length = null;
			break;
		case 'text':
		case 'varchar':
			$fixed = false;
		case 'unknown':
		case 'char':
		case 'bpchar':
			$type[] = 'text';
			if ($length == '1') {
				$type[] = 'boolean';
				if (preg_match('/^(is|has)/', $field['name'])) {
					$type = array_reverse($type);
				}
			} elseif (strstr($db_type, 'text')) {
				$type[] = 'clob';
				$type = array_reverse($type);
			}
			if ($fixed !== false) {
				$fixed = true;
			}
			break;
		case 'date':
			$type[] = 'date';
			$length = null;
			break;
		case 'datetime':
		case 'timestamp':
		case 'timestamptz':
			$type[] = 'timestamp';
			$length = null;
			break;
		case 'time':
			$type[] = 'time';
			$length = null;
			break;
		case 'float':
		case 'float4':
		case 'float8':
		case 'double':
		case 'real':
			$type[] = 'float';
			break;
		case 'decimal':
		case 'money':
		case 'numeric':
			$type[] = 'decimal';
			if (isset($field['scale'])) {
				$length = $length.','.$field['scale'];
			}
			break;
		case 'tinyblob':
		case 'mediumblob':
		case 'longblob':
		case 'blob':
		case 'bytea':
			$type[] = 'blob';
			$length = null;
			break;
		case 'oid':
			$type[] = 'blob';
			$type[] = 'clob';
			$length = null;
			break;
		case 'year':
			$type[] = 'integer';
			$type[] = 'date';
			$length = null;
			break;
		default:
			throw new Exception("Error in method _getMDB2DataType(), unknown database attribute type: " . var_export($sDatatype, true));
		}
		if ((int)$length <= 0) {
			$length = null;
		}
		return array($type, $length, $unsigned, $fixed);
	}

	public function beginTransaction($sSavepoint=null) {
		if($sSavepoint !== null) {
			//throw new Exception("Error in method beginTransaction(), unimplemented property: \$sSavepoint");
			if (!$this->inTransaction()) {
				return new MDB2_Error('Can\'t create savepoint while not in a transaction');
			}
			return $this->_query('SAVEPOINT ' . $sSavepoint);
		} else {
			if($this->inTransaction()) {
				return MDB2_OK;
			} else {
				if($this->_oPDO->beginTransaction()) {
					$this->_bInTransaction = true;
					return MDB2_OK;
				} else {
					return new MDB2_Error();
				}
			}
		}
	}

	public function inTransaction($bIgnoreNested=false) {
		if($bIgnoreNested) {
			throw new Exception("Error in method inTransaction(), unimplemented property: \$bIgnoreNested");
		}
		return $this->_bInTransaction;
		/* Not until PHP 5.3
		return !!$this->_oPDO->inTransaction();
		*/
	}

	public function commit($sSavepoint=null) {
		if($this->inTransaction()) {
			if ($sSavepoint !== null) {
				return $this->_query('RELEASE SAVEPOINT ' . $sSavepoint);
			} else {
				if($this->_oPDO->commit()) {
					$this->_bInTransaction = false;
					return MDB2_OK;
				} else {
					return new MDB2_Error();
				}
			}
		} else {
			return new MDB2_Error();
		}
	}

	public function rollback($sSavepoint=null) {
		if ($sSavepoint !== null) {
			if (!$this->inTransaction()) {
				return new MDB2_Error('Can\'t rollback to savepoint while not in a transaction');
			}
			return $this->_query('ROLLBACK TO SAVEPOINT ' . $sSavepoint);
		}

		if($this->_oPDO->rollBack()) {
			$this->_bInTransaction = false;
			return MDB2_OK;
		} else {
			return new MDB2_Error();
		}
	}

	public function setFetchMode($iFetchMode, $sObjectClass='stdClass') {
		if ($sObjectClass !== 'stdClass') {
			throw new Exception("Error in method setFetchMode(), setting the object class is not supported: " . var_export($sObjectClass, true));
		}
		if (!is_numeric($iFetchMode)) {
			throw new Exception("Error in method setFetchMode(), unsupported Fetch Mode requested: " . var_export($iFetchMode, true));
		}
		$this->setPDOFetchMode($iFetchMode);
		$this->_oPDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->getPDOFetchMode($iFetchMode));
	}

	public function exec($sQuery) {
		try {
			return $this->_oPDO->exec($sQuery);
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	protected function _query($sQuery) {
		try {
			return $this->_oPDO->query($sQuery);
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function query($sQuery, $mTypes=null, $mResultClass=true, $mResultWrapClass=true) {
		try {
			if ($mResultClass !== true) {
				throw new Exception('Error in method query(), unimplemented/unknown result class: ' . var_export($mResultClass, true));
			}
			if ($mResultWrapClass !== true) {
				throw new Exception('Error in method query(), unimplemented/unknown class to wrap results: ' . var_export($mResultWrapClass, true));
			}
			/*
			if ($mTypes !== null) {
				throw new Exception('Error in method query(), unimplemented/unknown column types: ' . var_export($mTypes, true));
			}
			*/
			return new MDB2_Driver_pgsql_Result($this->_oPDO->query($sQuery), $this);

		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function numRows() {
		if($this->_oPDO->rowCount()) {
			return $this->_oPDO->rowCount();
		} else {
			return new MDB2_Error();
		}
	}

	public function lastInsertID($sTable=null, $sField=null) {
		if (!$sTable && !$sField) {
			return $this->queryOne('SELECT lastval()', 'integer');
		}

		$sSequence = $sTable;
		if ($sField) {
			$sSequence .= '_' $sField;
		}
		return $this->queryOne("SELECT currval('" . $this->quoteIdentifier($sSequence) . "')", 'integer');
	}

	private function _validatePortabilityOptions() {
		$aErrors = array();
		// Not allowed options.
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_FIX_CASE)) {
			$aErrors[] = 'MDB2_PORTABILITY_FIX_CASE';
		}
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_EMPTY_TO_NULL)) {
			$aErrors[] = 'MDB2_PORTABILITY_EMPTY_TO_NULL';
		}
		return $aErrors;
	}

	private function _isPortabilityOptionSet($iPortabilityConstant) {
		return (isset($this->aPortabilityOptions['portability']) && $this->aPortabilityOptions['portability'] & $iPortabilityConstant);
	}

	// Method inspired by MDB2.
	private static function _getNativeDataType($sDatatype) {
		return preg_replace('/^([a-z]+)[^a-z].*/i', '\\1', $sDatatype);
	}
}