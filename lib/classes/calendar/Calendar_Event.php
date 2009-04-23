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
	 * getForDate()
	 *
	 * Gets an array of Calendar_Events which occur on the given Date
	 *
	 * @param	[mixed	$mixDate				integer	: UNIX Timestamp
	 * 											string	: Date String
	 * 											NULL	: Use today's date	]
	 * 
	 * @return	array							Array of Calendar_Event objects
	 * 
	 * @method
	 */
	public static function getForDate($mixDate=null)
	{
		if ($mixDate === null)
		{
			// NULL -- Use Today's date
			$strDate	= date("Y-m-d");
		}
		elseif ($intDate = strtotime($mixDate))
		{
			// Date String
			$strDate	= date("Y-m-d", $intDate);
		}
		elseif (is_int($mixDate) && $mixDate > 0)
		{
			// Assume UNIX Timestamp
			throw new Exception("\$mixDate is a UNIX Timestamp");
			$strDate	= date("Y-m-d", $mixDate);
		}
		else
		{
			$strDateOutputError	= (is_string($mixDate)) ? "'{$mixDate}'" : $mixDate;
			throw new Exception("Parameter mixDate ({$strDateOutputError}) is neither a positive integer, valid date string, or NULL");
		}
		
		throw new Exception("Getting all Active Events for {$strDate} (Converted from {$mixDate})");
		
		// Pull Events
		$selEventsForDate	= self::_preparedStatement('selEventsForDate');
		$mixResult			= $selEventsForDate->Execute(array('date'=>$strDate));
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
																					"CAST(start_timestamp AS DATE) = <date>",
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