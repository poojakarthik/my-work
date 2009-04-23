<?php
/**
 * Calendar_Event
 *
 * Models a record of the calendar_event table
 *
 * @class	Calendar_Event
 */
class Calendar_Event extends ORM
{
	protected			$_strTableName				= "calendar_event";
	protected static	$_strStaticTableName		= "calendar_event";
	
	protected static	$_arrImportColumns	=	array
												(
													'name'					=> 0,
													'description'			=> 1,
													'departments'			=> 2,
													'start_date'			=> 3
												);
	
	/**
	 * __construct()
	 *
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the object with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($arrProperties=array(), $bolLoadById=false)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * parseDescription()
	 *
	 * Gets an array of Calendar_Events which occur on the given Date
	 *
	 * @param	[mixed	$mixDate				integer	: UNIX Timestamp
	 * 											string	: Date String
	 * 											NULL	: Use today's date (default)]
	 * @param	[bool	$bolIncludeCancelled	TRUE	: Include Cancelled Events
	 * 											FALSE	: Filter Cancelled Events (default)]
	 * 
	 * @return	array							Array of Calendar_Event objects
	 * 
	 * @method
	 */
	public function parseDescription()
	{
		$arrTokens	= array();
		preg_match_all("/\[(\w+)(\:)(\d+)\]/", $this->description, $arrTokens, PREG_SET_ORDER);
		
		$strParsedDescription	= $this->description;
		foreach($arrTokens as $arrToken)
		{
			$strToken		= $arrToken[0];
			$strObjectName	= $arrToken[1];
			$strOperator	= $arrToken[2];
			$intObjectId	= (int)$arrToken[3];
			
			switch ($strObjectName)
			{
				case 'CustomerGroup':
					if ($objCustomerGroup = Customer_Group::getForId($intObjectId))
					{
						$strReplace	= "<span style='color:#{$objCustomerGroup->customerPrimaryColor};'>{$objCustomerGroup->internalName}</span>";
					}
					else
					{
						$strReplace	= "<span style='color:#f00;font-weight:bold;'>[{$strObjectName}{$strOperator}{$intObjectId}!Customer Group with Id {$intObjectId} does not exist]</span>";
					}
					break;
				
				default:
					$strReplace	= $strToken;
					break;
			}
			
			$strParsedDescription	= str_replace($strToken, $strReplace, $strParsedDescription);
		}
		
		return $strParsedDescription;
	}
	
	/**
	 * getForDate()
	 *
	 * Gets an array of Calendar_Events which occur on the given Date
	 *
	 * @param	[mixed	$mixDate				integer	: UNIX Timestamp
	 * 											string	: Date String
	 * 											NULL	: Use today's date (default)]
	 * @param	[bool	$bolIncludeCancelled	TRUE	: Include Cancelled Events
	 * 											FALSE	: Filter Cancelled Events (default)]
	 * 
	 * @return	array							Array of Calendar_Event objects
	 * 
	 * @method
	 */
	public static function getForDate($mixDate=null, $bolIncludeCancelled=false)
	{
		if ($mixDate === null)
		{
			// NULL -- Use Today's date
			//throw new Exception("\$mixDate is NULL");
			$strDate	= date("Y-m-d");
		}
		elseif (is_string($mixDate) && $intDate = strtotime($mixDate))
		{
			// Date String
			//throw new Exception("\$mixDate is a Date string");
			$strDate	= date("Y-m-d", $intDate);
		}
		elseif (is_int($mixDate) && $mixDate > 0)
		{
			// Assume UNIX Timestamp
			//throw new Exception("\$mixDate is a UNIX Timestamp");
			$strDate	= date("Y-m-d", $mixDate);
		}
		else
		{
			$strDateOutputError	= (is_string($mixDate)) ? "'{$mixDate}'" : $mixDate;
			throw new Exception("Parameter mixDate ({$strDateOutputError}) is neither a positive integer, valid date string, or NULL");
		}
		
		//throw new Exception("Getting all Active Events for {$strDate} (Converted from {$mixDate})");
		
		// Pull Events
		$selEventsForDate	= self::_preparedStatement('selEventsForDate');
		$mixResult			= $selEventsForDate->Execute(array('date'=>$strDate, 'include_cancelled'=>(int)$bolIncludeCancelled));
		if ($mixResult === false)
		{
			throw new Exception($selEventsForDate->Error());
		}
		
		// Create and Return resultset of Calendar_Events
		$arrEvents	= array();
		while ($arrEvent = $selEventsForDate->Fetch())
		{
			$arrEvents[]	= new self($arrEvent);
		}
		return $arrEvents;
	}
	
