<?php

//----------------------------------------------------------------------------//
// Framework
//----------------------------------------------------------------------------//
/**
 * Framework
 *
 * The framework which links everything
 *
 * The framework which links all of our modules
 *
 *
 * @prefix	fwk
 *
 * @package	framework
 * @class	Framework
 */
 class Framework
 {
	//------------------------------------------------------------------------//
	// errErrorHandler
	//------------------------------------------------------------------------//
	/**
	 * errErrorHandler
	 *
	 * Handles errors for application
	 *
	 * This object will handle all errors in the application
	 *
	 * @type		ErrorHandler
	 *
	 * @property
	 * @see			ErrorHandler
	 */
	public $errErrorHandler;
	
	//------------------------------------------------------------------------//
	// _intStopwatchTime
	//------------------------------------------------------------------------//
	/**
	 * _intStopwatchTime
	 *
	 * When the stopwatch started
	 *
	 * When the stopwatch started
	 *
	 * @type		integer
	 *
	 * @property
	 */
	private $_intStopwatchTime;
	
	//------------------------------------------------------------------------//
	// _intLapTime
	//------------------------------------------------------------------------//
	/**
	 * _intLapTime
	 *
	 * Time of last LapWatch() call
	 *
	 * Time of last LapWatch() call
	 *
	 * @type		integer
	 *
	 * @property
	 */
	private $_intLapTime;

	//------------------------------------------------------------------------//
	// Framework - Constructor
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Framework object
	 *
	 * Constructor for the Framework object
	 *
	 * @method
	 */
	 function __construct($bolBasicsOnly=FALSE)
	 {
	 	ob_start();
		if (DEBUG_MODE == FALSE)
		{
			error_reporting(0);
		}
	 	$this->_errErrorHandler = new ErrorHandler(); 	
		set_exception_handler(Array($this->_errErrorHandler, "PHPExceptionCatcher"));
		set_error_handler(Array($this->_errErrorHandler, "PHPErrorCatcher"));
		
		// start timing
		$this->_intStartTime		= microtime(TRUE);
		$this->_intStopwatchTime	= microtime(TRUE);
		$this->_intLapTime			= microtime(TRUE);
	}

	public function __set($name, $value)
	{
		$this->{$name} = $value;
	}

	public function __get($name)
	{
		static $selInvoiceTotalServices, $selInvoiceTotalDebitsCredits, $_selDisputedBalance, $_selAccountBalance, $_selAccountPayments, $_selAccountOverdueBalance;
		static $_selFindOwnerIndial100, $_selFindRecordType, $_selFindRecordCode, $_selFindOwnerAccount, $_selFindOwnerAccountIndial100, $_selGetCDR;
		static $_insCharge, $_selFindChargeOwner, $_selAccountUnbilledCharges, $_insAddNote, $_selCheckELB, $_insAddExtension, $_updServiceExtension;
		static $_selFNN, $_selFindOwner, $_selFNNInUse;
		
		switch($name)
		{
			case 'selInvoiceTotalServices':
				if (!isset($selInvoiceTotalServices))
				{
					$arrServiceColumns = Array();
					$arrServiceColumns['Shared']			= "RatePlan.Shared";
					$arrServiceColumns['MinMonthly']		= "RatePlan.MinMonthly";
					$arrServiceColumns['ChargeCap']			= "RatePlan.ChargeCap";
					$arrServiceColumns['UsageCap']			= "RatePlan.UsageCap";
					$arrServiceColumns['FNN']				= "Service.FNN";
					$arrServiceColumns['CappedCharge']		= "Service.CappedCharge";
					$arrServiceColumns['UncappedCharge']	= "Service.UncappedCharge";
					$arrServiceColumns['Service']			= "Service.Id";
					$selInvoiceTotalServices		= new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, " .
																				"RatePlan",
																				$arrServiceColumns,
																				"Service.Account = <Account> AND RatePlan.Id = ServiceRatePlan.RatePlan AND " .
																				"Service.CreatedOn <= NOW() AND (ISNULL(Service.ClosedOn) OR Service.ClosedOn > NOW()) AND (NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime)",
																				"RatePlan.Id");
				}
				return $selInvoiceTotalServices;

			case 'selInvoiceTotalDebitsCredits':
				if (!isset($selInvoiceTotalDebitsCredits))
				{
					$selInvoiceTotalDebitsCredits	= new StatementSelect(	"Charge",
																 	"Nature, SUM(Amount) AS Amount",
															 		"Service = <Service> AND Status = ".CHARGE_APPROVED,
															  		NULL,
															  		"2",
															  		"Nature");
				}
				return $selInvoiceTotalDebitsCredits;
		
			case '_selDisputedBalance':
				if (!isset($_selDisputedBalance))
				{
					//TODO!flame! is this right?		
				 	$_selDisputedBalance = new StatementSelect(	"Invoice",
				 														"SUM(Disputed) AS DisputedBalance",
				 														"Account = <Account> AND Status = ".INVOICE_DISPUTED);
				}
				return $_selDisputedBalance;
		
			case '_selAccountBalance':
				if (!isset($_selAccountBalance))
				{
				 	$_selAccountBalance = new StatementSelect(	"Invoice",
	 														"SUM(Balance) AS AccountBalance",
	 														"Account = <Account> AND (Balance < 0 OR Status NOT IN (".INVOICE_SETTLED.", ".INVOICE_WRITTEN_OFF.")) AND Status != ".INVOICE_TEMP);
				}
				return $_selAccountBalance;
		
			case '_selAccountPayments':
				if (!isset($_selAccountPayments))
				{
					$_selAccountPayments = new StatementSelect(	"Payment",
															"SUM(Balance) AS TotalBalance",
															"Account = <Account> AND Status IN (". PAYMENT_WAITING .", ". PAYMENT_PAYING .", ". PAYMENT_FINISHED .")");
				}
				return $_selAccountPayments;
		
			case '_selAccountOverdueBalance':
				if (!isset($_selAccountOverdueBalance))
				{
					$_selAccountOverdueBalance = new StatementSelect(	"Invoice",
															"SUM(Balance) - SUM(Disputed) AS OverdueBalance",
															"DueOn < NOW() AND Account = <Account> AND (Balance < 0 OR Status NOT IN (".INVOICE_SETTLED.", ".INVOICE_WRITTEN_OFF.")) AND Status != ".INVOICE_TEMP);
				}
				return $_selAccountOverdueBalance;
		
			case '_selFindOwnerIndial100':
				if (!isset($_selFindOwnerIndial100))
				{
					$_selFindOwnerIndial100	= new StatementSelect("Service", "AccountGroup, Account, Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE) AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
				}
				return $_selFindOwnerIndial100;
		
			case '_selFindRecordType':
				if (!isset($_selFindRecordType))
				{
					$_selFindRecordType		= new StatementSelect("RecordType", "Id, Context", "ServiceType = <ServiceType> AND Code = <Code>", "", "1");
				}
				return $_selFindRecordType;
		
			case '_selFindRecordCode':
				if (!isset($_selFindRecordCode))
				{
					$_selFindRecordCode		= new StatementSelect("RecordTypeTranslation", "Code", "Carrier = <Carrier> AND CarrierCode = <CarrierCode>", "", "1");
				}
				return $_selFindRecordCode;
		
			case '_selFindOwnerAccount':
				if (!isset($_selFindOwnerAccount))
				{
					$_selFindOwnerAccount 			= new StatementSelect("Service", "Id", "FNN = <fnn> AND Account = <Account>", "CreatedOn DESC", "1");
				}
				return $_selFindOwnerAccount;
		
			case '_selFindOwnerAccountIndial100':
				if (!isset($_selFindOwnerAccountIndial100))
				{
					$_selFindOwnerAccountIndial100	= new StatementSelect("Service", "Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE) AND Account = <Account>", "CreatedOn DESC", "1");
				}
				return $_selFindOwnerAccountIndial100;
		
			case '_selGetCDR':
				if (!isset($_selGetCDR))
				{
					$_selGetCDR				= new StatementSelect("CDR", "CDR.CDR AS CDR", "Id = <Id>");
				}
				return $_selGetCDR;
		
			case '_insCharge':
				if (!isset($_insCharge))
				{
					$arrColumns						= DataAccess::getDataAccess()->FetchClean("Charge");
					$arrColumns ['CreatedOn']		= new MySQLFunction("NOW()");
					$_insCharge				= new StatementInsert("Charge", $arrColumns);
				}
				return $_insCharge;
		
			case '_selFindChargeOwner':
				if (!isset($_selFindChargeOwner))
				{
					$_selFindChargeOwner		= new StatementSelect(	"Account LEFT OUTER JOIN Service ON (Service.Account = Account.Id)",
																			"Account.AccountGroup AS AccountGroup, Account.Id AS Account, Service.Id AS Service",
																			"( <Account> IS NULL   OR   Account.Id = <Account> ) AND " .
																			"( <Service> IS NULL   OR   Service.Id = <Service> )");
				}
				return $_selFindChargeOwner;
		
			case '_selAccountUnbilledCharges':
				if (!isset($_selAccountUnbilledCharges))
				{
					$_selAccountUnbilledCharges	= new StatementSelect(	"Charge",
																"Nature, SUM(Amount) AS Amount",
																"Account = <Account> " .
																" AND Status = ".CHARGE_APPROVED ,
																NULL,
																NULL,
																"Nature");
				}
				return $_selAccountUnbilledCharges;
		
			case '_insAddNote':
				if (!isset($_insAddNote))
				{
					$arrData = Array();
				 	$arrData['Note']			= NULL;
				 	$arrData['AccountGroup']	= NULL;
				 	$arrData['Contact']			= NULL;
				 	$arrData['Account']			= NULL;
				 	$arrData['Service']			= NULL;
				 	$arrData['Employee']		= NULL;
				 	$arrData['Datetime']		= new MySQLFunction("NOW()");
				 	$arrData['NoteType']		= NULL;			
					$_insAddNote			= new StatementInsert("Note", $arrData);
				}
				return $_insAddNote;
		
			case '_selCheckELB':
				if (!isset($_selCheckELB))
				{
					$_selCheckELB			= new StatementSelect("ServiceExtension", "*", "Service = <Service>");
				}
				return $_selCheckELB;
		
			case '_insAddExtension':
				if (!isset($_insAddExtension))
				{
					$_insAddExtension		= new StatementInsert("ServiceExtension");
				}
				return $_insAddExtension;
		
			case '_updServiceExtension':
				if (!isset($_updServiceExtension))
				{
					$_updServiceExtension	= new StatementUpdate("ServiceExtension", "Service = <Service>", Array("Archived" => NULL));
				}
				return $_updServiceExtension;
		
			case '_selFNN':
				if (!isset($_selFNN))
				{
					$_selFNN				= new StatementSelect("Service", "FNN", "Id = <Service>");
				}
				return $_selFNN;
		
			case '_selFindOwner':
				if (!isset($_selFindOwner))
				{
					$_selFindOwner		= new StatementSelect(	"Service JOIN Account ON Account.Id = Service.Account",
																		"Account.Id AS Account, Service.AccountGroup AS AccountGroup, Service.Id AS Service",
																		"(FNN = <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND " .
																			"((<DateOnly> = 0 AND <DateTime> BETWEEN Service.CreatedOn AND Service.ClosedOn OR (Service.ClosedOn IS NULL AND Service.CreatedOn <= <DateTime>)) OR (<DateOnly> = 1 AND CAST(<DateTime> AS DATE) BETWEEN CAST(Service.CreatedOn AS DATE) AND CAST(Service.ClosedOn AS DATE) OR (Service.ClosedOn IS NULL AND CAST(Service.CreatedOn AS DATE) <= CAST(<DateTime> AS DATE))))",
																		"Service.CreatedOn DESC, Service.Id DESC");
				}
				return $_selFindOwner;
		
			case '_selFNNInUse':
				if (!isset($_selFNNInUse))
				{
					// This is used to check if an FNN is/has-been in use, or is scheduled to be used in the future
					$_selFNNInUse			= new StatementSelect("Service", "Id, Account, AccountGroup", "(FNN LIKE <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND (ClosedOn IS NULL OR (ClosedOn >= CreatedOn AND <DateTime> <= ClosedOn))");
				}
				return $_selFNNInUse;
		
			default: 
				return null;
		}
	}


	//------------------------------------------------------------------------//
	// Framework - Destructor
	//------------------------------------------------------------------------//
	/**
	 * __destruct()
	 *
	 * Desctructor for the Framework object
	 *
	 * Desctructor for the Framework object
	 *
	 * @method
	 */
	 function __destruct()
	 {
		// Close application log
		/*if (LOG_TO_FILE && !SAFE_LOGGING && defined('LOG_PATH'))
		{
			fclose($this->_ptrLog);
		}*/
	 }
	 
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Flushes the output buffer
	 *
	 * Flushes the output buffer
	 *
	 * @method
	 */
	 function Render()
	 {
	 	// render the debug help window
		if (DEBUG_MODE === TRUE)
		{
			//DebugWindow();
		}
		
	 	// flush the output buffer
	 	ob_flush();
	 }

	//------------------------------------------------------------------------//
	// Uptime
	//------------------------------------------------------------------------//
	/**
	 * Uptime()
	 *
	 * How long the process has been running
	 *
	 * How long the process has been running
	 *
	 * @method
	 */
	 function Uptime()
	 {
	 	$intTime = microtime(TRUE);
	 	return round($intTime - $this->_intStartTime, 4);
	 }

	//------------------------------------------------------------------------//
	// StartWatch
	//------------------------------------------------------------------------//
	/**
	 * StartWatch()
	 *
	 * Resets and starts stopwatch
	 *
	 * Resets and starts stopwatch
	 *
	 * @method
	 */
	 function StartWatch()
	 {
	 	$this->_intStopwatchTime	= microtime(TRUE);
	 	$this->_intLapTime			= $this->_intStopwatchTime;
	 }

	//------------------------------------------------------------------------//
	// SplitWatch
	//------------------------------------------------------------------------//
	/**
	 * SplitWatch()
	 *
	 * How long the stopwatch has been running
	 *
	 * How long the stopwatch has been running
	 *
	 * @method
	 */
	 function SplitWatch()
	 {
	 	return round(microtime(TRUE) - $this->_intStopwatchTime, 4);
	 }

	//------------------------------------------------------------------------//
	// LapWatch
	//------------------------------------------------------------------------//
	/**
	 * LapWatch()
	 *
	 * Time since the last LapWatch() call
	 *
	 * Time since the last LapWatch() call
	 *
	 * @method
	 */
	 function LapWatch()
	 {
	 	$intOldLapTime		= $this->_intLapTime;
	 	$this->_intLapTime	= microtime(TRUE);
	 	return round($this->_intLapTime - $intOldLapTime, 4);
	 }
	 
	//------------------------------------------------------------------------//
	// AddToLog()
	//------------------------------------------------------------------------//
	/**
	 * AddToLog()
	 *
	 * Adds a string to the application log
	 *
	 * Adds a string to the application log
	 * 
	 * @param	string	$strText		Text to be added to the log
	 * @param	bool	$bolNewLine		optional TRUE: Append a new line character to the end of the string
	 *
	 * @method
	 */
	 function AddToLog($strText, $bolNewLine = TRUE)
	 {
	 	/*// Are we logging?
	 	if (!LOG_TO_FILE || !defined(LOG_PATH))
	 	{
	 		return;
	 	}
	 	
	 	if ($bolNewLine)
	 	{
	 		$strText .= "\n";
	 	}
	 	
	 	// Are we in safe mode?
	 	if (SAFE_LOGGING)
	 	{
	 		// We need to open the file every time we append.  Huge overhead, but no corrupt files
	 		$this->_ptrLog = fopen(LOG_PATH, "a");
	 		fwrite($this->_ptrLog, $strText);
	 		fclose($this->_ptrLog);
	 	}
	 	else
	 	{
	 		fwrite($this->_ptrLog, $strText);
	 	}*/
	 }
	 
	//------------------------------------------------------------------------//
	// GetAccountBalance()
	//------------------------------------------------------------------------//
	/**
	 * GetAccountBalance()
	 *
	 * Determines the current Account Balance for a specified account
	 *
	 * Determines the current Account Balance for a specified account
	 * Return value includes tax.
	 * 
	 *
	 * @param	integer		$intAccount		The account to determine the balance total for
	 * 
	 * @return	mixed						float: account balance total
	 * 										FALSE: an error occurred
	 *
	 * @method
	 */
	 function GetAccountBalance($intAccount)
	 {	 								
	 	// Get sum of invoice balances
	 	if ($this->_selAccountBalance->Execute(Array('Account' => $intAccount)) === FALSE)
	 	{
			// ERROR
			return FALSE;
	 	}
	 	$arrAccountBalance = $this->_selAccountBalance->Fetch();
	 	$fltAccountBalance = (float)$arrAccountBalance['AccountBalance'];
	 	
	 	// Get sum of account payment balances
	 	if ($this->_selAccountPayments->Execute((Array('Account' => $intAccount))) === FALSE)
	 	{
			// ERROR
			return FALSE;
	 	}
	 	$arrAccountPayments = $this->_selAccountPayments->Fetch();
	 	$fltAccountBalance -= (float)$arrAccountPayments['TotalBalance'];
	 	
	 	return $fltAccountBalance;
	 }
	 
	//------------------------------------------------------------------------//
	// GetOverdueBalance()
	//------------------------------------------------------------------------//
	/**
	 * GetOverdueBalance()
	 *
	 * Determines the current Overdue Balance for a specified account
	 *
	 * Determines the current Overdue Balance for a specified account
	 * = past due invoice balance - disputed balance - unbilled credits
	 * Return amount includes GST
	 *
	 *
	 * @param	integer		$intAccount		The account to determine the overdue balance total for
	 * 
	 * @return	mixed						float: account balance total
	 * 										FALSE: an error occurred
	 *
	 * @method
	 */
	 function GetOverdueBalance($intAccount)
	 {	 						
	 	// get balance of any invoice that is past due
		if ($this->_selAccountOverdueBalance->Execute(Array('Account' => $intAccount)) === FALSE)
	 	{
			// ERROR
			return FALSE;
	 	}
	 	
		// set overdue balance
	 	$arrOverdueBalance = $this->_selAccountOverdueBalance->Fetch();
		if ($arrOverdueBalance)
		{
	 		$fltOverdueBalance = (float)$arrOverdueBalance['OverdueBalance'];
		}
		else
		{
			$fltOverdueBalance = 0;
		}
		
		// get disputed balance of any invoices (does this include tax?????)
		if ($this->_selDisputedBalance->Execute(Array('Account' => $intAccount)) === FALSE)
	 	{
			// ERROR
			return FALSE;
	 	}
	 	
		// remove disputed balance from overdue balance
	 	$arrDisputedBalance = $this->_selDisputedBalance->Fetch();
		if ($arrDisputedBalance)
		{
	 		$fltOverdueBalance -= (float)$arrDisputedBalance['DisputedBalance'];
		}

		// get balance of unbilled debits & unbilled approved credits (does NOT include tax)
		$this->_selAccountUnbilledCharges->Execute(Array('Account' => $intAccount));
		$arrCharges = $this->_selAccountUnbilledCharges->FetchAll();

		foreach($arrCharges as $arrCharge)
		{
			if ($arrCharge['Nature'] == 'DR')
			{
				//$fltUnbilledDebits		= (float)$arrCharge['Amount'];
			}
			else
			{
				$fltUnbilledCredits		= (float)$arrCharge['Amount'];
			}
		}
		
		// remove unbilled credits from overdue balance
		$fltOverdueBalance -= max(0, AddGST($fltUnbilledCredits));
		
		// return the balance
		return max(0, $fltOverdueBalance);
	 }
	
	//------------------------------------------------------------------------//
	// GetUnbilledCharges()
	//------------------------------------------------------------------------//
	/**
	 * GetUnbilledCharges()
	 *
	 * Determines the current unbilled charges (adjustments) for a specified account
	 *
	 * Determines the current unbilled charges (adjustments) for a specified account
	 * Return amount includes GST
	 * 
	 *
	 * @param	integer		$intAccount		The account to determine the unbilled charges total for
	 * 
	 * @return	mixed						float: unbilled charges total
	 * 										FALSE: an error occurred
	 *
	 * @method
	 */
	 function GetUnbilledCharges($intAccount)
	 {	 						
		// get balance of unbilled debits & unbilled approved credits
		$this->_selAccountUnbilledCharges->Execute(Array('Account' => $intAccount));
		$arrCharges = $this->_selAccountUnbilledCharges->FetchAll();

		foreach($arrCharges as $arrCharge)
		{
			if ($arrCharge['Nature'] == 'DR')
			{
				$fltUnbilledDebits		= (float)$arrCharge['Amount'];
				$fltUnbilledDebits		= AddGST($fltUnbilledDebits);
			}
			else
			{
				$fltUnbilledCredits		= (float)$arrCharge['Amount'];
				$fltUnbilledCredits		= AddGST($fltUnbilledCredits);
			}
		}
		
		// return the balance
		return $fltUnbilledDebits - $fltUnbilledCredits;
	 }	
	 
	//------------------------------------------------------------------------//
	// GetDistputedBalance()
	//------------------------------------------------------------------------//
	/**
	 * GetDistputedBalance() (THIS FUNCTION IS SPELT WRONG AND IT DOESN'T LOOK LIKE IT IS USED ANYWHERE)
	 *
	 * Determines the current Disputed Balance for a specified account
	 *
	 * Determines the current Disputed Balance for a specified account
	 * 
	 *
	 * @param	integer		$intAccount		The account to determine the disputed balance total for
	 * 
	 * @return	mixed						float: account balance total
	 * 										FALSE: an error occurred
	 *
	 * @method
	 */
	 function GetDistputedBalance($intAccount)
	 {	 								
	 	if ($this->_selDisputedBalance->Execute(Array('Account' => $intAccount)) === FALSE)
	 	{
			// ERROR
			return FALSE;
	 	}
	 	
	 	$arrDisputedBalance = $this->_selDisputedBalance->Fetch();
	 	return (float)$arrDisputedBalance['DisputedBalance'];
	 }
	
 	//------------------------------------------------------------------------//
	// GetInvoiceTotal()
	//------------------------------------------------------------------------//
	/**
	 * GetInvoiceTotal()
	 *
	 * Determine the total of an invoice before having generated one
	 *
	 * Determine the total of an invoice before having generated one
	 * Returns inc tax total
	 * 
	 *
	 * @param	integer		$intAccount		The account to determine the invoice total for
	 * 
	 * @return	mixed						float: invoice total (ex. tax)
	 * 										FALSE: an error occurred
	 *
	 * @method
	 */
	 function GetInvoiceTotal($intAccount)
	 {
		// zero out totals
		$fltDebits			= 0.0;
		$fltTotalCharge		= 0.0;
		$fltTotalCredits	= 0.0;
		$fltTotalDebits		= 0.0;
		
		// Retrieve list of services for this account
		$this->selInvoiceTotalServices->Execute(Array('Account' => $intAccount));
		if(!$arrServices = $this->selInvoiceTotalServices->FetchAll())
		{
			// No services for this account
			return 0.0;
		}
		
		// Get a list of shared plans for this account
		$arrSharedPlans = Array();
		foreach($arrServices as $arrService)
		{
			if ($arrService['Shared'])
			{
				$arrSharedPlans[$arrService['RatePlan']]['Count']++;
				$arrSharedPlans[$arrService['RatePlan']]['MinMonthly']	= $arrService['MinMonthly'];
				$arrSharedPlans[$arrService['RatePlan']]['UsageCap']	= $arrService['UsageCap'];
				$arrSharedPlans[$arrService['RatePlan']]['ChargeCap']	= $arrService['ChargeCap'];
			}
		}
		
		// for each service belonging to this account
		foreach ($arrServices as $arrService)
		{
			$fltServiceCredits	= 0.0;
			$fltServiceDebits	= 0.0;
			$fltTotalCharge		= 0.0;
			
			if ($arrService['Shared'] > 0)
			{
				// this is a shared plan, add to rateplan count
				$arrSharedPlans[$arrService['RatePlan']]['ServicesBilled']++;
				
				// is this the last Service for this RatePlan?
				if ($arrSharedPlans[$arrService['RatePlan']]['ServicesBilled'] == $arrSharedPlans[$arrService['RatePlan']]['Count'])
				{
					// this is the last service, add min monthly to this service
					$fltMinMonthly 	= max($arrSharedPlans[$arrService['RatePlan']]['MinMonthly'], 0);
				}
				else
				{
					$fltMinMonthly 	= 0;
				}
				$fltUsageCap 		= max($arrSharedPlans[$arrService['RatePlan']]['UsageCap'], 0);
				$fltChargeCap 		= max($arrSharedPlans[$arrService['RatePlan']]['ChargeCap'], 0);
			}
			else
			{
				// this is not a shared plan
				$fltMinMonthly 		= $arrService['MinMonthly'];
				$fltUsageCap 		= $arrService['UsageCap'];
				$fltChargeCap 		= $arrService['ChargeCap'];
			}
			
			// add capped charges
			if ($arrService['ChargeCap'] > 0.0)
			{
				// this is a capped plan
				if ($fltChargeCap > $arrService['CappedCharge'])
				{
					// under the Charge Cap : add the Full Charge
					$fltTotalCharge = (float)$arrService['CappedCharge'];
				}
				elseif ($arrService['UsageCap'] > 0 && $fltUsageCap < $arrService['CappedCharge'])
				{
					// over the Usage Cap : add the Charge Cap + Charge - Usage Cap
					$fltTotalCharge = (float)$fltChargeCap + (float)$arrService['CappedCharge'] - (float)$fltUsageCap;
				}
				else
				{
					// over the Charge Cap, Under the Usage Cap : add Charge Cap
					$fltTotalCharge = (float)$fltChargeCap;
				}
			}
			else
			{
				// this is not a capped plan
				$fltTotalCharge = (float)$arrService['CappedCharge'];
			}
			
			// add uncapped charges
			$fltTotalCharge += (float)$arrService['UncappedCharge'];

			// If there is a minimum monthly charge, apply it
			if ($fltMinMonthly > 0)
			{
				$fltTotalCharge = max($fltMinMonthly, $fltTotalCharge);
			}
			
			// if this is a shared plan
			if ($arrService['Shared'] > 0)
			{
				// remove total charged from min monthly
				$arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] = $arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] - $fltTotalCharge;
				
				// reduce caps
				$arrSharedPlans[$arrService['RatePlan']]['ChargeCap'] -= (float)$arrService['UncappedCharge'];
				$arrSharedPlans[$arrService['RatePlan']]['UsageCap'] -= (float)$arrService['UncappedCharge'];
			}
			
			// Calculate Debit and Credit Totals
			$mixResult = $this->selInvoiceTotalDebitsCredits->Execute(Array('Service' => $arrService['Id']));
			if($mixResult > 2 || $mixResult === FALSE)
			{
				if ($mixResult === FALSE)
				{

				}
				
				// Incorrect number of rows returned or an error
				continue;
			}
			else
			{
				$arrDebitsCredits = $this->selInvoiceTotalDebitsCredits->FetchAll();
				foreach($arrDebitsCredits as $arrCharge)
				{
					if ($arrCharge['Nature'] == "DR")
					{
						$fltServiceDebits	+= $arrCharge['Amount'];
					}
					else
					{
						$fltServiceCredits	+= $arrCharge['Amount'];
					}
				}
			}
			
			// service total
			$fltServiceTotal	= $fltTotalCharge + $fltServiceDebits - $fltServiceCredits;
			
			// add to invoice totals
			$fltTotalDebits		+= $fltServiceDebits + $fltTotalCharge;
			$fltTotalCredits	+= $fltServiceCredits;
		}
		
		// calculate invoice total
		$fltTotal	= $fltTotalDebits - $fltTotalCredits;
		$fltTax		= ceil(($fltTotal / TAX_RATE_GST) * 100) / 100;
		$fltBalance	= $fltTotal + $fltTax;
		
		// Return inc. Tax total
		return AddGST($fltTotal);
	 }
	
	//------------------------------------------------------------------------//
	// FindServiceByFNN
	//------------------------------------------------------------------------//
	/**
	 * FindServiceByFNN()
	 *
	 * finds a service based on the FNN
	 *
	 * finds a service based on the FNN
	 * 
	 *
	 * @return	bool					
	 *
	 * @method
	 */
	 function FindServiceByFNN($strFNN, $strDate=NULL, $intAccount=NULL)
	 {		
		if ($strDate == NULL)
		{
			$strDate = date("Y-m-d", time());
		}
		
		$strDate 	= (string)$strDate;
		$strFNN 	= (string)$strFNN;
		
		$intAccount = (int)$intAccount;
		if ($intAccount)
		{
			$intResult = $this->_selFindOwnerAccount->Execute(Array('fnn' => $strFNN, 'Account' => $intAccount));
			if ($intResult !== FALSE)
			{
				$arrResult = $this->_selFindOwnerAccount->Fetch();		
			}
		}
		else
		{
	 		$intResult = $this->_selFindOwner->Execute(Array('fnn' => $strFNN, 'date' => $strDate));
			if ($intResult !== FALSE)
			{
				$arrResult = $this->_selFindOwner->Fetch();		
			}
	 	}
		
	 	if ($arrResult)
	 	{
			// found the service
	 		return $arrResult['Id'];
	 	}
	 	else
	 	{
	 		$arrParams['fnn'] 		= substr($strFNN, 0, -2) . "__";
			$arrParams['date'] 		= $strDate;
			
			if ($intAccount)
			{
				$arrParams['Account'] 		= $intAccount;
				$intResult = $this->_selFindOwnerAccountIndial100->Execute($arrParams);
				if ($intResult !== FALSE)
				{
					$arrResult = $this->_selFindOwnerAccountIndial100->Fetch();		
				}
			}
			else
			{
	 			$intResult = $this->_selFindOwnerIndial100->Execute($arrParams);
				if ($intResult !== FALSE)
				{
					$arrResult = $this->_selFindOwnerIndial100->Fetch();		
				}
	 		}
	 		
	 		if($arrResult)
	 		{
				// found the service
	 			return $arrResult['Id'];
	 		}
	 	}
	 	
	 	return false;
	 }
	 
	//------------------------------------------------------------------------//
	// UnRateCDR()
	//------------------------------------------------------------------------//
	/**
	 * UnRateCDR()
	 *
	 * UnRates a CDR
	 *
	 * UnRates a CDR
	 *	 
	 * @param	int		$intCDR		Id of the CDR to unrate
	 * @param	int		$intStatus	optional to set for the unrated CDR
	 *
	 * @return	bool
	 *
	 * @method
	 */
	 function UnRateCDR($intCDR, $intStatus = CDR_NORMALISED)
	 {
	 	$intCDR = (int)$intCDR;
	 	if (!$intCDR)
		{
			return FALSE;
		}
		
	 	// Select the CDR
	 	$selCDR = new StatementSelect(	"CDR JOIN Rate ON CDR.Rate = Rate.Id",
	 									"CDR.Id AS Id, CDR.Service AS Service, CDR.Charge AS Charge, Rate.Uncapped AS Uncapped",
	 									"CDR.Id = $intCDR AND CDR.Status = ".CDR_RATED,
	 									NULL,
	 									"1");
		$arrColumns = Array();
	 	$arrColumns['UncappedCharge']	= new MySQLFunction("UncappedCharge - <UncappedCharge>");
	 	$arrColumns['CappedCharge']		= new MySQLFunction("CappedCharge - <CappedCharge>");
	 	$updServiceTotals = new StatementUpdate("Service", "Id = <Service>", $arrColumns);
		
		$arrColumns = Array();
	 	$arrColumns['Status']	= CDR_NORMALISED;
	 	$ubiCDR = new StatementUpdateById("CDR", $arrColumns);
		
	 	if (!$selCDR->Execute())
	 	{
	 		return FALSE;
	 	}
		
	 	while($arrCDR = $selCDR->Fetch())
	 	{	 	
		 	// Uncapped or Capped
			$arrColumns = Array();
		 	if ($arrCDR['Uncapped'])
		 	{
		 		$arrColumns['UncappedCharge']	= new MySQLFunction("UncappedCharge - <UncappedCharge>", Array('UncappedCharge' => $arrCDR['Charge']));
		 		$arrColumns['CappedCharge']		= new MySQLFunction("CappedCharge - <CappedCharge>", Array('CappedCharge' => 0));
		 	}
		 	else
		 	{
		 		$arrColumns['CappedCharge']		= new MySQLFunction("CappedCharge - <CappedCharge>", Array('CappedCharge' => $arrCDR['Charge']));
		 		$arrColumns['UncappedCharge']	= new MySQLFunction("UncappedCharge - <UncappedCharge>", Array('UncappedCharge' => 0));
		 	}
		 	
		 	// Update the Service
		 	if ($updServiceTotals->Execute($arrColumns, Array('Service' => $arrCDR['Service'])) === FALSE)
		 	{
		 		return FALSE;
		 	}
			
			// update the CDR
			$arrColumns = Array();
			$arrColumns['Id']		= $arrCDR['Id'];
	 		$arrColumns['Status']	= $intStatus;
			if($ubiCDR->Execute($arrColumns))
			{
				return TRUE;
			}
	 	}
		return FALSE;
	 }
	 
	
 	//------------------------------------------------------------------------//
	// FindRecordType
	//------------------------------------------------------------------------//
	/**
	 * FindRecordType()
	 *
	 * Find the record type from a Service Type & Record Code
	 *
	 * Find the record type from a Service Type & Record Code
	 * 
	 *
	 * @param	int		intServiceType		Service Type Constant
	 * @param	string	strRecordCode		Vixen Record Type Code
	 * @return	int		Record Type Id					
	 *
	 * @method
	 */
	 function FindRecordType($intServiceType, $strRecordCode)
	 {

	 	$intResult = $this->_selFindRecordType->Execute(Array("ServiceType" => $intServiceType, "Code" => $strRecordCode));
		
		if ($intResult === FALSE)
		{
			return false;
		}
		
	 	if ($arrResult = $this->_selFindRecordType->Fetch())
	 	{
	 		return $arrResult['Id'];
	 	}
		
		// Return false if there was no match
	 	return false;
	 }
	 
 	//------------------------------------------------------------------------//
	// AddCharge
	//------------------------------------------------------------------------//
	/**
	 * AddCharge()
	 *
	 * Find the record type from a Service Type & Record Code
	 *
	 * Find the record type from a Service Type & Record Code
	 * 
	 *
	 * @param	int		intServiceType		Service Type Constant
	 * @param	string	strRecordCode		Vixen Record Type Code
	 * @return	int		Record Type Id					
	 *
	 * @method
	 */
	 function AddCharge($arrCharge)
	 {
		// make sure we have enough data to insert...
		if ($arrCharge['Account'] === NULL)
		{
			// not enough data to define ownership
			return FALSE;
		}
		
		// Grab ownership data
		if (!$arrCharge['Account'] || !$arrCharge['AccountGroup'])
		{
			if ($this->_selFindChargeOwner->Execute($arrCharge) === FALSE)
			{
				Debug($this->_selFindChargeOwner);
				return FALSE;
			}
			$arrResponse = $this->_selFindChargeOwner->Fetch();
			
			// Only use data we need
			if (!$arrCharge['Account'])
			{
				$arrCharge['Account'] = $arrResponse['Account'];
			}
			if (!$arrCharge['AccountGroup'])
			{
				$arrCharge['AccountGroup'] = $arrResponse['AccountGroup'];
			}
		}
		
		// merge with default data
		$arrDefaultCharge ['Nature']		= 'DR';
		$arrDefaultCharge ['Invoice']		= NULL;
		$arrDefaultCharge ['Notes']			= "";
		$arrDefaultCharge ['Description']	= "";
		$arrDefaultCharge ['ChargeType']	= "";
		$arrDefaultCharge ['Amount']		= 0.0;
		$arrDefaultCharge ['Status']		= CHARGE_APPROVED;
		$arrCharge = array_merge($arrDefaultCharge, $arrCharge);
		
		// set date
		$arrCharge ['CreatedOn']			= new MySQLFunction("NOW()");
		
		// Insert into DB
		$insId = $this->_insCharge->Execute($arrCharge);
		
		if ($insId === FALSE)
		{
			Debug($this->_insCharge->Error());
		}
		
		return $insId;
	 }
	 
	 
	//------------------------------------------------------------------------//
	// ReversePayment
	//------------------------------------------------------------------------//
	/**
	 * ReversePayment()
	 *
	 * Reverses a specified Payment
	 *
	 * Reverses a specified Payment
	 * 
	 * @param	integer	$intPayment		the Id of the Payment to reverse
	 * @param	integer	$intEmployee	optional Id of the Employee who reversed
	 *
	 * @return	boolean					whether the removal was successful or not
	 *
	 * @method
	 */
	 function ReversePayment($intPayment, $intReversedBy = NULL)
	 {
	 	// Check validity 	
	 	if (!is_int($intPayment) || !$intPayment)
	 	{
			return FALSE;
	 	}
	 	
	 	// Find all InvoicePayments
	 	$arrCols = Array();
	 	$arrCols['Amount']	= 'InvoicePayment.Amount';
	 	$arrCols['Status']	= 'Invoice.Status';
	 	$arrCols['Balance']	= 'Invoice.Balance';
	 	$arrCols['Invoice']	= 'Invoice.Id';
	 	$arrCols['Id']		= 'InvoicePayment.Id';
	 	$selInvoicePayments = new StatementSelect("(Payment JOIN InvoicePayment ON Payment.Id = InvoicePayment.Payment) JOIN Invoice ON (InvoicePayment.InvoiceRun = Invoice.InvoiceRun AND InvoicePayment.Account = Invoice.Account)", $arrCols, "Payment.Id = $intPayment AND Payment.Account = InvoicePayment.Account AND Payment.Account = Invoice.Account");
	 	$selInvoicePayments->Execute();
	 	$arrInvoicePayments = $selInvoicePayments->FetchAll();
	 	$qryDelete = new Query();
	 	foreach ($arrInvoicePayments as $arrInvoicePayment)
	 	{
			// Add to Invoice Balance & set new Status
			$arrData = Array();
			$arrData['Id']	= $arrInvoicePayment['Invoice'];
			$arrData['Balance']	= new MySQLFunction("Balance + <Payment>", Array('Payment' => $arrInvoicePayment['Amount']));
			$arrData['Status']	= INVOICE_COMMITTED;
			$ubiInvoice = new StatementUpdateById("Invoice", $arrData);
			$ubiInvoice->Execute($arrData);
			
			// Remove InvoicePayment
			$qryDelete->Execute("DELETE FROM InvoicePayment WHERE Id = {$arrInvoicePayment['Id']}");
	 	}
	 	
	 	// Set Payment Balance to Amount and set status to Reversed
		$arrData = Array();
		$arrData['Id']		= $intPayment;
		$arrData['Balance']	= 0.0;
		$arrData['Status']	= PAYMENT_REVERSED;
		$ubiPayment = new StatementUpdateById("Payment", $arrData);
		$ubiPayment->Execute($arrData);
		
		// Remove or Credit any associated Surcharges
		$arrReversedCharges = Array();
		$bolChargesReversed = FALSE;
		$arrCols = Array();
		$arrCols['Status']	= CHARGE_DELETED;
		$ubiSurcharge	= new StatementUpdateById("Charge", $arrCols);
		$insCredit		= new StatementInsert("Charge");
		$selSurcharges	= new StatementSelect("Charge", "*", "Nature = 'DR' AND LinkId = <Payment> AND LinkType = ".CHARGE_LINK_PAYMENT);
		$selSurcharges->Execute(Array('Payment' => $intPayment));
		while ($arrSurcharge = $selSurcharges->Fetch())
		{
			// Is it Invoiced?
			switch ($arrSurcharge['Status'])
			{
				case CHARGE_INVOICED:
					// Add a credit to negate the charge
					$arrCredit					= $arrSurcharge;
					$arrCredit['CreatedOn']		= date("Y-m-d");
					$arrCredit['ChargedOn']		= date("Y-m-d");
					$arrCredit['CreatedBy']		= $intReversedBy;
					$arrCredit['ApprovedBy']	= NULL;
					$arrCredit['Nature']		= 'CR';
					$arrCredit['Description']	= "Payment Reversal: ". $arrCredit['Description'];
					$arrCredit['Status']		= CHARGE_APPROVED;
					unset($arrCredit['Id']);
					$insCredit->Execute($arrCredit);
					
					// Append appriate message to the list of messages for the system note
					$arrReversedCharges[] = "A new adjustment has been created to credit the Account: {$arrCredit['Account']} for the invoiced payment surcharge of \$". number_format(AddGST($arrCredit['Amount']), 2, ".", "");
					$bolChargesReversed = TRUE;
					
					break;
				
				case CHARGE_APPROVED:
					// Set the charge status to Deleted
					$arrSurcharge['Status']	= CHARGE_DELETED;
					$ubiSurcharge->Execute($arrSurcharge);
					
					// Append appriate message to the list of messages for the system note
					$arrReversedCharges[] = "The yet-to-be-invoiced surcharge adjustment of \$". number_format(AddGST($arrSurcharge['Amount']), 2, ".", "") ." has been deleted from Account: {$arrSurcharge['Account']}";
					$bolChargesReversed = TRUE;
					break;
			}
		}
		
		// Add a note if we have an Account
		$selPayment = new StatementSelect("Payment", "AccountGroup, Account, Amount, PaidOn", "Id = <Id>");
		if ($selPayment->Execute($arrData))
		{
			$arrPayment = $selPayment->Fetch();
			
			// Do we have an employee?
			if ($intReversedBy)
			{
				$selEmployee = new StatementSelect("Employee", "CONCAT(FirstName, ' ', LastName) AS FullName", "Id = $intReversedBy");
				$selEmployee->Execute();
				$arrEmployee = $selEmployee->Fetch();
				$strEmployee = $arrEmployee['FullName'];
			}
			else
			{
				$strEmployee = "Administrators";
			}
			
			$strDate = date("d/m/Y", strtotime($arrPayment['PaidOn']));
			
			// Work out if the payment was applied to an AccountGroup, or a specific Account
			if ($arrPayment['Account'] != NULL)
			{
				// The payment has been made to a specific account
				$strAccountClause = "a Payment";
			}
			else
			{
				// The payment has been applied to an AccountGroup
				$strAccountClause = "an AccountGroup Payment";
			}
			
			// Build the Reversed Charges clause
			if ($bolChargesReversed)
			{
				$strReversedChargesClause = "\nThe following associated actions have also taken place:\n" . implode("\n", $arrReversedCharges);
			}
			
			
			// Add the note
			$arrNote = Array();
			$arrNote['Note']			= "$strEmployee Reversed $strAccountClause made on $strDate for \$". number_format($arrPayment['Amount'], 2, ".", "") . $strReversedChargesClause;
			$arrNote['AccountGroup']	= $arrPayment['AccountGroup'];
			$arrNote['Account']			= $arrPayment['Account'];
			$arrNote['Datetime']		= new MySQLFunction("NOW()");
			$arrNote['NoteType']		= 7;
			$insNote = new StatementInsert("Note", $arrNote);
			$insNote->Execute($arrNote);
		}
		
		return TRUE;
	 }
	 
	 
	
	//------------------------------------------------------------------------//
	// AddNote
	//------------------------------------------------------------------------//
	/**
	 * AddNote()
	 *
	 * Adds a Note to a particular AccountGroup, Account, Service, or Contact
	 *
	 * Adds a Note to a particular AccountGroup, Account, Service, or Contact.
	 * You can specify either a Service OR a Contact to add a note to.
	 *
	 * @param	string	$strContent						Note content
	 * @param	integer	$intType						The Type of note to insert
	 * @param	integer	$intEmployee					Employee who is making the note
 	 * @param	integer	$intAccountGroup				The AccountGroup to add a note to
	 * @param	integer	$intAccount						The Account to add a note to
	 * @param	integer	$intService			optional	The Service to add a note to
	 * @param	integer	$intContact			optional	The Contact to add a note to
	 *
	 * @return	boolean
	 */
	 function AddNote($strContent, $intType, $intEmployee, $intAccountGroup, $intAccount, $intService=NULL, $intContact=NULL)
	 {
	 	// Sanity Check
	 	if ($intService && $intContact)
	 	{
	 		// Can't have both Contact and Service
	 		return FALSE;
	 	}
	 	
	 	// Insert the note
	 	$arrData = Array();
	 	$arrData['Note']			= (string)$strContent;
	 	$arrData['AccountGroup']	= $intAccountGroup;
	 	$arrData['Contact']			= $intContact;
	 	$arrData['Account']			= $intAccount;
	 	$arrData['Service']			= $intService;
	 	$arrData['Employee']		= $intEmployee;
	 	$arrData['Datetime']		= new MySQLFunction("NOW()");
	 	$arrData['NoteType']		= $intType;
	 	return (bool)$this->_insAddNote->Execute($arrData);
	 }
	 
	 
		 
	//------------------------------------------------------------------------//
	// EnableELB
	//------------------------------------------------------------------------//
	/**
	 * EnableELB()
	 *
	 * Enables Extension-Level Billing for the specified Service
	 *
	 * Enables Extension-Level Billing for the specified Service.  It will not add
	 * new entries if there are old ones archived
	 *
	 * @param	integer	$intService		The Service to enable ELB on
	 *
	 * @return	boolean					Pass/Fail				
	 */
	 function EnableELB($intService)
	 {	 	
	 	// Check for ELB in table
	 	$arrWhere = Array();
	 	$arrWhere['Service']	= $intService;
	 	if ($this->_selCheckELB->Execute($arrWhere))
	 	{
	 		// Unarchive the old data
	 		return ($this->_updServiceExtension->Execute(Array('Archived' => 0), $arrWhere) === FALSE) ? FALSE : TRUE;
	 	}
	 	else
	 	{
	 		$this->_selFNN->Execute($arrWhere);
	 		$arrData = $this->_selFNN->Fetch();
	 		
	 		// Insert new data
	 		for ($i = 0; $i < 100; $i++)
	 		{
	 			$arrData['RangeStart']	= $arrData['RangeEnd'] = $i;
	 			$arrData['Name']		= substr($arrData['FNN'], 0, -2) . str_pad($arrData['RangeStart'], 2, '0', STR_PAD_LEFT);
	 			$arrData['Service']		= $intService;
	 			$arrData['Archived']	= 0;
	 			if (!$this->_insAddExtension->Execute($arrData))
	 			{
	 				return FALSE;
	 			}
	 		}
	 		
	 		return TRUE;	 		
	 	}
	 }
	 
	 
		 
	//------------------------------------------------------------------------//
	// DisableELB
	//------------------------------------------------------------------------//
	/**
	 * DisableELB()
	 *
	 * Disables Extension-Level Billing for the specified Service
	 *
	 * Disables Extension-Level Billing for the specified Service
	 *
	 * @param	integer	$intService		The Service to disable ELB on
	 *
	 * @return	boolean					Pass/Fail				
	 */
	 function DisableELB($intService)
	 {
	 	// Archive the ELB data
	 	$arrWhere = Array();
	 	$arrWhere['Service']	= $intService;
	 	return ($this->_updServiceExtension->Execute(Array('Archived' => 1), $arrWhere) === FALSE) ? FALSE : TRUE;
	 }
	 
	 	 
		 
	//------------------------------------------------------------------------//
	// FindFNNOwner
	//------------------------------------------------------------------------//
	/**
	 * FindFNNOwner()
	 *
	 * Finds the Owner of a given FNN
	 *
	 * Finds the Owner of a given FNN
	 *
	 * @param	string	$strFNN					The FNN to own
	 * @param	integer	$strDatetime			The Datetime to own on
	 * @param	boolean	$bolDateOnly			Date comparison only (instead of Datetime)
	 *
	 * @return	mixed							Array of Owner Details or String Error				
	 */
	 function FindFNNOwner($strFNN, $strDatetime, $bolDateOnly=FALSE)
	 {
	 	// Check Data
	 	if (!IsValidFNN($strFNN))
	 	{
	 		// Invalid FNN
	 		return "'$strFNN' is not a valid FNN!";
	 	}
	 	if (!strtotime($strDatetime))
	 	{
	 		// Invalid Date
	 		return "'$strDatetime' is not a valid Datetime String!";
	 	}
	 	
	 	// Find the Owner
	 	$arrWhere = Array();
	 	$arrWhere['FNN']			= $strFNN;
	 	$arrWhere['DateTime']		= $strDatetime;
	 	$arrWhere['IndialRange']	= substr($strFNN, 0, -2).'__';
	 	$arrWhere['DateOnly']		= (int)$bolDateOnly;
	 	$mixResult	= $this->_selFindOwner->Execute($arrWhere);
		
	 	if ($mixResult === FALSE)
	 	{
	 		// Error
	 		return $this->_selFindOwner->Error();
	 	}
	 	elseif (!$mixResult)
	 	{
	 		// No Result
	 		return "FNN '$strFNN' not found in Flex!";
	 	}
	 	
	 	// Return Owner Details	 	
	 	return $this->_selFindOwner->Fetch();
	 }
	 
	//------------------------------------------------------------------------//
	// IsFNNInUse
	//------------------------------------------------------------------------//
	/**
	 * IsFNNInUse()
	 *
	 * Checks if an FNN is/has-been in use, or is scheduled to be used in the future, since the given date
	 *
	 * Checks if an FNN is/has-been in use, or is scheduled to be used in the future, since the given date
	 *
	 * @param	string	$strFNN					The FNN to check
	 * @param	bool	$bolIsIndial			TRUE If the FNN to check is an Indial100
	 * @param	string	$strDate				The date to check from
	 *
	 * @return	mixed							TRUE if the FNN is/has been in use since $strDate, or is scheduled to be used
	 * 											beyond this date
	 * 											FALSE if the FNN isn't in use and is not scheduled to be used
	 * 											String if there is an error				
	 */
	 function IsFNNInUse($strFNN, $bolIsIndial, $strDate)
	 {
	 	// Check Data
	 	if (!IsValidFNN($strFNN))
	 	{
	 		// Invalid FNN
	 		return "'$strFNN' is not a valid FNN!";
	 	}
	 	if (!strtotime($strDate))
	 	{
	 		// Invalid Date
	 		return "'$strDate' is not a valid Date String!";
	 	}
	 	
	 	// Prepare the FNN
	 	$arrWhere = Array();
	 	if ($bolIsIndial)
	 	{
	 		// Make sure none of the numbers in the FNN's Indial100 range are being used
	 		$arrWhere['FNN']		= substr($strFNN, 0, -2) . '__';
	 	}
	 	else
	 	{
	 		$arrWhere['FNN']		= $strFNN;
	 	}
	 	$arrWhere['DateTime']		= $strDate;
	 	$arrWhere['IndialRange']	= substr($strFNN, 0, -2) . '__';
	 
	 	
	 	$mixResult	= $this->_selFNNInUse->Execute($arrWhere);

	 	if ($mixResult === FALSE)
	 	{
	 		// Error
	 		return $this->_selFNNInUse->Error();
	 	}
	 	
	 	return (bool)($mixResult > 0);
	 }
	 
 }

?>
