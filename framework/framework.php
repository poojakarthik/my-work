<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// FRAMEWORK
//----------------------------------------------------------------------------//
/**
 * FRAMEWORK
 *
 * The framework which links everything
 *
 * The framework which links all of our modules
 *
 * @file		framework.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

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
	 * Framework()
	 *
	 * Constructor for the Framework object
	 *
	 * Constructor for the Framework object
	 *
	 * @method
	 */
	 function __construct()
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
		
		// Init application log
		$this->_strLogFileName	= date("Y-m-d_His", time()).".log";
		if (LOG_TO_FILE && !SAFE_LOGGING && defined(LOG_PATH))
		{
			$this->_ptrLog = fopen(LOG_PATH.$this->_strLogFileName, "a");
		}
		else
		{
			$this->_ptrLog = NULL;
		}
		
		// init statements
		$arrServiceColumns = Array();
		$arrServiceColumns['Shared']			= "RatePlan.Shared";
		$arrServiceColumns['MinMonthly']		= "RatePlan.MinMonthly";
		$arrServiceColumns['ChargeCap']			= "RatePlan.ChargeCap";
		$arrServiceColumns['UsageCap']			= "RatePlan.UsageCap";
		$arrServiceColumns['FNN']				= "Service.FNN";
		$arrServiceColumns['CappedCharge']		= "Service.CappedCharge";
		$arrServiceColumns['UncappedCharge']	= "Service.UncappedCharge";
		$arrServiceColumns['Service']			= "Service.Id";
		$this->selInvoiceTotalServices		= new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, " .
																	"RatePlan",
																	$arrServiceColumns,
																	"Service.Account = <Account> AND RatePlan.Id = ServiceRatePlan.RatePlan AND " .
																	"Service.CreatedOn <= NOW() AND (ISNULL(Service.ClosedOn) OR Service.ClosedOn > NOW()) AND (NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime)",
																	"RatePlan.Id");
																
		$this->selInvoiceTotalDebitsCredits	= new StatementSelect(	"Charge",
																 	"Nature, SUM(Amount) AS Amount",
															 		"Service = <Service> AND Status = ".CHARGE_APPROVED,
															  		NULL,
															  		"2",
															  		"Nature");
		
		//TODO!flame! is this right?		
	 	$this->_selDisputedBalance = new StatementSelect(	"Invoice",
	 														"SUM(Disputed) AS DisputedBalance",
	 														"Account = <Account> AND Status = ".INVOICE_DISPUTED);
	 	
	 	$this->_selAccountBalance = new StatementSelect(	"Invoice",
	 														"SUM(Balance) AS AccountBalance",
	 														"Account = <Account> AND (Balance < 0 OR Status != ".INVOICE_SETTLED.") AND Status != ".INVOICE_TEMP);
		
		$this->_selAccountPayments = new StatementSelect(	"Payment",
															"SUM(Balance) AS TotalBalance",
															"Account = <Account>");
		
		$this->_selAccountOverdueBalance = new StatementSelect(	"Invoice",
	 														"SUM(Balance) - SUM(Disputed) AS OverdueBalance",
	 														"DueOn < NOW() AND Account = <Account> AND (Balance < 0 OR Status != ".INVOICE_SETTLED.") AND Status != ".INVOICE_TEMP);
	 														
		$this->_selFindOwner 			= new StatementSelect("Service", "AccountGroup, Account, Id", "FNN = <fnn> AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
		$this->_selFindOwnerIndial100	= new StatementSelect("Service", "AccountGroup, Account, Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE) AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
		$this->_selFindRecordType		= new StatementSelect("RecordType", "Id, Context", "ServiceType = <ServiceType> AND Code = <Code>", "", "1");
		$this->_selFindRecordCode		= new StatementSelect("RecordTypeTranslation", "Code", "Carrier = <Carrier> AND CarrierCode = <CarrierCode>", "", "1");
		
		$this->_selFindOwnerAccount 			= new StatementSelect("Service", "Id", "FNN = <fnn> AND Account = <Account>", "CreatedOn DESC", "1");
		$this->_selFindOwnerAccountIndial100	= new StatementSelect("Service", "Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE) AND Account = <Account>", "CreatedOn DESC", "1");

		/* BROKEN : NOT USED
		$strTables						= "DestinationCode";
		$strData						= "Id, Code, Description";
		$strWhere						= "Carrier = <Carrier> AND CarrierCode = <CarrierCode> AND Context = <Context>";
		$this->_selFindDestination		= new StatementSelect($strTables, $strData, $strWhere, "", "1");
		*/
		
		$this->_selGetCDR				= new StatementSelect("CDR", "CDR.CDR AS CDR", "Id = <Id>");
		
		$arrColumns						= $GLOBALS['dbaDatabase']->FetchClean("Charge");
		$arrColumns ['CreatedOn']		= new MySQLFunction("NOW()");
		$this->_insCharge				= new StatementInsert("Charge", $arrColumns);
		
		$this->_selFindChargeOwner		= new StatementSelect(	"Account LEFT OUTER JOIN Service ON (Service.Account = Account.Id)",
																"Account.AccountGroup AS AccountGroup, Account.Id AS Account, Service.Id AS Service",
																"( <Account> IS NULL   OR   Account.Id = <Account> ) AND " .
																"( <Service> IS NULL   OR   Service.Id = <Service> )");
																
		$this->_selAccountOverdueCharges	= new StatementSelect(	"Charge",
													"Nature, SUM(Amount) AS Amount",
													"Account = <Account> " .
													" AND Status = ".CHARGE_APPROVED ,
													NULL,
													NULL,
													"Nature");

	 }
	 
	//------------------------------------------------------------------------//
	// Framework - Destructor
	//------------------------------------------------------------------------//
	/**
	 * Framework()
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
		if (LOG_TO_FILE && !SAFE_LOGGING && defined(LOG_PATH))
		{
			fclose($this->_ptrLog);
		}
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
	 	// Are we logging?
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
	 	}
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
	 	
	 	/* UNCOMMENT ME
	 	// Get sum of account payment balances
	 	if ($this->_selAccountPayments->Execute((Array('Account' => $intAccount))) === FALSE)
	 	{
			// ERROR
			return FALSE;
	 	}
	 	$arrAccountPayments = $this->_selAccountBalance->Fetch();
	 	$fltAccountBalance += (float)$arrAccountPayments['TotalBalance'];
	 	*/
	 	
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
		
		// get disputed balance of any invoices
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

		// get balance of unbilled debits & unbilled approved credits
		$this->_selAccountOverdueCharges->Execute(Array('Account' => $intAccount));
		$arrCharges = $this->_selAccountOverdueCharges->FetchAll();

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
		$fltOverdueBalance 				-= max(0, ($fltUnbilledCredits + ($fltUnbilledCredits / 10)));
		
		// return the balance
		return max(0, $fltOverdueBalance);
	 }
	 
	//------------------------------------------------------------------------//
	// GetDistputedBalance()
	//------------------------------------------------------------------------//
	/**
	 * GetDistputedBalance()
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
		
		// Return ex. Tax total
		return $fltTotal;
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
	 	$selInvoicePayments = new StatementSelect("InvoicePayment JOIN Invoice ON (InvoicePayment.InvoiceRun = Invoice.InvoiceRun AND InvoicePayment.Account = Invoice.Account)", $arrCols, "Payment = $intPayment");
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
		
		// Add a note if we have an Account
		$selPayment = new StatementSelect("Payment", "AccountGroup, Account, Amount, PaidOn", "Id = <Id> AND Account IS NOT NULL");
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
			
			$strDate = date("d/M/Y", strtotime($arrPayment['PaidOn']));
			
			// Add the note
			$arrNote = Array();
			$arrNote['Note']			= "$strEmployee Reversed a Payment made on $strDate for \${$arrPayment['Amount']}";
			$arrNote['AccountGroup']	= $arrPayment['AccountGroup'];
			$arrNote['Account']			= $arrPayment['Account'];
			$arrNote['Datetime']		= new MySQLFunction("NOW()");
			$arrNote['NoteType']		= 7;
			$insNote = new StatementInsert("Note", $arrNote);
			$insNote->Execute($arrNote);
		}
		
		return TRUE;
	 }
 }

//----------------------------------------------------------------------------//
// ApplicationBaseClass
//----------------------------------------------------------------------------//
/**
 * ApplicationBaseClass
 *
 * Abstract Base Class for Application Classes
 *
 * Use this class as a base for all application classes
 *
 *
 * @prefix		app
 *
 * @package		framework
 * @class		DatabaseAccess 
 */
 abstract class ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// db
	//------------------------------------------------------------------------//
	/**
	 * db
	 *
	 * Instance of the DataAccess class
	 *
	 * Instance of the DataAccess class
	 *
	 * @type		DataAccess
	 *
	 * @property
	 */
	 public $db;
 	
 	//------------------------------------------------------------------------//
	// Framework
	//------------------------------------------------------------------------//
	/**
	 * Framework
	 *
	 * Instance of the Framework class
	 *
	 * Instance of the Framework class
	 *
	 * @type		Framework
	 *
	 * @property
	 */
	 public $Framework;
 	
 	
 	//------------------------------------------------------------------------//
	// ApplicationBaseClass() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * ApplicationBaseClass()
	 *
	 * Constructor for ApplicationBaseClass
	 *
	 * Constructor for ApplicationBaseClass

	 * @return		void
	 *
	 * @method
	 */ 
	function __construct()
	{
		// connect to database if not already connected
		if (!isset ($GLOBALS['dbaDatabase']) || !$GLOBALS['dbaDatabase'] || !($GLOBALS['dbaDatabase'] instanceOf DataAccess))
		{
			$GLOBALS['dbaDatabase'] = new DataAccess();
		}
		
		// make global database object available
		$this->db = &$GLOBALS['dbaDatabase'];
		
		// make global framework object available
		$this->Framework = &$GLOBALS['fwkFramework'];
		
		// make global error handler available
		$this->_errErrorHandler = $this->Framework->_errErrorHandler;
	}
 }
?>
