<?php

//----------------------------------------------------------------------------//
// StatementSelect
//----------------------------------------------------------------------------//
/**
 * StatementSelect
 *
 * SELECT Query
 *
 * Implements a SELECT query using mysqli statements
 *
 *
 * @prefix		sel
 *
 * @package		framework
 * @class		StatementSelect
 */
 class StatementSelect extends Statement
 {
	//------------------------------------------------------------------------//
	// arrBoundResults
	//------------------------------------------------------------------------//
	/**
	 * arrBoundResults
	 *
	 * Stores the temporary bound results
	 *
	 * Stores the temporary bound results, so we only have to call bind_restults once
	 *
	 * @type	array
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
 	private $_arrBoundResults = Array();
	
	//------------------------------------------------------------------------//
	// datMetaData
	//------------------------------------------------------------------------//
	/**
	 * datMetaData
	 *
	 * Stores the meta data for the response
	 *
	 * Stores the meta data for the response, so we can use it for oblib later
	 *
	 * @type	array
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
 	private $_datMetaData = Array();	
	
	//------------------------------------------------------------------------//
	// _bolObLib
	//------------------------------------------------------------------------//
	/**
	 * _bolObLib
	 *
	 * Evaluates whether or not we want oblib objects or an array returned
	 *
	 * Evaluates whether or not we want oblib objects returned or if we would
	 * prefer to use an array. In order to set this value as "TRUE", you need
	 * to call useObLib (TRUE). By default, we do not want to use ObLib
	 *
	 * @type	boolean
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
 	private $_bolObLib = FALSE;

 
	//------------------------------------------------------------------------//
	// StatementSelect() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * StatementSelect()
	 *
	 * Constructor for StatementSelect object
	 *
	 * Constructor for StatementSelect object
	 *
	 * @param		string	strTables		String of tables, like in an SQL FROM clause,
	 * 										ignoring the  FROM keyword.
	 * 										(eg. "TableName[, Table2Name JOIN Table3Name...]")
	 * @param		mixed	mixColumns		Can be either associative or indexed array.
	 * 										Use indexed for normal column referencing.
	 * 										Use associated arrays for either renaming of
	 * 										columns (eg. ["ColumnAlias"] = "ColumnName") and
	 * 										special SQL funcion calls (eg. ["NowAlias"] = "NOW()")
	 * @param		mixed	mixWhere		optional A full SQL WHERE clause, minus the keyword.
	 * 										Paramaters should be aliased in a meaningful
	 * 										fashion enclosed in <>'s
	 * 										(eg. "FooBar = <FooBar>")
	 *										Can also be passed as an associative array (eg. the same
	 *										array as passed to the execute method), to produce a
	 *										WHERE clause like "Foo = <Foo> AND Bar = <Bar>" using
	 *										the array keys.
	 * @param		string	strOrder		optional A full SQL ORDER BY clause, minus the keywords
	 * 										(eg. "ColumnName ASC, Column2Name")
	 * @param		string	strLimit		optional SQL LIMIT clause, minus the keyword
	 * 										(eg. "5") - Return first 5 rows
	 * 										(eg. "5,10") - Return rows 6-15
	 * @param		string	strGroupBy		optional a full SQL GROUP BY clause, minus the keywords
	 * 										(eg. "")
	 * @return		void
	 *
	 * @method
	 */ 
	function __construct($strTables, $mixColumns, $mixWhere = "", $strOrder = "", $strLimit = "", $strGroupBy = "", $strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		parent::__construct($strConnectionType);
		
		// Trace
		$this->Trace("Input: $strTables, $mixColumns, $mixWhere, $strOrder, $strLimit, $strGroupBy");
		
		// prepare the WHERE clause
		$strWhere = $this->PrepareWhere($mixWhere);
		
		// Compile the query from our passed info
	 	$strQuery = "SELECT ";
	 	
	 	if (is_string($mixColumns))
	 	{
	 		// $mixColumns is just a string, therefore only one column selected
	 		$strQuery .= $mixColumns . "\n";
	 	}
 		elseif ($this->IsAssociativeArray($mixColumns))
 		{
			foreach ($mixColumns as $strAlias=>$strColumn)
			{
				if (!$strColumn)
				{
		 			// No alias (key is the column name)
		 			$strQuery .= $strAlias;
				}
				else
				{
		 			// Alias
		 			$strQuery .= $strColumn." AS ".$strAlias;
				}
				
		 		$strQuery .= ", ";
			}
			$strQuery = substr($strQuery, 0, -2);
			
			
			/*
			// If arrColumns is associative, then add keys and values with "AS" between them
			reset($mixColumns);
			
		 	// Add the columns 	
		 	while (isset(current($mixColumns)))
		 	{
		 		$strQuery .= current($mixColumns);
		 		
		 		if (current($mixColumns) == '')
		 		{
		 			// No alias
		 			$strQuery .= key($mixColumns);
		 		}
		 		else
		 		{
		 			// Alias
		 			$strQuery .= " AS ";
		 			$strQuery .= key($mixColumns);
		 		}
		 		$strQuery .= ", ";
				next($mixColumns);
		 	}
			$strQuery = substr($strQuery, 0, -2);*/
 		}
 		elseif (is_array($mixColumns))
 		{
 			// If it's an indexed array
			reset($mixColumns);
			
		 	// Add the columns 	
		 	foreach($mixColumns as $strColumn)
		 	{
		 		$strQuery .= "$strColumn, ";
		 	}
			$strQuery = substr($strQuery, 0, -2);
 		}
 		else
 		{
 			// We have an invalid type, so throw an exception
 			//throw new InvalidTypeException();
 			echo("Invalid Type!  Line 765\n");
 		}

	 	// Add the FROM line
	 	$strQuery .= "\n FROM " . $strTables . "\n";

	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = $this->FindAlias($strWhere);
			$strQuery .= $strWhere . "\n";
			//Debug($strQuery);
	 	}
	 	
	 	// Add the GROUP BY clause
	 	if ($strGroupBy != "")
	 	{
			$strQuery .= "GROUP BY " . $strGroupBy . "\n";	
	 	}
	 	
	 	// Add the ORDER BY clause
	 	if ($strOrder != "")
	 	{
			$strQuery .= "ORDER BY " . $strOrder . "\n";	
	 	}
	 	
	 	// Add the LIMIT clause
	 	if ($strLimit != "")
	 	{
			$strQuery .= "LIMIT " . $strLimit . "\n";	
	 	}
	 	
		// Trace
		$this->Trace("Query: $strQuery");
		
		$this->_strQuery = $strQuery;
	 	
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();
		
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
	 		// There was problem preparing the statment
	 		//throw new Exception("Could not prepare statement : $strQuery\n");
			// Trace
			$this->Trace("Error: ".$this->Error());
			Debug($this->Error());
			//throw new Exception($this->Error());
	 	}
		//Debug($this->_strQuery);
	}
	
	//------------------------------------------------------------------------//
	// useObLib()
	//------------------------------------------------------------------------//
	/**
	 * useObLib()
	 *
	 * Changes our ObLib status
	 *
	 * Changes our flag to specify whether or not we are using ObLib
	 *
	 * @param		boolean	bolObLib		TRUE: We are using ObLib
	 *										FALSE: We are not using ObLib
	 * 
	 * @return		void
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 public function useObLib ($bolObLib)
	 {
	 	$this->_bolObLib = ($bolObLib === TRUE);
	 }
	 
	//------------------------------------------------------------------------//
	// MetaData()
	//------------------------------------------------------------------------//
	/**
	 * MetaData()
	 *
	 * Get the Meta Data from the Result
	 *
	 * Get the Meta Data from the Result
	 *
	 * @return		Array
	 * @method
	 */ 
	 public function MetaData ()
	 {
		return $this->_datMetaData;
	 }

	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementSelect, with a new set of values
	 *
	 * Executes the StatementSelect, with a new set of values
	 *
	 * @param		array	arrWhere		Associative array of parameters for the WHERE clause.
	 * 										MUST use the same aliases as used when the object was
	 * 										created.  Key string is the alias (ignoring the <>'s)
	 * 										, and the Value is the value to be inserted.
	 * 
	 * @return		int		number of rows
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrWhere = Array())
	 {
	 	// Trace
//		$this->Trace("Execute($strQuery)");
	 	
	 	$strType = "";
	 	
	 	// Bind the WHERE data to our mysqli_stmt
	 	if (isset($this->_arrWhereAliases))
	 	{
	 		$i = 0;
		 	// Bind the WHERE data to our mysqli_stmt
		 	foreach ($this->_arrWhereAliases as $strAlias)
		 	{
				if (is_array($arrWhere[$strAlias]))
				{
					$strParam = $arrWhere[$strAlias]['Value'];
				}
				else
				{
					$strParam = $arrWhere[$strAlias];
				}
		 		$strType .= $this->GetDBInputType($strParam);
		 		$arrParams[] = $strParam;
	 			$i++;
		 	}
			//Debug($arrParams);
/*
		 	if (count($this->_arrWhereAliases) != count($arrParams))
		 	{
		 		Debug("Number of Aliases doesn't match variables");
		 		Debug($this->_arrWhereAliases);
		 		Debug($arrParams);
		 		DebugBacktrace();
		 	}

		 	Debug("Aliases: ".count($this->_arrWhereAliases)."; Params: ".count($arrParams). "; Types: $strType; Query: \n".$this->_strQuery);
		 	DebugBacktrace();*/
		 	//Debug("Aliases: ".count($this->_arrWhereAliases)."; Params: ".count($arrParams). "; Types: $strType; Query: \n".$this->_strQuery);
			if (isset ($arrParams) && is_array($arrParams))
			{
		 		array_unshift($arrParams, $strType);
				call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
			}
			//Debug ($this);
	 	}
		
	 	// Free any previous results
	 	$this->_stmtSqlStatment->free_result();
	 	
	 	// Run the Statement
	 	$mixResult = $this->_stmtSqlStatment->execute();
	 	$this->Debug($mixResult);
	 	if(!$mixResult)
	 	{
			// Trace
			$this->Trace("Failed: ".$this->Error());
			// Die in the rectal sphincter
			throw new Exception($this->Error());
	 	}
	 	
		
		
	 	// Store the results (required for result_metadata())
	 	$this->_stmtSqlStatment->store_result();
	 	
	 	// Retrieve the metatdata from the resultset
	 	$this->_datMetaData = $this->_stmtSqlStatment->result_metadata();
		
		// Create a parameter list for bind_result()
	 	while ($fldField = $this->_datMetaData->fetch_field())
	 	{
	 		// Each parameter is a reference to an index in the result array (key is the Field name)
	 		$arrFields[] = &$this->_arrBoundResults[$fldField->name];
	 	}
		
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_result"), $arrFields);
		return $this->_stmtSqlStatment->num_rows;
	}
	
	//------------------------------------------------------------------------//
	// Count()
	//------------------------------------------------------------------------//
	/**
	 * Count()
	 *
	 * Counts how many rows were returned by the last execution
	 *
	 * Counts how many rows were returned by the last execution
	 *
	 * @return		integer							Returns a number (0..*) with the number of 
	 *										rows returned by this query
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Count()
	{
		return $this->_stmtSqlStatment->num_rows;
	}

	//------------------------------------------------------------------------//
	// Fetch()
	//------------------------------------------------------------------------//
	/**
	 * Fetch()
	 *
	 * Fetches the next row of data
	 *
	 * Fetches the next row of data from the resultset as an Associative Array
	 *
	 * @param		data	&oblobjPushObject	An object that is inherited from `data` which
	 *											contains the method "Push". This will be used
	 *											to insert information into the object
	 *
	 * @return		mixed					Associative array of columns and values
	 * 										Key is the ColumnName, value is its value
	 * 										or FALSE if there was no next row
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Fetch (&$oblobjPushObject=null)
	{
		// Firstly, if we have a row to pull ... pull it (and put it into $this->_arrBoundResults)
		if($this->_stmtSqlStatment->fetch())
		{
			// If we are using ObLib, we want to turn everything into an oblib object
			if ($this->_bolObLib === TRUE)
			{
				// We're up to here which means that we're using ObLib ...
				
				// Because we're using ObLib, we need the first parameter to be called.
				// In this instance, this means we need $oblobjPushObject to be an
				// inheritance of `data` and contain the method "Push"
				if (!is_subclass_of ($oblobjPushObject, 'data') || !method_exists ($oblobjPushObject, 'Push'))
				{
					throw new Exception (
						'You are using Oblib ... Therefore you must have ' .
						'1 parameter ([inherited of] data $oblobjPushObject) ' .
						'with a method named `Push` where the information will be returned'
					);
				}
				
				// Start at 0 and loop through all the fields in the meta data
				$i=0;
				while ($fldField = @$this->_datMetaData->fetch_field_direct ($i++))
				{
					// Because Id is not defined in our database definitions, we
					// can have to specify that Id fields are Integers
					if ($fldField->name == "Id")
					{
						$oblobjPushObject->Push (new dataInteger ("Id", $this->_arrBoundResults ["Id"]));
					}
					else
					{
						// Create a new instance of an oblib object using the ObLib parameter of the database definition
						if (isset ($this->db->arrTableDefine->{$fldField->table}["Column"][$fldField->name]["ObLib"]))
						{
							$oblobjPushObject->Push
							(
								new $this->db->arrTableDefine->{$fldField->table}["Column"][$fldField->name]["ObLib"]
								(
									$fldField->name, $this->_arrBoundResults [$fldField->name]
								)
							);
						}
					}
				}
				
				return true;
			}
			else
			{
				// If we're not using oblib, return an associative array
 				$arrResult = array();
				foreach($this->_arrBoundResults as $strKey=>$mixValue)
				{
					$arrResult[$strKey] = $mixValue;
				}
				return $arrResult;
			}
		}
		
		// If there was no result, then return false
		return false;
	}
	
	
	//------------------------------------------------------------------------//
	// FetchAll()
	//------------------------------------------------------------------------//
	/**
	 * FetchAll()
	 *
	 * Fetches the resultset
	 *
	 * Fetches the entire resultset as an Indexed array of Associated arrays
	 * NOTE: 	If you have previously called Fetch(), then FetchAll() will only
	 * 			return the remaining rows in the resultset, not all.
	 *
	 * @return		array					Indexed array of Associated arrays
	 * 										Associative arrays of columns and values
	 * 										Key is the ColumnName, value is its value
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function FetchAll()
	{
	 	// Retrieve the remaining rows of data from the resultset
	 	$arrResults = Array();

		// Add the results to our huge array of results
	 	while($this->_stmtSqlStatment->fetch())
	 	{
			$arrTemp = array();
			foreach($this->_arrBoundResults as $strKey=>$mixValue)
			{
				$arrTemp[$strKey] = $mixValue;
			}
			$arrResults[] = $arrTemp;
	 	}
	 	
 		return $arrResults;
	}
}

?>
