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
 * @version		6.10
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
		
		// Loop through each CDR
		foreach($arrCDRList as $arrCDR)
		{
			// set current CDR
			$this->_arrCurrentCDR = $arrCDR;
			
			// Find Rate for this CDR
			if (!$arrRate = $this->_FindRate())
			{
				// rate not found
				// set status in database
				//TODO!!!!
				
				// add to report
				//TODO!!!!
			}
			
			// Calculate Charge
			$fltCharge = $this->_CalculateCharge();
			if ($fltCharge === FALSE)
			{
				// Charge calculation failed
				// THIS SHOULD NEVER HAPPEN
				// report the error
				//TODO!!!!
			}
			
			// Calculate Cap Rate
			$fltCharge = $this->_CalculateCap();
			if ($fltCharge === FALSE)
			{
				// Charge calculation failed
				// THIS SHOULD NEVER HAPPEN
				// report the error
				//TODO!!!!
			}
			
			// Calculate Prorate
			$fltCharge = $this->_CalculateProrate();
			if ($fltCharge === FALSE)
			{
				// Charge calculation failed
				// THIS SHOULD NEVER HAPPEN
				// report the error
				//TODO!!!!
			}
			
			// Update Service & Account Totals
			if (!$this->_UpdateTotals())
			{
				// problem updating totals
				// set status in database
				//TODO!!!!
				
				// add to report
				//TODO!!!!
			}
			
			// Check for overlimit accounts
			// TODO!!! - FUTURE
			// Check if an account is over its limit and do something if it is
			// implement this some time in the future
			
			// save CDR back to database
			//TODO!!!!
		
		}
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
		//TODO!!!!
		
		// return something
		//TODO!!!!
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
		
		// set the current charge
		// $this->_arrCurrentCDR['Charge'] =
		//TODO!!!!
		
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
	 * @return	mixed	float	charge amount
	 * 					bool	FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _CalculateCap()
	 {
	 	// is this a capped charge
		if () //TODO!!!!
		{
			// call Zeemus magic rating formular
			$fltCharge = $this->_ZeemusMagicRatingFormula();
			
			// set the current charge
			// $this->_arrCurrentCDR['Charge'] =
			//TODO!!!!
		}
		else
		{
			// not a capped charge, we will return the existing charge amount
			$fltCharge = $this->_arrCurrentCDR['Charge'];
		}
		
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
		if () //TODO!!!!
		{
			// Yes it is, NFI what to do now
			//TODO!!!!
			
			// set the current charge
			//TODO!!!!
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
	 * @return	bool	you guesed it, TRUE is good / FALSE is bad / DONKEYS are some place in the middle
	 *
	 * @method
	 */
	 private function _UpdateTotals()
	 {
	 	// update service totals
		//TODO!!!!
		
		// update account totals
		//TODO!!!!
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
	 * @param	string	$strType	Rate type to use, 'Std' or 'Ext'
	 *	 
	 * @return	mixed	float : charge amount
	 * 					FALSE if charge could not be calculated
	 *
	 * @method
	 */
	 private function _ZeemusMagicRatingFormula($strType = 'Std')
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
