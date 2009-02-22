<?php
//----------------------------------------------------------------------------//
// Service_Rate_Plan
//----------------------------------------------------------------------------//
/**
 * Service
 *
 * Models a record of the ServiceRatePlan table
 *
 * Models a record of the ServiceRatePlan table
 *
 * @class	Service
 */
class Service_Rate_Plan extends ORM
{	
	protected	$_strTableName	= "ServiceRatePlan";
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
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
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * contractMonthsRemaining()
	 *
	 * Calculates the number of months remaining on this contract
	 *
	 * @param	[boolean	$bolLoadById		]	TRUE	: The months remaining is rounded up (includes this month) (Default)
	 * 												FALSE	: The months remaining is rounded down (excludes this month)
	 * 
	 * @return	integer
	 * 
	 * @method
	 */
	public function contractMonthsRemaining($bolInclusive=true)
	{
		$intScheduledEndDatetime		= strtotime($this->contract_scheduled_end_datetime);
		
		if ($intScheduledEndDatetime)
		{
			$intCurrentDate		= time();
			$strEndDate			= date("Y-m-d", $intScheduledEndDatetime);
			
			Flex_Date::difference($strEndDate, date('Y-m-d', $intCurrentDate));
			
			//$intCurrentMonths	= self::_dateToMonths($strCurrentDate, $bolInclusive);
			//$intEndMonths		= self::_dateToMonths($strEndDate, $bolInclusive);
		}
		else
		{
			return false;
		}
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceRatePlan", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("ServiceRatePlan");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("ServiceRatePlan");
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