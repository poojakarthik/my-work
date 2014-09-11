<?php

//----------------------------------------------------------------------------//
// QueryCreate
//----------------------------------------------------------------------------//
/**
 * QueryCreate
 *
 * CREATE Query
 *
 * Implements a CREATE query using mysqli
 *
 *
 * @prefix		crq
 *
 * @package		framework
 * @class		QueryCreate
 */
 class QueryCreate extends Query
 {
 	function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		parent::__construct($strConnectionType);
	}
		
 	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the Query
	 *
	 * Executes the Query
	 *
	 * @param		mixed	mixTable		string containing name of the table to create
	 * 										or an array of table names to create.
	 * 
	 * @return		bool
	 * @method
	 */ 
	 function Execute($mixTable)
	 {
		// Trace
		$this->Trace("Input: $mixTable");
	 	
	 	// check what we were given
		if (!$mixTable)
		{
			return FALSE;
		}
		elseif (is_string($mixTable))
		{
			// convert string to array
			$arrTables = Array($mixTable);
		}
		elseif (is_array($mixTable))
		{
			$arrTables = $mixTable;
		}
		else
		{
			return FALSE;
		}
		
		// by default we return TRUE
		$bolReturn = TRUE;

		// create tables
		foreach($arrTables as $strTableName)
		{
			//echo($strTableName);
			// check that table def exists
			if (is_array($this->db->arrTableDefine->{$strTableName}))
			{
				$arrTableDefine = $this->db->arrTableDefine->{$strTableName};
				
				/* CREATE TABLE `{$define['Name']}` (
				 *		`{$define['Id']}`	bigint	NOT NULL	auto_increment,
				 *		`{$column['name']}` {$column['type']} {$column['attributes']} {$column['null']} DEFAULT '{$column['default']}',
				 *		...
				 *
				 * INDEX	(`{$index[n]}`, `{$index[n++]}`),
				 * UNIQUE	(`{$unique[n]}`, `{$unique[n++]}`),
				 * PRIMARY KEY	(`{$define['Id']}`)
				 * ) TYPE = {$define['Type']}
		 		 */
				 /*
				 	$define['Name']		= "";			// table name
				 	$define['Type']		= "MYISAM";		// defaults to	'MYISAM'
					$define['Id']		= "Id";			// defaults to	'Id'
					
					$define['Index'][] 		= "";
					$define['Unique'][] 	= "";
					
					$define['Column'][$strName]['Type'] 		= "";			// Validation type: s, i etc
					$define['Column'][$strName]['SqlType'] 		= "";			// Sql Type: Varchar(5), Int etc
					$define['Column'][$strName]['Null'] 		= TRUE|FALSE;	// optional, defaults to FALSE (NOT NULL)
					$define['Column'][$strName]['Default'] 		= "";			// optional default value
					$define['Column'][$strName]['Attributes'] 	= "";			// optional attributes
				 
				 */
				 
				// clean reused variables 
				$strIndex = '';
				$strUnique = '';

				// set defaults primary index
				if (empty($arrTableDefine['Id']))
				{
					$arrTableDefine['Id'] = 'Id';
				}
				
				// set default table type
				if (empty($structure['Type']))
				{
					$arrTableDefine['Type'] = 'MYISAM';
				}
				 
				// build index string
				if (is_array($arrTableDefine['Index']))
				{
					foreach($arrTableDefine['Index'] as $strIndexValue)
					{
						$strIndex .= "$strIndexValue,";
					}
					$strIndex = substr($strIndex, 0, -1);
				}
				 
				// build unique string
				if (is_array($arrTableDefine['Unique']))
				{
					foreach($arrTableDefine['Unique'] as $strUniqueValue)
					{
						$strUnique .= "$strUniqueValue,";
					}
					$strUnique = substr($strUnique, 0, -1);
				}
				
				// build the CREATE query
				$strQuery  = "CREATE TABLE $strTableName (\n";
				
				// autoindex (Id column)
				$strQuery .= "	{$arrTableDefine['Id']} bigint NOT NULL auto_increment,\n";
				
				// columns
				foreach($arrTableDefine['Column'] as $strColumnKey=>$arrColumn)
				{
					// use the key if we don't have a column name
					if (empty($arrColumn['Name']))
					{
						$arrColumn['Name'] = $strColumnKey;
					}
					
					// null, defaults to not null
					if ($arrColumn['Null'] === TRUE)
					{
						$strNull = '';
					}
					else
					{
						$strNull = 'NOT NULL';
					}
					
					// default
					if($arrColumn['Default'])
					{
						$arrColumn['Default'] = "DEFAULT '{$arrColumn['Default']}'";
					}
					
					$strQuery .= "	{$arrColumn['Name']} {$arrColumn['SqlType']} {$arrColumn['Attributes']} $strNull {$arrColumn['Default']},\n";
				}
				 
				// index
				if ($strIndex)
				{
					$strQuery .= "	INDEX	($strIndex),\n";
				}
				// unique
				if ($strUnique)
				{
					$strQuery .= "	UNIQUE	($strUnique),\n";
				}
				// primary key & table type
				$strQuery .= "	PRIMARY KEY	({$arrTableDefine['Id']})\n";
				$strQuery .= ") TYPE = {$arrTableDefine['Type']}\n";
				
				// Trace
				$this->Trace("Query: ".$strQuery);
				
				// run query
				$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
				//echo (mysqli_error($this->db->refMysqliConnection));
				// check result
				$this->Debug($mixReturn);
				if ($mixReturn !== TRUE)
				{
					// we will return false
					// Trace
					$this->Trace("Failed: ".$this->Error());
					$bolReturn = FALSE;
				}
			}
			else
			{
				// we will return false
				$bolReturn = FALSE;
			}
		}
		
		return $bolReturn;
	 }
}

?>
