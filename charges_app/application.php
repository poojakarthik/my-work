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
 * @package		charge_application
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
echo "<pre>";

// Application entry point - create an instance of the application object
$appCharge = new ApplicationCharge($arrConfig);

// Execute the application
$appCharge->Execute();

// finished
echo("\n-- End of Charges --\n");
echo "</pre>";
die();



//----------------------------------------------------------------------------//
// ApplicationCharge
//----------------------------------------------------------------------------//
/**
 * ApplicationCharge
 *
 * Charge Module
 *
 * Charge Module
 *
 *
 * @prefix		app
 *
 * @package		charge_application
 * @class		ApplicationSkel
 */
 class ApplicationCharge extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
	}
	
	//------------------------------------------------------------------------//
	// Execute
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Execute the application
	 *
	 * Execute the application
	 *
	 * @return			VOID
	 *
	 * @method
	 */
 	function Execute()
 	{
		// Get list of charges that need to be generated (1000 at a time)
		While($arrCharges = $this->_GetCharges())
		{
			// for each charge
			foreach ($arrCharges as $arrCharge)
			{
				// Calculate partial charge if needed
				if (!$arrCharge['Continuable'] && ($arrCharge['TotalCharged'] + $arrCharge['RecursionCharge']) > $arrCharge['MinCharge'])
				{
					// final (partial) charge for a non-continuable charge
					$arrCharge['RecursionCharge'] = $arrCharge['MinCharge'] - $arrCharge['TotalCharged'];
				}
				
				// Add Charge details to Charges Table
				//TODO!!!!
				
				// update RecuringCharge Table
				//TODO!!!!
					// TotalCharged
					// LastChargedOn * date charge is for, not todays date !!!!
					// TotalRecursions
					
				// add to report
				//TODO!!!!
			}
		}
		
		// Send Report
		//TODO!!!!
	}
	
	//------------------------------------------------------------------------//
	// _GetCharges
	//------------------------------------------------------------------------//
	/**
	 * _GetCharges()
	 *
	 * Get a list of recurring charges that need to be generated
	 *
	 * Get a list of recurring charges that need to be generated
	 * List is limited to 1000
	 *
	 * @return		Mixed	Array	Charges that need to be generated
	 *						bol		FALSE if there are no charges to be generated		 
	 *
	 * @method
	 */
	function _GetCharges()
	{
		// get the next 1000 charges that need to be added
		//TODO!!!!
		// Select * FROM RecurringCharge WHERE
		/*
		Archived = 0
		AND
		StartedOn <= date_today
		AND
		(
			Continuable = 1
			OR
			(Continuable = 0 AND MinCharge > TotalCharged)
		)
		AND
		(
			(
				RecurringFreqType = BILLING_FREQ_DAY
				AND
				date_today >= LastChargedOn + RecurringFreq days
			)
			OR
			(
				RecurringFreqType = BILLING_FREQ_MONTH
				AND
				date_today >= LastChargedOn + RecurringFreq months
			)
			OR
			(
				RecurringFreqType = BILLING_FREQ_HALF_MONTH
				AND
				(
					(
						DATE_FORMAT(LastChargedOn, %e) < 15
						AND
						date_today >= LastChargedOn + 14 days
					)
					OR
					(
						DATE_FORMAT(LastChargedOn, %e) > 14
						AND
						date_today >= LastChargedOn - 14 days + 1 month
					)
				)
			)		
		)
		*/
	}
 }


?>
