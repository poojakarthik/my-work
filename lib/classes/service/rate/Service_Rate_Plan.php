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
	 * Calculates the number of months remaining on this Contract
	 *
	 * @param	[boolean	$bolInclusive		]	TRUE	: The months remaining is rounded up (includes this month) (Default)
	 * 												FALSE	: The months remaining is rounded down (excludes this month)
	 * 
	 * @return	integer
	 * 
	 * @method
	 */
	public function contractMonthsRemaining($bolInclusive=true)
	{
		$intScheduledEndDatetime		= strtotime($this->contract_scheduled_end_datetime);
		//throw new Exception("Scheduled Contract End: {$this->contract_scheduled_end_datetime}");
		
		if ($intScheduledEndDatetime)
		{
			$intCurrentDate		= time();
			$strEndDate			= date("Y-m-d", $intScheduledEndDatetime);
			
			return Flex_Date::difference(date('Y-m-d', $intCurrentDate), $strEndDate, 'm') + 1;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * calculatePayout()
	 *
	 * Calculates the Payout figure for this Contract (excluding global tax)
	 *
	 * @param	[boolean	$bolInclusive		]	TRUE	: The months remaining is rounded up (includes this month) (Default)
	 * 												FALSE	: The months remaining is rounded down (excludes this month)
	 * 
	 * @return	integer
	 * 
	 * @method
	 */
	public function calculatePayout($bolInclusive=true)
	{
		$objContractTerms	= Contract_Terms::getCurrent();
		$objRatePlan		= new Rate_Plan(array('id'=>$this->RatePlan), true);
		
		$fltTotalPayout		= 0.0;
		
		$intInvoiceCount	= $this->getInvoiceCount();
		
		$intContractMonthsRemaining	= $this->contractMonthsRemaining($bolInclusive);
		//throw new Exception("Months Remaining: {$intContractMonthsRemaining}; Invoice Count: {$intInvoiceCount}");
		
		if ($intContractMonthsRemaining)
		{
			// Payout
			$fltTotalPayout	+= ($intInvoiceCount >= $objContractTerms->contract_payout_minimum_invoices) ? (($intContractMonthsRemaining * $objRatePlan->MinMonthly) * ($objRatePlan->contract_payout_percentage / 100)) : 0.0;
			
			// Early Exit Fee
			$fltTotalPayout	+= ($intInvoiceCount >= $objContractTerms->exit_fee_minimum_invoices) ? $objRatePlan->contact_exit_fee : 0.0;
		}
		return round($fltTotalPayout, 2);
	}
	
	/**
	 * getInvoiceCount()
	 *
	 * Gets the number of Invoices this Service Rate Plan record has been included in
	 * 
	 * @return	integer
	 * 
	 * @method
	 */
	public function getInvoiceCount($bolInclusive=true)
	{
		$selInvoiceCount	= self::_preparedStatement('selInvoiceCount');
		if ($selInvoiceCount->Execute($this->toArray()) === false)
		{
			throw new Exception($selInvoiceCount->Error());
		}
		else
		{
			$arrInvoiceCount	= $selInvoiceCount->Fetch();
			return $arrInvoiceCount['invoice_count'];
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
				case 'selInvoiceCount':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceTotal JOIN InvoiceRun ON InvoiceRun.Id = ServiceTotal.invoice_run_id", "COUNT(ServiceTotal.Id) AS invoice_count", "service_rate_plan = <Id> AND InvoiceRun.invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED);
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