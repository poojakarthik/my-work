<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		rating_application
 * @author		Jared 'flame' Herbohn
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

echo "<pre>";

// Application entry point - create an instance of the application object
$appRating = new ApplicationRating($arrConfig);

// Change status of all CDRs with missing rate 
$appRating->ReRate(CDR_RATE_NOT_FOUND);

// run the Rate method until there is nothing left to rate
while ($appRating->Rate())
{
	//REMOVE FOR LIVE SYSTEM
	// break here to only rate 1000 CDRs
	//break;
}

//TODO!!!! - send the report

// finished
echo("\n-- End of Rating --\n");
echo "</pre>";
die();



//----------------------------------------------------------------------------//
// ApplicationRating
//----------------------------------------------------------------------------//
/**
 * ApplicationRating
 *
 * Rating Module
 *
 * Rating Module
 *
 *
 * @prefix		app
 *
 * @package		rating_application
 * @class		ApplicationRating
 */
 class ApplicationRating extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// _rptRatingReport
	//------------------------------------------------------------------------//
	/**
	 * _rptRatingReport
	 *
	 * Rating report
	 *
	 * Rating Report, including information on errors, failed ratings,
	 * and a total of each
	 *
	 * @type		Report
	 *
	 * @property
	 */
	private $_rptRatingReport;	
 	
 	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Rating Application
	 *
	 * Constructor for the Rating Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
	 	// Initialise framework components
		$this->_rptRatingReport = new Report("Rating Report for " . date("Y-m-d H:i:s"), "flame@telcoblue.com.au");
		
		$this->_rptRatingReport->AddMessage("\n".MSG_HORIZONTAL_RULE.MSG_RATING_TITLE, FALSE);
		
		// Init Statement
		$this->_ubiServiceTotalsCapped		= new StatementUpdateById("Service", Array('CappedCharge' => NULL));
		$this->_ubiServiceTotalsUncapped	= new StatementUpdateById("Service", Array('UncappedCharge' => NULL));
		
		$this->_selFleetAccount		= new StatementSelect(	"RateGroup JOIN ServiceRateGroup ON RateGroup.Id = ServiceRateGroup.RateGroup, Service",
															"Service.Account AS Account",
															"ServiceRateGroup.Service = <Service>" .
															" AND RateGroup.RecordType = <RecordType>" .
															" AND RateGroup.ServiceType = <ServiceType>" .
															" AND Service.Id = ServiceRateGroup.Service");
								
		$strWhere					= "(ISNULL(ClosedOn) OR ClosedOn > <Date>) ";
		$strWhere					.="AND (FNN = <FNN> OR (FNN != <FNN> AND Indial100 = 1 AND FNN LIKE <Prefix>))";	
		$this->_selServiceByFNN		= new StatementSelect(	"Service",
															"Id",
															$strWhere, 'CreatedOn DESC', '1');
		
		// Init Rate finding (aka Dirty Huge Donkey) Query
		$strTables					=	"Rate JOIN RateGroupRate ON Rate.Id = RateGroupRate.Rate, " .
										"RateGroup JOIN RateGroupRate AS RateGroupRate2 ON RateGroup.Id = RateGroupRate2.RateGroup, " .
										"ServiceRateGroup JOIN RateGroup AS RateGroup2 ON ServiceRateGroup.RateGroup = RateGroup2.Id";
										
		$strWhere					=	"RateGroupRate.Id 				= RateGroupRate2.Id AND \n" .
										"RateGroup.Id 					= RateGroup2.Id AND \n" .
										"ServiceRateGroup.Service 		= <Service> AND \n" .
										"ServiceRateGroup.StartDateTime	<= <DateTime> AND \n" .
										"ServiceRateGroup.EndDateTime	>= <DateTime> AND \n" .
										"Rate.RecordType				= <RecordType> AND \n" .
										"Rate.Destination 				= <Destination> AND \n" .
										"Rate.StartTime					<= <Time> AND \n" .
										"Rate.EndTime 					>= <Time> AND \n" .
										"( Rate.Monday					= <Monday> OR \n" .
										"Rate.Tuesday					= <Tuesday> OR \n" .
										"Rate.Wednesday					= <Wednesday> OR \n" .
										"Rate.Thursday					= <Thursday> OR \n" .
										"Rate.Friday					= <Friday> OR \n" .
										"Rate.Saturday					= <Saturday> OR \n" .
										"Rate.Sunday					= <Sunday> ) \n";
										
		//FAKE : for testing only
		//$strTables = "Rate";
		//$strWhere  = "1 = 1";
		//$this->_selFindRate			= new StatementSelect($strTables, "Rate.*", $strWhere, "", 1);
		
		$this->_selFindRate			= new StatementSelect($strTables, "Rate.*", $strWhere, "ServiceRateGroup.CreatedOn DESC", 1);
		
		// fleet rate query
		$strWhere					.=	"AND Rate.Fleet 				= 1 \n";
		$this->_selFindFleetRate	= new StatementSelect($strTables, "Rate.*", $strWhere, "ServiceRateGroup.CreatedOn DESC", 1);
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// Rate
	//------------------------------------------------------------------------//
	/**
	 * Rate()
	 *
	 * Rate CDR Records
	 *
	 * Rates CDR Records
	 * Rates the next batch of 1000 normalised ready to rate CDRs in the database
	 *
	 * @return	bool	returns true untill all CDRs have been rated
	 * @method
	 */
	 function Rate()
	 {
	 	// get list of CDRs to rate (limit results to 1000)
	 	$selGetCDRs = new StatementSelect("CDR", "*", "Status = ".CDR_NORMALISED." OR Status = ".CDR_RERATE, "Status ASC", "1000");
	 	$selGetCDRs->Execute();
		$arrCDRList = $selGetCDRs->FetchAll();
		
		// we will return FALSE if there are no CDRs to rate
		$bolReturn = FALSE;
		$updSaveCDR = new StatementUpdateById("CDR");
		
		$this->Framework->StartWatch();
		
		$intPassed = 0;
		$intFailed = 0;
		
		// Loop through each CDR
		foreach($arrCDRList as $arrCDR)
		{
			// return TRUE if we have rated (or tried to rate) any CDRs
			$bolReturn = TRUE;
		
			// Report
			$arrAlises['<SeqNo>'] = str_pad($arrCDR['Id'], 60, " ");
			$this->_rptRatingReport->AddMessageVariables(MSG_LINE, $arrAlises, FALSE);
			
			// set current CDR
			$this->_arrCurrentCDR = $arrCDR;

			// Find Rate for this CDR
			if (!$this->_arrCurrentRate = $this->_FindRate())
			{
				// rate not found
				// set status in database
				$arrCDR['Status']	= CDR_RATE_NOT_FOUND;
				$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
				$updSaveCDR->Execute($arrCDR);
				
				// add to report
				$arrAlises['<Reason>'] = "Rate not found";
				$this->_rptRatingReport->AddMessageVariables(MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);
				
				$intFailed++;
				continue;
			}
			
			if ($this->_arrCurrentRate['PassThrough'])
			{
				// Calculate Passthrough rate
				$fltCharge = $this->_CalculatePassThrough();
			}
			else
			{
				// Calculate other rate types
				
				// Calculate Charge
				$fltCharge = $this->_CalculateCharge();
				if ($fltCharge === FALSE)
				{
					// Charge calculation failed
					// THIS SHOULD NEVER HAPPEN
	
					$arrCDR['Status'] = CDR_UNABLE_TO_RATE;
					$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
					$updSaveCDR->Execute($arrCDR);
					
					// add to report
					$arrAlises['<Reason>'] = "Base charge calculation failed";
					$this->_rptRatingReport->AddMessageVariables(MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);
					
					$intFailed++;
					continue;
				}
			
				// Calculate Cap Rate
				$fltCharge = $this->_CalculateCap();
				if ($fltCharge === FALSE)
				{
					// Charge calculation failed
					// THIS SHOULD NEVER HAPPEN
	
					$arrCDR['Status'] = CDR_UNABLE_TO_CAP;
					$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
					$updSaveCDR->Execute($arrCDR);
					
					// add to report
					$arrAlises['<Reason>'] = "Unable to cap CDR";
					$this->_rptRatingReport->AddMessageVariables(MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);
					
					$intFailed++;
					continue;
				}
				
				// Calculate Prorate
				$fltCharge = $this->_CalculateProrate();
				if ($fltCharge === FALSE)
				{
					// Charge calculation failed
					// THIS SHOULD NEVER HAPPEN
	
					$arrCDR['Status'] = CDR_UNABLE_TO_PRORATE;
					$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
					$updSaveCDR->Execute($arrCDR);
					
					// add to report
					$arrAlises['<Reason>'] = "ProRating failed";
					$this->_rptRatingReport->AddMessageVariables(MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);
					
					$intFailed++;
					continue;
				}
			}
			// Update Service & Account Totals
			$mixResult = $this->_UpdateTotals($arrCDR['Service']);
			if ($mixResult === FALSE)
			{
				Debug($this->_UpdateTotals->Error());
				// problem updating totals
				// set status in database
				$arrCDR['Status'] = CDR_TOTALS_UPDATE_FAILED;
				$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
				$updSaveCDR->Execute($arrCDR);
				
				// add to report
				$arrAlises['<Reason>'] = "Totals updating failed";
				$this->_rptRatingReport->AddMessageVariables(MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);
				
				$intFailed++;
				continue;
			}
			elseif (!$mixResult)
			{
				// add to report
				$arrAlises['<Reason>'] = "Totals didn't change";
				$this->_rptRatingReport->AddMessageVariables(MSG_IGNORE.MSG_FAIL_LINE, $arrAlises, FALSE);
			}
			
			// Check for overlimit accounts
			// TODO!!! - FUTURE
			// Check if an account is over its limit and do something if it is
			// implement this some time in the future
			
			// Report
			$this->_rptRatingReport->AddMessage(MSG_OK, FALSE);
			
			// save CDR back to database
			$arrCDR['Rate'] = $this->_arrCurrentRate['Id'];
			$arrCDR['Charge'] = $this->_arrCurrentCDR['Charge'];
			$arrCDR['Status'] = CDR_RATED;
			$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
			$updSaveCDR->Execute($arrCDR);
			$intPassed++;
		}
		
		// Report footer
		$arrAliases['<Total>']	= $intFailed + $intPassed;
		$arrAliases['<Time>']	= $this->Framework->SplitWatch();
		$arrAliases['<Pass>']	= $intPassed;
		$arrAliases['<Fail>']	= $intFailed;
		$this->_rptRatingReport->AddMessageVariables("\n".MSG_HORIZONTAL_RULE.MSG_REPORT, $arrAliases, FALSE);
		
		// Return TRUE or FALSE
		return $bolReturn;
	 }
	
	//------------------------------------------------------------------------//
	// DeRate
	//------------------------------------------------------------------------//
	/**
	 * DeRate()
	 *
	 * DeRate CDR Records
	 *
	 * DeRate CDR Records
	 * DeRate un-invoiced CDRs
	 *
	 * @param	mixed	$mixWhere	array to be used as a WHERE clause when selecting
	 *								CDRs to derate. Most usefull would be;
	 *								$arrWhere['Account']	Id of account
	 *								$arrWhere['Service']	Id of service
	 *								$arrWhere['File']		Id of file
	 *								can also be a string WHERE clause
	 *
	 * @return	int		number of records derated
	 * @method
	 */
	 function DeRate($mixWhere)
	 {
	 
	 	// TODO !!!! - implament this in the GUI, Bash needs to implement an update similar to the
		// generic data miner
		//
	 	// convert the WHERE clause to a string
		// NOTE !!!! - this will break if $this->_selFindRate is ever not instanciated in the constructor
		$strWhere = $this->_selFindRate->PrepareWhere($mixWhere);
		
		// don't derate invoiced or temp invoiced CDRs
		if ($strWhere)
		{
			$strWhere .= " AND Status <> ".CDR_INVOICED." AND Status <> ".CDR_TEMP_INVOICE." ";
		}
		else
		{
			$strWhere .= " Status <> ".CDR_INVOICED." AND Status <> ".CDR_TEMP_INVOICE." ";
		}
		
		// set up derating database object
		//TODO!!!!
		// $updDeRate = new ....
		
		// run the update query
		//TODO!!!!
		//$updDeRate->Execute($arrData, $arrWhere);
		
		// return the number of affected Rows
		// TODO!!!!
	 }
	
	//------------------------------------------------------------------------//
	// _FindRate
	//------------------------------------------------------------------------//
	/**
	 * _FindRate()
	 *
	 * Find the appropriate rate for the current CDR
	 *
	 * Find the appropriate rate for the current CDR
	 *
	 * @return	mixed	array	rate details
	 * 					bool	FALSE if rate not found
	 * @method
	 */
	 private function _FindRate()
	 {
	 	// is this a fleet service
		$intSourceFleetAccount = $this->_FindFleetAccount($this->_arrCurrentCDR['Service']);
		if ($intSourceFleetAccount)
		{
			// get the destination service Id
			$intDestinationService = $this->_FindServiceByFNN($this->_arrCurrentCDR['Destination'], $this->_arrCurrentCDR['StartDatetime']);
			
			// is the destination a fleet account
			$intDestinationFleetAccount = $this->_FindFleetAccount($intDestinationService);
			if ($intDestinationFleetAccount)
			{
				// are both fleet services on the same account
				if ($intSourceFleetAccount === $intDestinationFleetAccount)
				{
					$bolFleet = TRUE;
				}
			}
		}

	 	// find the appropriate rate
	 	$strAliases['Service']		= $this->_arrCurrentCDR['Service'];
	 	$strAliases['RecordType']	= $this->_arrCurrentCDR['RecordType'];
	 	$strAliases['Destination']	= $this->_arrCurrentCDR['DestinationCode'];
		if (!$this->_arrCurrentCDR['DestinationCode'])
		{
			$strAliases['Destination']	= 0;
		}
	 	$strAliases['DateTime']		= $this->_arrCurrentCDR['StartDatetime'];
	 	$intTime					= strtotime($this->_arrCurrentCDR['StartDatetime']);
	 	$strAliases['Time']			= date("H:i:s", $intTime);
	 	$strDay						= date("l", $intTime);
	 	$strAliases['Monday']		= ($strDay == "Monday") ? TRUE : DONKEY;
	 	$strAliases['Tuesday']		= ($strDay == "Tuesday") ? TRUE : DONKEY;
	 	$strAliases['Wednesday']	= ($strDay == "Wednesday") ? TRUE : DONKEY;
	 	$strAliases['Thursday']		= ($strDay == "Thursday") ? TRUE : DONKEY;
	 	$strAliases['Friday']		= ($strDay == "Friday") ? TRUE : DONKEY;
	 	$strAliases['Saturday']		= ($strDay == "Saturday") ? TRUE : DONKEY;
	 	$strAliases['Sunday']		= ($strDay == "Sunday") ? TRUE : DONKEY;
		if ($bolFleet === TRUE)
		{
			// look for a fleet rate
			$this->_selFindFleetRate->Execute($strAliases);
			if (!($arrRate = $this->_selFindFleetRate->Fetch()))
			{
				// no fleet rate, look for a normal rate
				$this->_selFindRate->Execute($strAliases);
			}
		}
		else
		{
			// look for a normal rate
			$this->_selFindRate->Execute($strAliases);
		}
		
		//FAKE : For testing only
		//$this->_selFindRate->Execute();
		
		// check if we found a rate
		if (!($arrRate = $this->_selFindRate->Fetch()))
		{
			return FALSE;
		}
		
		/* DIRTY HUGE DONKEY QUERY
		 * 
		 * SELECT	Rate.*
		 *
		 * FROM		Rate INNER JOIN RateGroupRate ON Rate.Id = RateGroupRate.Rate,
		 * 			RateGroup INNER JOIN RateGroupRate ON RateGroup.Id = RateGroupRate.RateGroup,
		 * 			ServiceRateGroup INNER JOIN RateGroup ON ServiceRateGroup = RateGroup.Id
		 * 
		 * WHERE	ServiceRateGroup.Service		= <Service>			AND
		 * 			Rate.RecordType					= <RecordType>		AND
		 * 			Rate.Destination				LIKE <Destination>	AND
		 * 			ServiceRateGroup.StartDateTime	<= <DateTime>		AND
		 * 			ServiceRateGroup.EndDateTime	>= <DateTime>		AND
		 * 			Rate.StartTime					<= <Time>			AND
		 * 			Rate.EndTime					>= <Time>			AND
		 * 				( Rate.Monday 				= <Monday>			OR
		 * 				  Rate.Tuesday				= <Tuesday>			OR
		 * 				  Rate.Wednesday			= <Wednesday>		OR
		 * 				  Rate.Thursday				= <Thursday>		OR
		 * 				  Rate.Friday				= <Friday>			OR
		 * 				  Rate.Saturday				= <Saturday>		OR
		 * 				  Rate.Sunday				= <Sunday> )
		 * 
		 * ORDER BY ServiceRateGroup.CreatedOn DESC
		 * 
		 * LIMIT 1
		 */
		 	
		// set the current rate
		$this->_arrCurrentRate = $arrRate;
		
		// return something
		return $arrRate;
	 }
	 
	//------------------------------------------------------------------------//
	// _FindFleetAccount
	//------------------------------------------------------------------------//
	/**
	 * _FindFleetAccount()
	 *
	 * Find the Fleet Account for a Service
	 *
	 * Find the Fleet Account for a Service
	 * Checks if there is a fleet rate plan attached to this service and returns
	 * the Account Id for the service if a fleet rate is found.
	 *
	 * @param	int		$intService		Id of the service
	 *
	 * @return	mixed	int				Account Id
	 * 					bool			FALSE if Account not found
	 * @method
	 */
	 private function _FindFleetAccount($intService)
	 {
	 	// return FALSE if invalid Service
		if ((int)$intService == 0)
		{
			return FALSE; 
		}
		
		// find fleet RateGroup attached to this service for this record type & service type
		$arrWhere = Array();
		$arrWhere['Service']		= $intService;
		$arrWhere['RecordType']		= $this->_arrCurrentCDR['RecordType'];
		$arrWhere['ServiceType']	= $this->_arrCurrentCDR['ServiceType'];
		$this->_selFleetAccount->Execute($arrWhere);
		if($arrAccount = $this->_selFleetAccount->Fetch())
		{
			return $arrAccount['Account'];
		}
		// return FALSE if fleet rate not found
		return FALSE;
			
	 }
	 
	//------------------------------------------------------------------------//
	// _FindServiceByFNN
	//------------------------------------------------------------------------//
	/**
	 * _FindServiceByFNN()
	 *
	 * Find the Id for a Service (by FNN)
	 *
	 * Find the Id for a Service (by FNN).  Returns the most recently created service as of $Date
	 * with the specified FNN.
	 *
	 * @param	str		$strFNN		Service FNN
	 * @param	str		$strDate	optional Date to check on
	 *	 
	 * @return	mixed	int			Service Id
	 * 					bool		FALSE if Service not found
	 * @method
	 */
	 private function _FindServiceByFNN($strFNN, $strDate)
	 {
	 	// return FALSE if invalid FNN
		if ((int)$strFNN == 0)
		{
			return FALSE; 
		}
		
		// correct missing date
		if ((int)$strDate == 0)
		{
			$strDate = "0000-00-00 00:00:00"; 
		}
		
		// set prefix
		$strPrefix = substr($strFNN, 0, -2).'__';
		
	 	// find Service (ignores achived services, accounts for Indial 100s)
	 	$this->_selServiceByFNN->Execute(Array('FNN' => $strFNN, 'Date' => $strDate, 'Prefix' => $strPrefix));
		if ($arrService = $this->_selServiceByFNN->Fetch())
		{
			return $arrService['Id'];
		}
		
		// return FALSE if Account not found
		return FALSE;
	 }
	 
	 
	 
	//------------------------------------------------------------------------//
	// _CalculateCharge
	//------------------------------------------------------------------------//
	/**
	 * _CalculateCharge()
	 *
	 * Calculate the base charge for the current CDR Record
	 *
	 * Calculate the base charge for the current CDR Record
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 * @method
	 */
	 private function _CalculateCharge()
	 {
	 	// call Zeemus magic rating formula
		$fltCharge = $this->_ZeemusMagicRatingFormula();
		
		// apply minimum charge
		$fltCharge = Max($fltCharge, $this->_arrCurrentRate['StdMinCharge']);
		
		// set the current charge
		$this->_arrCurrentCDR['Charge'] = $fltCharge;
		
		// return the charge amount
		return $fltCharge;
	 }
	 
	//------------------------------------------------------------------------//
	// _CalculatePassThrough
	//------------------------------------------------------------------------//
	/**
	 * _CalculatePassThrough()
	 *
	 * Calculate the Pass Through charge for the current CDR Record
	 *
	 * Calculate the Pass Through charge for the current CDR Record
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 * @method
	 */
	 private function _CalculatePassThrough()
	 {
	 	// get the current charge
		$fltCharge		= $this->_arrCurrentCDR['Charge'];
		
	 	if ($this->_arrCurrentRate['PassThrough'])
		{
			// add the charge
			$fltCharge = $this->_arrCurrentCDR['Charge'];
			
			// add the flagfall
			$fltCharge += $this->_arrCurrentRate['StdFlagfall'];
			
			// apply minimum charge
			$fltCharge = Max($fltCharge, $this->_arrCurrentRate['StdMinCharge']);
			
			// set the current charge
			$this->_arrCurrentCDR['Charge'] = $fltCharge;
		}
		
		// return the charge amount
		return $fltCharge;
	 }
	 
	//------------------------------------------------------------------------//
	// _CalculateCap
	//------------------------------------------------------------------------//
	/**
	 * _CalculateCap()
	 *
	 * Calculate the cap charge for the current CDR Record
	 *
	 * Calculate the cap charge for the current CDR Record
	 *
	 * Cap can be set in $ (CapCost) or units (CapUnits) but not both*.
	 * Cap Limit can be set in $ (CapLimit) or units (CapUsage) but not both*.
	 * *If both are specified, units will be used.
	 * The type of Cap & Cap Limit can be mixed, eg. Cap in $, Cap Limit in Units.
	 *
	 * if CapLimit is set ($ limit to capping) then only the standard rate will
	 * be used to calculate the charge. If CapUsage is set then the excess rate
	 * will be used to calculate the over cap charge.
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 *function 
	 * @method
	 */
	 private function _CalculateCap()
	 {
	 	// get cap details
	 	$intCapUnits	= $this->_arrCurrentRate['CapUnits'];
		$fltCapCost		= $this->_arrCurrentRate['CapCost'];
		$intCapUsage	= $this->_arrCurrentRate['CapUsage'];
		$fltCapLimit	= $this->_arrCurrentRate['CapLimit'];
		
		// get CDR details
		$fltCharge		= $this->_arrCurrentCDR['Charge'];
		$fltFullCharge	= $fltCharge;
		$intUnits		= $this->_arrCurrentCDR['Units'];
		
	 	// is this a capped charge
		if (!$intCapUnits && !$fltCapCost && !$intCapUsage && !$fltCapLimit)
		{
			// not a capped rate, don't do anything
		}
		elseif ($fltCharge <= $fltCapCost || $intUnits <= $intCapUnits)
		{
			// under the cap, don't do anything
		}
		else
		{
			// calculate cap charge
			if ($intUnits > $intCapUnits)
			{
				// over cap units, cap at cap units
				//resend to Zeemus magic rating formular with less units
				$fltCharge = $this->_ZeemusMagicRatingFormula('Std', $intCapUnits);
			}
			elseif ($fltCharge > $fltCapCost)
			{
				// over cap cost, cap at cap cost
				$fltCharge = $fltCapCost;
			}
		
			// calculate over cap limit charges
			if ($intCapUsage && $intUnits > $intCapUsage)
			{
				// calculate excess units
				$intExsUnits = $intUnits - $intCapUsage;
				
				// resend to Zeemus magic rating formular with excess units
				$fltCharge += $this->_ZeemusMagicRatingFormula('Exs', $intExsUnits);
			}
			elseif ($fltCapLimit && $fltFullCharge > $fltCapLimit)
			{
				// calculate excess charge
				$fltCharge += ($fltFullCharge - $fltCapLimit) + $this->_arrCurrentRate['ExsFlagfall'];
			}
		}
		
		// set the current charge
		$this->_arrCurrentCDR['Charge'] = $fltCharge;
			
		// return the charge amount
		return $fltCharge;
	 }
	 
	//------------------------------------------------------------------------//
	// _CalculateProrate
	//------------------------------------------------------------------------//
	/**
	 * _CalculateProrate()
	 *
	 * Calculate the prorate charge for the current CDR Record
	 *
	 * Calculate the prorate charge for the current CDR Record
	 *
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _CalculateProrate()
	 {
	 	// get current charge
		$fltCharge = $this->_arrCurrentCDR['Charge'];
		
	 	// is this a prorate charge
		if ($this->_arrCurrentRate['Prorate'])
		{
			// Yes it is...
			$intEndDay		= floor(strtotime($this->_arrCurrentCDR['EndDateTime'])/86400);
			$intStartDay	= floor(strtotime($this->_arrCurrentCDR['StartDateTime'])/86400);
			$intEndMonth	= floor((strtotime("+ 1 month", $intStartDay)/86400) - 1);
			$intDaysInMonth = $intEndMonth - $intStartDay;
			
			// is StartDate -> EndDate a whole month
			if ($intEndDay < $intEndMonth)
			{
				// calculate prorate
				$intDaysInCharge = $intEndDay - $intStartDay;
				try
				{
					$fltCharge = ($fltCharge / $intDaysInMonth ) * $intDaysInCharge;
				}
				catch (Exception $excException)
				{
					// Divide by zero
					return FALSE;
				}	
			}
		}
		
		// return the charge amount
		return $fltCharge;
	 }
	 
	//------------------------------------------------------------------------//
	// _UpdateTotals
	//------------------------------------------------------------------------//
	/**
	 * _UpdateTotals()
	 *
	 * Update the Service & Account totals
	 *
	 * Update the Service & Account totals
	 *
	 * @return	bool	you guessed it, TRUE is good / FALSE is bad / DONKEYs are some place in the middle
	 *
	 * @method
	 */
	 private function _UpdateTotals($arrService)
	 {
	 	// update service totals
		$fltCharge = $this->_arrCurrentCDR['Charge'];

		// don't update totals (or fail) if charge is zero
		if ($fltCharge == 0)
		{
			return 1;
		}
		
		// set service Id
		$arrData['Id'] = $this->_arrCurrentCDR['Service'];
		
		if ($this->_arrCurrentRate['Uncapped'])
		{
			$arrData['UncappedCharge'] = (float)$arrService['UncappedCharge'] + $fltCharge;
			return $this->_ubiServiceTotalsUncapped->Execute($arrData);
		}
		else
		{
			$arrData['CappedCharge'] = (float)$arrService['CappedCharge'] + $fltCharge;
			return $this->_ubiServiceTotalsCapped->Execute($arrData);
		}
	 }
	 
	//------------------------------------------------------------------------//
	// _ZeemusMagicRatingFormula
	//------------------------------------------------------------------------//
	/**
	 * _ZeemusMagicRatingFormula()
	 *
	 * Calculate the charge for the current CDR Record
	 *
	 * Calculate the charge for the current CDR Record
	 * This is where the actual work of applying the magic Zeemu rating formula
	 * is done.
	 *
	 * @param	string	$strType	optional Rate type to use, 'Std' or 'Exs'
	 * @param	int		$intUnits	optional units to use when calculating ($q)
	 *	 
	 * @return	mixed	float : charge amount
	 * 					FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _ZeemusMagicRatingFormula($strType = 'Std', $intUnits = 0)
	 {
	 	// select rate type to use (Std or Exs
		if ($strType != 'Std' && $strType != 'Exs')
		{
			return FALSE;
		}
		
		// ------------------------------------------------ //
		// rate details
		// ------------------------------------------------ //
		// a rate should never have a per unit rate & a markup
		// as it would be redundant (the rate should be set as a
		// $ markup rather then a rate)
		$r	= $this->_arrCurrentRate[$strType.'RatePerUnit'];	// rate per unit
		$f	= $this->_arrCurrentRate[$strType.'Flagfall'];		// flagfall
		$p	= $this->_arrCurrentRate[$strType.'Percentage'];	// percentage markup
		$d	= $this->_arrCurrentRate[$strType.'Markup'];		// dollar markup per unit
		$u	= $this->_arrCurrentRate[$strType.'Units'];			// units to charge in
		
		// ------------------------------------------------ //
		
		// ------------------------------------------------ //
		// CDR details
		// ------------------------------------------------ //
		$c	= $this->_arrCurrentCDR['Cost'];		// our cost (total)
		$q	= $this->_arrCurrentCDR['Units'];		// number of units (total)
		
		// ------------------------------------------------ //
		
		// ------------------------------------------------ //
		// Units Passed to Method
		// ------------------------------------------------ //
		if ((int)$intUnits > 0)
		{
			$q = (int)$intUnits;
		}
		
		// ------------------------------------------------ //
		
		// calculate number of units to charge
		$n = ceil($q / $u);
		
		// ------------------------------------------------ //
		// apply the rate
		// ------------------------------------------------ //
		
		$fltCharge = 0;
		
		// apply per unit rate & flagfall
		// always applied, will equate to zero if there is
		// no per unit rate and no flagfall
		$fltCharge = ($n * $r + $f);
		
		// apply % and $ markup
		// only applied if the rate has a markup on cost
		// this will add our cost + markup to the charge
		// if there is no cost and no $ markup this will
		// equate to zero
		if ($p || $d)
		{
			$fltCharge += ($c + $p * $c / 100 + $n * $d);
		}
		
		// ------------------------------------------------ //
		
		// return the charge
		return $fltCharge;
	 }
	 
	 
	//------------------------------------------------------------------------//
	// ReRate()
	//------------------------------------------------------------------------//
	/**
	 * ReRate()
	 *
	 * Changes CDR Status from specified value to CDR_RERATE
	 *
	 * Changes CDR Status from specified value to CDR_RERATE
	 *
	 * @param	integer		$intStatus			Status to look for
	 *	 
	 * @return	integer							Number of CDRs affected
	 *
	 * @method
	 */
	 function ReRate($intStatus)
	 {
	 	$arrColumns['Status']	= CDR_RERATE;
	 	$updReRate = new StatementUpdate("CDR", "Status = $intStatus", $arrColumns);
	 	$mixReturn = $updReRate->Execute($arrColumns, NULL);
	 	return (int)$mixReturn;
	 }
 }


?>
