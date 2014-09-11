<?php

//----------------------------------------------------------------------------//
// VixenWhere
//----------------------------------------------------------------------------//
/**
 * VixenWhere
 *
 * Funky new where object
 *
 * New where object for nice inputs
 *
 * 
 *
 *
 * @prefix	   vxw
 *
 * @package	  framework
 * @class		VixenWhere
 */
class VixenWhere
{
	//------------------------------------------------------------------------//
	// VixenWhere() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * VixenWhere()
	 *
	 * Constructor for VixenWhere object
	 *
	 * Constructor for VixenWhere object
	 *
	 * @param		string		strFunction		The function we are passing, represented as a string
	 *
	 * @return   	void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function __construct ($mixColumn=NULL, $mixValue=NULL, $mixEval=NULL)
	{
		$this->arrInternal = array();
	}
	
	//------------------------------------------------------------------------//
	// AddAnd()
	//------------------------------------------------------------------------//
	/**
	 * AddAnd()
	 *
	 * Adds AND statement to array
	 *
	 * Adds an entry into the internal array for a where statement of type AND
	 * e.g WHERE Id = 3 AND Account = 1000056654
	 *
	 * @param		mix		mixColumn	The name of the column to be entered
	 * @param		mix		mixValue	The value of said column
	 * @param		mix		mixEval	 	The operator involved (e.g. =, <, >, etc)
	 *
	 * @return   	boolean
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function AddAnd($mixColumn=NULL, $mixValue=NULL, $mixEval=WHERE_EQUALS)
	{
		// Check for null input values
		if (is_null($mixValue) && !is_object($mixColumn))
		{
			return FALSE;
		}
		$this->arrInternal[] = array("Column"=>$mixColumn, "Value"=>$mixValue, "Eval"=>$mixEval, "Type"=>'AND');
	}
	
	//------------------------------------------------------------------------//
	// AddOr()
	//------------------------------------------------------------------------//
	/**
	 * AddOr()
	 *
	 * Adds OR statement to array
	 *
	 * Adds an entry into the internal array for a where statement of type OR
	 * e.g WHERE Id = 3 OR Account = 1000056654
	 *
	 * @param		mix		mixColumn	The name of the column to be entered 
	 *									in the format TableName.ColumnName
	 * @param		mix		mixValue	The value of said column
	 * @param		mix		mixEval	 	The operator involved (e.g. =, <, >, etc)
	 *
	 * @return   	boolean
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function AddOr($mixColumn=NULL, $mixValue=NULL, $mixEval=WHERE_EQUALS)
	{
		// Check for null input values
		if (is_null($mixValue) && !is_object($mixColumn))
		{
			return FALSE;
		}
	  	$this->arrInternal[] = array("Column"=>$mixColumn, "Value"=>$mixValue, "Eval"=>$mixEval, "Type"=>'OR');
	}
	
	//------------------------------------------------------------------------//
	// Table()
	//------------------------------------------------------------------------//
	/**
	 * Table()
	 *
	 * Checks if table exists in the internal array
	 *
	 * Checks if table exists in the internal array by looking at the TableName
	 * part of TableName.ColumnName
	 *
	 * @param		string	strTable	The name of the table to be tested
	 *
	 * @return   	boolean
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 	
	function Table($strTable)
	{
	 	// Cycle through each of the entries in the internal array
		foreach ($this->arrInternal as $arrEntry)
		{
			// If the column entry is an array
			if (is_array ($arrEntry["Column"]))
			{
				// Cycle through column array
				foreach ($arrEntry["Column"] as $strCol)
				{
					// Disassemble the formatting and check
					$arrExplode = explode('.', $strCol, 2);
					if ($arrExplode[1])
					{
						if ($arrExplode[0] == $strTable)
						{
							return TRUE;
						}
					}
				}					
			}
			// If the column entry is an object
			elseif (is_object ($arrEntry["Column"]))
			{
				// Check by reexecuting the function on this object
				if ($arrEntry["Column"]->Table($strTable))
				{
					return TRUE;
				}
			}
			// Assume it's a string
			else
			{
				// Disassemble the formatting and check
				$arrExplode = explode('.', $arrEntry["Column"], 2);
				if ($arrExplode[1])
				{
					if ($arrExplode[0] == $strTable)
					{
						return TRUE;
					}
				}
			}
		}
		// Otherwise return FALSE
		return FALSE;		
	}
	
	//------------------------------------------------------------------------//
	// Tables()
	//------------------------------------------------------------------------//
	/**
	 * Tables()
	 *
	 * Returns a list of tables
	 *
	 * Returns a list of all tables added to the internal array
	 *
	 * @return   	array					array of tables returned
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 	
	function Tables()
	{
		$arrReturn = Array();
		// Cycle through each of the entries in the internal array
		foreach ($this->arrInternal as $arrEntry)
		{
			// If the column entry is an array
			if (is_array ($arrEntry["Column"]))
			{
				// Cycle through column array
				foreach ($arrEntry["Column"] as $strCol)
				{
					// Disassemble the formatting and return list
					$arrTable = explode('.', $strCol, 2); 
					$arrReturn[$arrTable[0]] = $arrTable[0];
				}					
			}
			// If the column entry is an object
			elseif (is_object ($arrEntry["Column"]))
			{	
				// Merge the array to return with the returned array
				// when the column entry is reexecuted through the function
				array_merge($arrReturn, $arrEntry["Column"]->Tables());
			}
			// Assume it's a string
			else
			{	
				// Disassemble the formatting and return list
				$arrTable = explode('.', $arrEntry["Column"], 2); 
				$arrReturn[$arrTable[0]] = $arrTable[0];
			}
		}
		// Return the array
		return $arrReturn;		
	}
	
	//------------------------------------------------------------------------//
	// Column()
	//------------------------------------------------------------------------//
	/**
	 * Column()
	 *
	 * Checks if column exists in the internal array
	 *
	 * Checks if column exists in the internal array
	 *
	 * @param		string	strColumn	The name of the column to be tested
	 *									in the format TableName.ColumnName
	 *
	 * @return   	boolean
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 	
	function Column($strColumn)
	{
		// Cycle through each of the entries in the internal array
	 	foreach ($this->arrInternal as $arrEntry)
		{
			// If the column entry is an array
			if (is_array ($arrEntry["Column"]))
			{	
				// Cycle through column array
				foreach ($arrEntry["Column"] as $strCol)
				{	
					// Check
					if ($strCol == $strColumn)
					{
						return TRUE;
					}
				}					
			}
			// If the column entry is an object
			elseif (is_object ($arrEntry["Column"]))
			{
				// Check by reexecuting the function on this object
				if ($arrEntry["Column"]->Column($strColumn))
				{
					return TRUE;
				}
			}
			// Assume it's a string
			else
			{
				// Check
				if ($arrEntry["Column"] == $strColumn)
				{
					return TRUE;
				}
			}
		}
		// Otherwise return FALSE
		return FALSE;		
	}
	
	//------------------------------------------------------------------------//
	// Columns()
	//------------------------------------------------------------------------//
	/**
	 * Columns()
	 *
	 * Returns an array of columns
	 *
	 * Returns an array of all columns added to the internal array in the format
	 * TableName.ColumnName
	 *
	 * @return   	array					array of columns returned
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Columns()
	{		
		$arrReturn = Array();
		// Cycle through each of the entries in the internal array
		foreach ($this->arrInternal as $arrEntry)
		{
			// If the column entry is an array
			if (is_array ($arrEntry["Column"]))
			{
				// Cycle through column array, add entries to return array
				foreach ($arrEntry["Column"] as $strCol)
				{
					$arrReturn[$strCol] = $strCol;
				}					
			}
			// Merge the array to return with the returned array
			// when the column entry is reexecuted through the function
			elseif (is_object ($arrEntry["Column"]))
			{
				array_merge($arrReturn, $arrEntry["Column"]->Columns());
			}
			// Assume it's a string, add entries to return array
			else
			{
				$arrReturn[$arrEntry["Column"]] = $arrEntry["Column"];
			}
		}
		// Return the array
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// WhereArray()
	//------------------------------------------------------------------------//
	/**
	 * WhereArray()
	 *
	 * Assemble a where clause
	 *
	 * Assemble a where clause into an array
	 *
	 * @param		string	arrWhere		an existing array that can be added
	 *
	 * @return		array					array of values from the where clause
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function WhereArray($arrWhere=NULL)
	{
		$arrReturn = Array();
		$intCount = 0;
		foreach ($this->arrInternal as $arrEntry)
		{
			// If the value of the entry is an array, loop through
			if (is_array ($arrEntry["Value"]))
			{
				foreach ($arrEntry["Value"] as $strCol)
				{
					// Format differently for a SEARCH operator
					if ($arrEntry["Eval"] == WHERE_SEARCH)
					{
						// LIKE uses percentage marks
						$arrReturn["index_$intCount"] = "%$strCol%";
					}
					else
					{	
						// Add the value of the entry to the return array
						$arrReturn["index_$intCount"] = $strCol;
					}
					$intCount++;
				}				
			}
			// If the entry is an object
			elseif (is_object ($arrEntry["Value"]))
			{
				// Merge the returned array with an array returned after executing
				// the function on this object
				array_merge($arrReturn, $arrEntry["Value"]->WhereArray($arrWhere));
			}
			// If the column of the entry is an array, loop through
			elseif (is_array ($arrEntry["Column"]))
			{
				// Cycle through each column in the array, but there is
				// only ONE value for all of the columns
				foreach ($arrEntry["Column"] as $strCol)
				{
					// Format differently for a SEARCH operator
					if ($arrEntry["Eval"] == WHERE_SEARCH)
					{
						// LIKE uses percentage marks
						$arrReturn["index_$intCount"] = "%{$arrEntry['Value']}%";
					}
					else
					{	
						// Add the value of the entry (of the column array) to the return array
						$arrReturn["index_$intCount"] = $arrEntry['Value'];
					}
					$intCount++;
				}
			}
			// Assume it's a string
			else
			{
				// Format differently for a SEARCH operator
				if ($arrEntry["Eval"] == WHERE_SEARCH)
				{	
					// LIKE uses percentage marks
					$arrReturn["index_$intCount"] = "%{$arrEntry['Value']}%";
				}
				else
				{	
					// Add the value of the entry to the return array
					$arrReturn["index_$intCount"] = $arrEntry['Value'];
				}
				$intCount++;
			}
		}
		// If another array has been passed in, merge this array to the return array
		if ($arrWhere)
		{
			array_merge($arrReturn, $arrWhere);
		}		
		return $arrReturn; 
	}
	//------------------------------------------------------------------------//
	// WhereString()
	//------------------------------------------------------------------------//
	/**
	 * WhereString()
	 *
	 * Assemble a where clause
	 *
	 * Assemble a where clause
	 *
	 * @param		string	strWhere		an existing where clause to be added
	 *										to the beginning of the constructed
	 *										string
	 * 
	 * @return		string					a valid where clause
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function WhereString($strWhere=NULL)
	{
		$strReturn = $strWhere . $strReturn;
		$intCount = 0;
		
		foreach ($this->arrInternal as $arrEntry)
		{			
			// If the column is an array of fields, loop through columns
			if (is_array ($arrEntry["Column"]))
			{
				$strReturn .= " " . $arrEntry['Type'] . " (";
				$strTemp = "";
				// add an OR between each column
				foreach ($arrEntry["Column"] as $strCol)
				{			
					// Constructing part of the where string depending on evaluation type
					if ($arrEntry['Eval'] == "BETWEEN")
					{
						$strTemp .= " OR (" . $strCol . " " . $arrEntry["Eval"] . " <index_" . $intCount . "> AND <index_" . $intCount + 1 . ">)";
						$intCount++;
					}
					elseif ($arrEntry['Eval'] == WHERE_SEARCH)
					{
						$strTemp .= " OR (" . $strCol . " LIKE <index_" . $intCount . ">)";
					}					
					else
					{
						$strTemp .= " OR (" . $strCol . " " . GetConstantDescription($arrEntry["Eval"], 'Where') . " <index_" . $intCount . ">)";
					}
					$intCount++;
				}
				$arrTemp = explode(' ', $strTemp, 3);
				$strReturn .= $arrTemp[2] . ")";				
			}
			elseif (is_object ($arrEntry["Column"]))
			{
				// If the column is a where object, create a seperate where string for it
				$strReturn .= $arrEntry['Column']->WhereString();
				
			}
			// If the column is a field and can be operated on easily
			else
			{
				// Construction of where clause part if it is to be a BETWEEN part
				if ($arrEntry['Eval'] == "BETWEEN")
				{
					$strReturn .= " " . $arrEntry["Type"] . " (" . $arrEntry["Column"] . " " . $arrEntry['Eval'] . " <index_" . $intCount . "> AND <index_" . ($intCount + 1) . ">)";
					$intCount++;
				}
				// Construction of where clause part if it is to be a LIKE part
				elseif ($arrEntry['Eval'] == WHERE_SEARCH)
				{
					$strReturn .= " " . $arrEntry["Type"] . " (" . $arrEntry["Column"] . " LIKE <index_" . $intCount . ">)";
					
				}	
				// Construction of where clause part if it anything else			
				else
				{
					$strReturn .= " " . $arrEntry["Type"] . " (" . $arrEntry["Column"] . " " . GetConstantDescription($arrEntry["Eval"], 'Where') . " <index_" . $intCount . ">)";
				}
				$intCount++;
			}
		}
		// Append the WhereString that was passed in
		if ($strWhere)
		{
			$strReturn .= $strWhere;
		}

		$arrReturn = explode(' ', $strReturn,3);
		$strReturn = $arrReturn[2];
		return $strReturn; 
	}
}

?>
