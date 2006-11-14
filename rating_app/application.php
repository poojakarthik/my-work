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
 * @package		Skeleton_application
 * @author		Jared 'flame' Herbohn
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Application entry point - create an instance of the application object
$appRating = new ApplicationRating();

// run the thing
$appRating->Rate();

// finished
echo("\n-- End of Rating --\n");
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
	 	// Initialise framework components
		$this->_rptRatingReport = new Report("Rating Report for " . date("Y-m-d H:i:s"), "flame@telcoblue.com.au");
		
		$this->_rptRatingReport->AddMessage("\n".MSG_HORIZONTAL_RULE.MSG_RATING_TITLE, FALSE);
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
	 *
	 * @method
	 */
	 function Rate()
	 {
	 	// get list of CDRs to rate (limit results to 1000)
	 	$selGetCDRs = new StatementSelect("CDR", "*", "Status = ".CDR_NORMALISED, null, "1000");
	 	$selGetCDRs->Execute();
		$arrCDRList = $selGetCDRs->FetchAll();
		
		$updSaveCDR = new StatementUpdateById("CDR");
		
		$this->Framework->StartWatch();
		
		$intPassed = 0;
		$intFailed = 0;
		
		// Loop through each CDR
		foreach($arrCDRList as $arrCDR)
		{
			// Report
			$arrAlises['<SeqNo>'] = str_pad($arrCDR['SequenceNo'], 60, " ");
			$this->_rptRatingReport->AddMessageVariables(MSG_LINE, $arrAlises, FALSE);
			
			// set current CDR
			$this->_arrCurrentCDR = $arrCDR;
			
			// Find Rate for this CDR
			if (!$this->_arrCurrentRate = $this->_FindRate())
			{
				// rate not found
				// set status in database
				$arrCDR['Status']	= CDR_RATING_NOT_FOUND;
				$arrCDR['RatedOn']	= new MySQLFunction('NOW()');
				$updSaveCDR->Execute($arrCDR);
				
				// add to report
				$arrAlises['<Reason>'] = "Rate not found";
				$this->_rptRatingReport->AddMessageVariables(MSG_FAILED.MSG_FAIL_LINE, $arrAlises, FALSE);
				
				$intFailed++;
				continue;
			}
			
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
			
			// Update Service & Account Totals
			if (!$this->_UpdateTotals())
			{
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
			
			// Check for overlimit accounts
			// TODO!!! - FUTURE
			// Check if an account is over its limit and do something if it is
			// implement this some time in the future
			
			// Report
			$this->_rptRatingReport->AddMessage(MSG_OK, FALSE);
			
			// save CDR back to database
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
		$this->_rptRatingReport->AddMessageVariables("\n".MSG_HORIZONTAL_RULE.MSG_REPORT, $arrAlises, FALSE);
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
	 	// find the appropriate rate
		//TODO!!!!
		
		// set the current rate
		$this->_arrCurrentRate = $arrRate;
		
		// return something
		return $arrRate;
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
		//TODO!!!!
		$fltCharge = $this->_ZeemusMagicRatingFormula();
		
		// set the current charge
		$this->_arrCurrentCDR['Charge'] = $fltCharge;
		
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
			if ($intUnits > $intCapUsage)
			{
				// calculate excess units
				$intExsUnits = $intUnits - $intCapUsage;
				
				// resend to Zeemus magic rating formular with excess units
				$fltCharge += $this->_ZeemusMagicRatingFormula('Ext', $intExsUnits);
			}
			elseif ($fltFullCharge > $fltCapLimit)
			{
				// calculate excess charge
				$fltCharge += ($fltFullCharge - $fltCapLimit)
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
	 	// is this a prorate charge
		if (false) //TODO!!!!
		{
			// Yes it is, NFI what to do now
			//TODO!!!!
			
			// set the current charge
			$this->_arrCurrentCDR['Charge'] = $fltCharge;
		}
		else
		{
			// not a prorate charge, we will return the existing charge amount
			$fltCharge = $this->_arrCurrentCDR['Charge'];
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
	 private function _UpdateTotals()
	 {
	 	// update service totals
		$inRate = $this->_arrCurrentCDR['Rate'];
		// = $this->_arrCurrentCDR['Charge'];
		// $this->_arrCurrentRate['Uncapped']

		if ($this->_arrCurrentRate['Uncapped'])
		{
			$arrService['UncappedCharge'] = DONKEY;
		}
		
		return DONKEY;		// ;)
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
	 * @param	string	$strType	optional Rate type to use, 'Std' or 'Ext'
	 * @param	int		$intUnits	optional units to use when calculating ($q)
	 *	 
	 * @return	mixed	float : charge amount
	 * 					FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _ZeemusMagicRatingFormula($strType = 'Std', $intUnits = 0)
	 {
	 	// select rate type to use (Std or Ext
		if ($strType != 'Std' && $strType != 'Ext')
		{
			return FALSE;
		}
		
		// ------------------------------------------------ //
		// rate details
		// ------------------------------------------------ //
		// a rate should never have a per unit rate & a markup
		// as it would be redundant (the rate should be set as a
		// $ markup rather then a rate)
		$r	= $this->_CurrentRate[$strType.'RatePerUnit'];	// rate per unit
		$f	= $this->_CurrentRate[$strType.'Flagfall'];		// flagfall
		$p	= $this->_CurrentRate[$strType.'Percentage'];	// percentage markup
		$d	= $this->_CurrentRate[$strType.'Markup'];		// dollar markup per unit
		$u	= $this->_CurrentRate[$strType.'Units'];		// units to charge in
		
		// ------------------------------------------------ //
		
		// ------------------------------------------------ //
		// CDR details
		// ------------------------------------------------ //
		$c	= $this->_CurrentCDR['Cost'];		// our cost (total)
		$q	= $this->_CurrentCDR['Units'];		// number of units (total)
		
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
		$n = $q / $u;
		
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
	 
 }


?>
