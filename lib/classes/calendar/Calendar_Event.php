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
	public static function parseDescription()
	{
		$arrTokens	= array();
		preg_match_all("/\[(\w+)(\:)(\d+)\]/", $this->description, $arrTokens, PREG_SET_ORDER);
		$arrTokens	= array_unique($arrTokens);
		
		$strParsedDescription	= $this->description;
		foreach($arrTokens as $arrToken)
		{
			$strToken		= $arrToken[0];
			$strObjectName	= $arrToken[1];
			$intObjectId	= (int)$arrToken[3];
			
			switch ($strObjectName)
			{
				case 'CustomerGroup':
					$objCustomerGroup	= Customer_Group::getForId($intObjectId);
					$strReplace			= "<span style='color:{$objCustomerGroup->customerPrimaryColor}'>{$objCustomerGroup->internalName}</span>";
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