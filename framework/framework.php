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
 
//----------------------------------------------------------------------------//
// VixenHelper
//----------------------------------------------------------------------------//
/**
 * VixenHelper
 *
 * Helper functions
 *
 * Helper functions
 *
 *
 * @prefix		hlp
 *
 * @package		framework
 * @class		VixenHelper 
 */
 class VixenHelper
 {
  	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Helper
	 *
	 * Constructor for the Helper
	 * 
	 *
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct()
	{			
		$this->_selFindOwner 			= new StatementSelect("Service", "AccountGroup, Account, Id", "FNN = <fnn> AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
		$this->_selFindOwnerIndial100	= new StatementSelect("Service", "AccountGroup, Account, Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE)AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
		$this->_selFindRecordType		= new StatementSelect("RecordType", "Id, Context", "ServiceType = <ServiceType> AND Code = <Code>", "", "1");
		$this->_selFindRecordCode		= new StatementSelect("RecordTypeTranslation", "Code", "Carrier = <Carrier> AND CarrierCode = <CarrierCode>", "", "1");
		
		$strTables						= "DestinationCode";
		$strData						= "Id, Code, Description";
		$strWhere						= "Carrier = <Carrier> AND CarrierCode = <CarrierCode> AND Context = <Context>";
		$this->_selFindDestination		= new StatementSelect($strTables, $strData, $strWhere, "", "1");
		
		$this->_selGetCDR				= new StatementSelect("CDR", "CDR.CDR AS CDR", "Id = <Id>");


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
	 protected function FindServiceByFNN($strFNN, $intAccount, $strDate=NULL)
	 {
		// TODO!rich! - Why aren't we using $intAccount????????
		
		if ($strDate == NULL)
		{
			$strDate = date("Y-m-d", time());
		}

	 	$intResult = $this->_selFindOwner->Execute(Array("fnn" => (string)$strFNN, "date" => (string)$strDate));
	 	
	 	if ($intResult === FALSE)
	 	{

	 	}
		
	 	if ($arrResult = $this->_selFindOwner->Fetch())
	 	{
			// found the service
	 		return $arrResult['Id'];
	 	}
	 	else
	 	{
	 		$arrParams['fnn'] 	= substr((string)$strFNN, 0, -2) . "__";
	 		$arrParams['date'] 	= date("Y-m-d", time());
	 		$intResult = $this->_selFindOwnerIndial100->Execute($arrParams);
	 		
	 		if ($intResult === FALSE)
	 		{

	 		}
	 		
	 		if(($arrResult = $this->_selFindOwnerIndial100->Fetch()))
	 		{
	 			$this->_arrNormalisedData['AccountGroup']	= $arrResult['AccountGroup'];
	 			$this->_arrNormalisedData['Account']		= $arrResult['Account'];
	 			$this->_arrNormalisedData['Service']		= $arrResult['Id'];
	 			return true;
	 		}
	 	}
	 	
		// Return false if there was no match, or more than one match
		$this->_arrNormalisedData['Status']	= CDR_BAD_OWNER;
		//Debug("Cannot match FNN: ".$this->_arrNormalisedData['FNN']);
		$this->strFNN = $this->_arrNormalisedData['FNN'];
	 	return false;
	 }
 }
?>
