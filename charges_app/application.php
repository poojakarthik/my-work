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
		
		$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
		$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>");
		$arrColumns['LastChargedOn']	= NULL;
		$this->_ubiRecurringCharge		= new StatementUpdateById("RecurringCharge", $arrColumns);
		
		$this->_insAddToChargesTable	= new StatementInsert("Charge");

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
		$intNonUnique = 0;
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
				
				// Calculate the charge date
				$strDate = $arrCharge['LastChargedOn'];
				switch ($arrCharge['RecurringFreqType'])
				{
					case BILLING_FREQ_DAY:
						$strDate	= date("Y-m-d", strtotime("+".$arrCharge['RecurringFreq']." days", strtotime($arrCharge['LastChargedOn'])));
						break;
					case BILLING_FREQ_MONTH:
						$strDate	= date("Y-m-d", strtotime("+".$arrCharge['RecurringFreq']." months", strtotime($arrCharge['LastChargedOn'])));
						break;
					case BILLING_FREQ_HALF_MONTH:
						if ((int)date("d", strtotime($arrCharge['LastChargedOn'])) > 14)
						{
							$strDate	= date("Y-m-d", strtotime("+14 days", strtotime($arrCharge['LastChargedOn'])));
						}
						else
						{
							$strDate	= date("Y-m-d", strtotime("-14 days", strtotime($arrCharge['LastChargedOn'])));
							$strDate	= date("Y-m-d", strtotime("+1 month", strtotime($strDate)));
						}
						break;
					default:
						$this->_rptRecurringChargesReport->AddMessage(MSG_FAIL.MSG_REASON."Invalid RecurringFreqType ".$arrCharge['RecurringFreqType']);
						continue;
				}
				$arrCharge['LastChargedOn'] = $strDate;
				
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
				
				// is this a unique charge
				if ($arrCharge['Unique'])
				{
					// check if a charge of this type already exists for this billing period
					//TODO!!!! -- check in the db
					/*
					SELECT Id FROM charge WHERE
					ChargeType 			= $arrData['ChargeType']
					AND AccountGroup 	= $arrData['AccountGroup']
					AND Account 		= $arrData['Account']
					AND Nature			= $arrData['Nature']
					AND Status			!= CHARGE_DECLINED
					AND Status			!= CHARGE_INVOICED
					LIMIT 1
					*/
					//$intNotUnique =
					if ($intNotUnique)
					{
						// update RecuringCharge Table
						$arrColumns['LastChargedOn']	= $arrCharge['LastChargedOn'];
						$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
						$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>", Array('Charge' => 0));
						if ($this->_ubiRecurringCharge->Execute($arrCharge) === FALSE)
						{

						}
						
						// add to report
						//TODO!!!! - different message (non-unique charge skipped)
						$this->_rptRecurringChargesReport->AddMessage(MSG_OK);
		
						$intNonUnique++;
						
						continue;
					}
				}

				if ($this->_insAddToChargesTable->Execute($arrData) === FALSE)
				{

				}
				
				// update RecuringCharge Table
				$arrColumns['LastChargedOn']	= $arrCharge['LastChargedOn'];
				$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
				$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>", Array('Charge' => $arrCharge['RecursionCharge']));
				if ($this->_ubiRecurringCharge->Execute($arrCharge) === FALSE)
				{

				}
				
				// add to report
				$this->_rptRecurringChargesReport->AddMessage(MSG_OK);

				$intPassed++;
			}
		}
		
		// build any CDR based credits
		
		// change status of CDR Creidts
		$arrColumns = Array();
		$arrColumns['Status']	= CDR_TEMP_CREDIT;
		$updCDRSetStatus = new StatementUpdate("CDR", "Credit = 1 AND Status = ".CDR_INVOICED, $arrColumns);
		if ($updCDRSetStatus->Execute($arrColumns) === FALSE)
		{
			// ERROR

		}

		// Get totals of CDR credits
		$arrColumns = Array();
		$arrColumns['Total']		= "SUM(Charge)";
		$arrColumns['AccountGroup']	= "AccountGroup";
		$arrColumns['Account']		= "Account";
		$arrColumns['Service']		= "Service";
		$arrColumns['Carrier']		= "Carrier";
		$selCDRCreditTotals = new StatementSelect("CDR", $arrColumns, "Credit = 1 AND Status = ".CDR_TEMP_CREDIT, NULL, NULL, "Service");
		if ($selCDRCreditTotals->Execute() === FALSE)
		{
			// ERROR

		}
		$arrCreditTotals = $selCDRCreditTotals->FetchAll();
		
		$arrData['AccountGroup']	= NULL;
		$arrData['Account']			= NULL;
		$arrData['Service']			= NULL;
		$arrData['CreatedBy']		= USER_ID;
		$arrData['CreatedOn']		= new MySQLFunction("NOW()");
		$arrData['ChargeType']		= CHARGE_CODE_CALL_CREDIT;
		$arrData['Description']		= "Call credit from ";
		$arrData['Nature']			= NATURE_CR;
		$arrData['Amount']			= NULL;
		$arrData['Status']			= CHARGE_WAITING;
		$insOneOffCredit = new StatementInsert("Charge");
		foreach($arrCreditTotals as $arrCreditTotal)
		{
			// make a one off credit (not approved)
			$arrData['AccountGroup']	= $arrCreditTotal['AccountGroup'];
			$arrData['Account']			= $arrCreditTotal['Account'];
			$arrData['Service']			= $arrCreditTotal['Service'];
			$arrData['Description']		.= $arrCreditTotal['Carrier'];
			$arrData['Amount']			= $arrCreditTotal['Total'];
			if ($insOneOffCredit->Execute($arrData) === FALSE)
			{
				$insOneOffCredit->Error();
			}
		}

		
		// change status of CDR credits
		$arrColumns['Status']	= CDR_CREDITED;
		$updCDRSetStatus = new StatementUpdate("CDR", "Credit = 1 AND Status = ".CDR_INVOICED, $arrColumns);
		if ($updCDRSetStatus->Execute($arrColumns) === FALSE)
		{
			// ERROR

		}
				
		
		// Report footer
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
		if ($this->_selGetCharges->Execute() === FALSE)
		{

			
			return FALSE;
		}
		return $this->_selGetCharges->FetchAll();
	}
 }


?>