	/**
	 * importFromCSVFile()
	 *
	 * Gets an array of Calendar_Events which occur on the given Date
	 *
	 * @param	string	$strPath				Path to the CSV file to Import from
	 * 
	 * @return	integer							Events imported
	 * 
	 * @method
	 */
	public static function importFromCSVFile($strPath)
	{
		if (!file_exists($strPath))
		{
			throw new Exception("Path {$strPath} does not exist");
		}
		elseif (!($resInputFile = @fopen($strPath, 'r')))
		{
			throw new Exception("Unable to open Path {$strPath} for reading: ".error_get_last());
		}
		
		$strErrorTail	= " in File {$strPath}";
		
		$intLine	= 0;
		
		$arrErrors	= array();
		
		// Parse Header
		$intLine++;
		if (!$arrHeader = fgetcsv($resInputFile))
		{
			throw new Exception("Unable to parse File Header {$strErrorTail}");
		}
		foreach (self::$_arrImportColumns as $strAlias=>$intColumn)
		{
			try
			{
				if (!array_key_exists($intColumn, $arrHeader))
				{
					throw new Exception("Header column at Index {$intColumn} does not exist {$strErrorTail}");
				}
				elseif ($arrHeader[$intColumn] !== $strAlias)
				{
					throw new Exception("Header column at Index {$intColumn} expected '{$strAlias}'; found '{$arrHeader[$intColumn]}' {$strErrorTail}");
				}
				else
				{
					//echo "\t[+] Header Match ('{$strAlias}' === '{$arrHeader[$intColumn]}')\n";
				}
			}
			catch (Exception $eException)
			{
				$arrErrors[]	= $eException->getMessage();
			}
		}
		
		// Parse Data
		$intSuccess	= 0;
		while ($arrData = fgetcsv($resInputFile))
		{
			$intLine++;
			
			try
			{
				if (!($intStartTimestamp = strtotime($arrData[self::$_arrImportColumns['start_date']])))
				{
					throw new Exception("Invalid Start Date '{".$arrData[self::$_arrImportColumns['start_date']]."}' in column ".self::$_arrImportColumns['start_date']);
				}
				
				$arrCalendarEvent	= array(
												'name'						=> trim($arrData[self::$_arrImportColumns['name']]),
												'description'				=> trim($arrData[self::$_arrImportColumns['description']]),
												'department_responsible'	=> trim($arrData[self::$_arrImportColumns['departments']]),
												'start_timestamp'			=> date("Y-m-d H:i:s", $intStartTimestamp),
												'end_timestamp'				=> null,
												'created_employee_id'		=> Employee::SYSTEM_EMPLOYEE_ID,
												'status_id'					=> STATUS_ACTIVE
											);
				
				$objCalendarEvent	= new self($arrCalendarEvent);
				$arrCalendarEvent->save();
				$intSuccess++;
			}
			catch (Exception $eException)
			{
				$arrErrors[]	= $eException->getMessage()." @ Line {$intLine}";
			}
		}
		
		if (count($arrErrors))
		{
			throw new Exception(count($arrErrors)." Fatal Errors were encountered {$strErrorTail}.  No data has been imported into Flex.\n\n:".implode("\n", $arrErrors));
		}
		return $intSuccess;
	}
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selEventsForDate':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	
																					"calendar_event",
																					"*",
																					"CAST(start_timestamp AS DATE) = <date> AND (<include_cancelled> = 1 OR status_id = ".STATUS_ACTIVE.")",
																					"start_timestamp ASC, id"
																				);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>