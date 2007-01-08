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
		
		// Get Charges Select Statement
		$arrWhere				= "StartedOn <= NOW() " .
								  "AND Archived = 0 " .
								  "AND " .
								  "(" .
								  "		Continuable = 1 " .
								  "		OR " .
								  "		(Continuable = 0 AND MinCharge > TotalCharged)" .
								  ") " .
								  "AND " .
								  "(" .
								  "		(" .
								  "			RecurringFreqType = ".BILLING_FREQ_DAY." " .
								  "			AND " .
								  "			NOW() >= ADDDATE(LastChargedOn, INTERVAL RecurringFreq DAY)" .
								  "		)" .
								  "		OR" .
								  "		(" .
								  "			RecurringFreqType = ".BILLING_FREQ_MONTH." " .
								  "			AND " .
								  "			NOW() >= ADDDATE(LastChargedOn, INTERVAL RecurringFreq MONTH)" .
								  "		)" .
								  "		OR" .
								  "		(" .
								  "			RecurringFreqType = ".BILLING_FREQ_HALF_MONTH." " .
								  "			AND " .
								  "			(" .
								  "				(" .
								  "					DATE_FORMAT(LastChargedOn, %e) < 15 " .
								  "					AND " .
								  "					NOW() >= ADDDATE(LastChargedOn, INTERVAL 14 DAY)" .
								  "				) " .
								  "				OR " .
								  "				(" .
								  "					DATE_FORMAT(LastChargedOn, %e) > 14" .
								  "					AND " .
								  "					NOW() >= ADDDATE(SUBDATE(LastChargedOn, INTERVAL 14 DAY), INTERVAL 1 MONTH)" .
								  "				)" .
								  "			)" .
								  "		)" .
								  ")";
		$this->_selGetCharges	= new StatementSelect("RecurringCharge", "*", $arrWhere, NULL, "1000");
		
		$arrColumns['TotalRecursions']				= new MySQLFunction("TotalRecursions + 1");
		$arrColumns['TotalCharged']					= new MySQLFunction("TotalCharged + <Charge>");
		$arrColumns['LastChargedOn']				= new MySQLFunction("ADDDATE(LastChargedOn, INTERVAL RecurringFreq DAY)");
		$this->_ubiRecurringChargeDay				= new StatementUpdateById("RecurringCharge", $arrColumns);
		$arrColumns['LastChargedOn']				= new MySQLFunction("ADDDATE(LastChargedOn, INTERVAL RecurringFreq MONTH)");
		$this->_ubiRecurringChargeMonth				= new StatementUpdateById("RecurringCharge", $arrColumns);
		$arrColumns['LastChargedOn']				= new MySQLFunction("ADDDATE(LastChargedOn, INTERVAL 14 DAY)");
		$this->_ubiRecurringChargeFirstHalfMonth	= new StatementUpdateById("RecurringCharge", $arrColumns);
		$arrColumns['LastChargedOn']				= new MySQLFunction("ADDDATE(SUBDATE(LastChargedOn, INTERVAL 14 DAY), INTERVAL 1 MONTH)");
		$this->_ubiRecurringChargeSecondHalfMonth	= new StatementUpdateById("RecurringCharge", $arrColumns);
		
		// Init Report
		$this->_rptRecurringChargesReport	= new Report("Recurring Charges Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au");
		$this->_rptRecurringChargesReport->AddMessage(MSG_HORIZONTAL_RULE);
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
		$this->_rptRecurringChargesReport->AddMessage(MSG_GENERATE_CHARGES);
		$intPassed = 0;
		$intTotal = 0;
		$this->StartWatch();
		
		// Get list of charges that need to be generated (1000 at a time)
		while($arrCharges = $this->_GetCharges())
		{
			// for each charge
			foreach ($arrCharges as $arrCharge)
			{
				$intTotal++;
				$this->_rptRecurringChargesReport->AddMessageVariables(MSG_LINE, Array('<Id>' => $arrCharge['Id']));
				
				// Calculate partial charge if needed
				if (!$arrCharge['Continuable'] && ($arrCharge['TotalCharged'] + $arrCharge['RecursionCharge']) > $arrCharge['MinCharge'])
				{
					// final (partial) charge for a non-continuable charge
					$arrCharge['RecursionCharge'] = $arrCharge['MinCharge'] - $arrCharge['TotalCharged'];
				}
				
				// Add Charge details to Charges Table
				$arrData['AccountGroup']	= $arrCharge['AccountGroup'];
				$arrData['Account']			= $arrCharge['Account'];
				$arrData['Service']			= $arrCharge['Service'];
				$arrData['CreatedBy']		= $arrCharge['CreatedBy'];
				$arrData['CreatedOn']		= $arrCharge['CreatedOn'];
				$arrData['ApprovedBy']		= $arrCharge['ApprovedBy'];
				$arrData['ChargeType']		= $arrCharge['ChargeType'];
				$arrData['Description']		= $arrCharge['Description'];
				$arrData['ChargedOn']		= new MySQLFunction("NOW()"); // FIXME
				$arrData['Nature']			= $arrCharge['Nature'];
				$arrData['Amount']			= $arrCharge['RecursionCharge'];
				if ($arrData['ApprovedBy'])
				{
					$arrData['Status']			= CHARGE_APPROVED;
				}
				else
				{
					$arrData['Status']			= CHARGE_WAITING;
				}
				$this->_insAddToChargesTable->Execute($arrData);
				
				// update RecuringCharge Table
				$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
				$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>", Array('Charge' => $arrCharge['RecursionCharge']));
				switch ($arrCharge['RecurringFreqType'])
				{
					case BILLING_FREQ_DAY:
						$arrColumns['LastChargedOn']		= new MySQLFunction("ADDDATE(LastChargedOn, INTERVAL RecurringFreq DAY)");
						$this->_ubiRecurringChargeDay->Execute($arrCharge);
						break;
					case BILLING_FREQ_MONTH:
						$arrColumns['LastChargedOn']		= new MySQLFunction("ADDDATE(LastChargedOn, INTERVAL RecurringFreq MONTH)");
						$this->_ubiRecurringChargeMonth->Execute($arrCharge);
						break;
					case BILLING_FREQ_HALF_MONTH:
						if ((int)date("d", strtotime($arrCharge['LastChargedOn'])) > 14)
						{
							$arrColumns['LastChargedOn']	= new MySQLFunction("ADDDATE(LastChargedOn, INTERVAL 14 DAY)");
							$this->_ubiRecurringChargeFirstHalfMonth->Execute($arrCharge);
						}
						else
						{
							$arrColumns['LastChargedOn']	= new MySQLFunction("ADDDATE(SUBDATE(LastChargedOn, INTERVAL 14 DAY), INTERVAL 1 MONTH)");
							$this->_ubiRecurringChargeSecondHalfMonth->Execute($arrCharge);
						}
						break;
					default:
						$this->_rptRecurringChargesReport->AddMessage(MSG_FAIL.MSG_REASON."Invalid RecurringFreqType ".$arrCharge['RecurringFreqType']);
						continue;
				}
				// add to report
				$this->_rptRecurringChargesReport->AddMessage(MSG_OK);
				
				$intPassed++;
			}
		}
		
		// TODO: Report footer
		$arrData['<Total>']		= $intTotal;
		$arrData['<Time>']		= $this->SplitWatch();
		$arrData['<Passed>']	= $intPassed;
		$arrData['<Failed>']	= $intTotal - $intPassed;
		$this->_rptRecurringChargesReport->AddMessageVariables(MSG_FOOTER, $arrData);
		
		// Send Report
		$this->_rptRecurringChargesReport->Finish();
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
		$this->_selGetCharges->Execute();
		return $this->_selGetCharges->FetchAll();
	}
 }


?>
