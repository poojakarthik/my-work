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
class Service_Rate_Plan extends ORM_Cached
{	
	protected			$_strTableName			= "ServiceRatePlan";
	protected static	$_strStaticTableName	= "ServiceRatePlan";

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize()
	{
		return 100;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	
	/**
	 * contractMonthsRemaining()
	 *
	 * Calculates the number of months remaining on this Contract
	 *
	 * @param	[boolean	$bInclusive		]	TRUE	: The months remaining is rounded up (includes this month) (Default)
	 * 											FALSE	: The months remaining is rounded down (excludes this month)
	 * 
	 * @return	integer
	 * 
	 * @method
	 */
	public function contractMonthsRemaining($bInclusive=true)
	{
		$iScheduledEndDatetime		= strtotime($this->contract_scheduled_end_datetime);
		$iEffectiveEndDatetime		= $this->contract_effective_end_datetime ? strtotime($this->contract_effective_end_datetime) : null;
		$iStartDatetime				= strtotime($this->StartDatetime);
		//throw new Exception("Scheduled Contract End: {$this->contract_scheduled_end_datetime}");
		
		if ($iScheduledEndDatetime) {
			$iCurrentDate		= time();
			
			// End Date
			$sEndDate			= date("Y-m-d", $iScheduledEndDatetime + 1);

			// From Date:
			//	Never earlier than the Contract Start
			//	Never later than the Contract End (effective or scheduled)
			$iFromDate			= max($iCurrentDate, $iStartDatetime);
			$iFromDate			= min($iFromDate, $iScheduledEndDatetime, coalesce($iEffectiveEndDatetime, PHP_INT_MAX));
			
			return max(0, min(Flex_Date::difference(date('Y-m-d', $iFromDate), $sEndDate, 'm', 'ceil'), Rate_Plan::getForId($this->RatePlan)->ContractTerm));
		} else {
			return false;
		}
	}
	
	/**
	 * calculatePayout()
	 *
	 * Calculates the Payout figure for this Contract (excluding global tax)
	 *
	 * @param	[boolean	$bInclusive		]	TRUE	: The months remaining is rounded up (includes this month) (Default)
	 * 											FALSE	: The months remaining is rounded down (excludes this month)
	 * 
	 * @return	integer
	 * 
	 * @method
	 */
	public function calculatePayout($bInclusive=true)
	{
		$oContractTerms	= Contract_Terms::getCurrent();
		$oRatePlan		= Rate_Plan::getForId($this->RatePlan);
		
		$fTotalPayout		= 0.0;
		
		$iContractMonthsRemaining	= $this->contractMonthsRemaining($bInclusive);
		//throw new Exception("Months Remaining: {$iContractMonthsRemaining}; Invoice Count: {$iInvoiceCount}");
		
		if ($iContractMonthsRemaining)
		{
			// Payout
			$fTotalPayout	+= coalesce($this->calculateContractPayout($bInclusive), 0.0);
			
			// Early Exit Fee
			$fTotalPayout	+= coalesce($this->calculateContractExitFee($bInclusive), 0.0);
		}
		return Rate::roundToCurrencyStandard($fTotalPayout, 2);
	}

	public function calculateContractPayout($bInclusive=true) {
		$oContractTerms				= Contract_Terms::getCurrent();
		$iInvoiceCount				= $this->getInvoiceCount();
		$oRatePlan					= Rate_Plan::getForId($this->RatePlan);
		$iContractMonthsRemaining	= $this->contractMonthsRemaining($bInclusive);

		$fPayout	= 0.0;
		if ($iContractMonthsRemaining) {
			if ($iInvoiceCount >= $oContractTerms->contract_payout_minimum_invoices) {
				return Rate::roundToCurrencyStandard(($iContractMonthsRemaining * $oRatePlan->MinMonthly) * ($oRatePlan->contract_payout_percentage / 100), 2);
			}
		}
		return null;
	}

	public function calculateContractExitFee($bInclusive=true) {
		$oContractTerms				= Contract_Terms::getCurrent();
		$iInvoiceCount				= $this->getInvoiceCount();
		$oRatePlan					= Rate_Plan::getForId($this->RatePlan);
		$iContractMonthsRemaining	= $this->contractMonthsRemaining($bInclusive);

		if ($iContractMonthsRemaining) {
			if ($iInvoiceCount >= $oContractTerms->exit_fee_minimum_invoices) {
				return Rate::roundToCurrencyStandard($oRatePlan->contract_exit_fee, 2);
			}
		}
		return null;
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
			throw new Exception_Database($selInvoiceCount->Error());
		}
		else
		{
			$arrInvoiceCount	= $selInvoiceCount->Fetch();
			return $arrInvoiceCount['invoice_count'];
		}
	}

        public static function getActiveForService($iServiceId, $sDateTime = null)
        {
            if ($sDateTime === null)
                $sDateTime = Data_Source_Time::currentTimestamp ();
            $oStatement = self::_preparedStatement('selActiveForServiceId');
            if ($oStatement->Execute(array('iServiceId'=>$iServiceId, 'sDateTime'=>$sDateTime)) === false)
            {
                    throw new Exception_Database($selFNNInstances->Error());
            }

            $aRecords = $oStatement->FetchAll();
            return count($aRecords) > 0 ? new self($aRecords[0]) : null;
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
                case 'selActiveForServiceId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Service = <iServiceId> AND <sDateTime> BETWEEN StartDateTime AND EndDatetime", 'CreatedOn DESC, Id DESC', 1);
					break;
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selInvoiceCount':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceTotal JOIN InvoiceRun ON InvoiceRun.Id = ServiceTotal.invoice_run_id", "COUNT(ServiceTotal.Id) AS invoice_count", "service_rate_plan = <Id> AND InvoiceRun.invoice_run_status_id = ".INVOICE_RUN_STATUS_COMMITTED);
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