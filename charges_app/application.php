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
								  "					DATE_FORMAT(LastChargedOn, '%e') < 15 " .
								  "					AND " .
								  "					NOW() >= ADDDATE(LastChargedOn, INTERVAL 14 DAY)" .
								  "				) " .
								  "				OR " .
								  "				(" .
								  "					DATE_FORMAT(LastChargedOn, '%e') > 14" .
								  "					AND " .
								  "					NOW() >= ADDDATE(SUBDATE(LastChargedOn, INTERVAL 14 DAY), INTERVAL 1 MONTH)" .
								  "				)" .
								  "			)" .
								  "		)" .
								  ")";
		$this->_selGetCharges	= new StatementSelect("RecurringCharge", "*", $arrWhere, NULL, "1000");
		
		$arrColumns = Array();
		$arrColumns['Id']				= NULL;
		$arrColumns['LastChargedOn']	= NULL;
		$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
		$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>", Array());
		$this->_ubiRecurringCharge		= new StatementUpdateById("RecurringCharge", $arrColumns);
		
		$this->_insAddToChargesTable	= new StatementInsert("Charge");

		// Init Report
		$this->_rptRecurringChargesReport	= new Report("Charges Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au");
		$this->_rptRecurringChargesReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		return;
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
		$this->Framework->StartWatch();
		$updCDRSetStatus = new StatementUpdate("CDR", "Credit = 1 AND Status = ".CDR_INVOICED, $arrColumns);
		
		// Get list of charges that need to be generated (1000 at a time)
		while($arrCharges = $this->_GetCharges())
		{
			// for each charge
			foreach ($arrCharges as $arrCharge)
			{
				$intTotal++;
				$this->_rptRecurringChargesReport->AddMessageVariables(MSG_LINE, Array('<Id>' => $arrCharge['Id']), FALSE);
				
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
				$arrData['ChargedOn']		= $strDate;
				$arrData['Nature']			= $arrCharge['Nature'];
				$arrData['Amount']			= $arrCharge['RecursionCharge'];
				$arrData['Notes']			= "";
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
						$arrColumns = Array();
						$arrColumns['Id']				= $arrCharge['Id'];
						$arrColumns['LastChargedOn']	= $arrCharge['LastChargedOn'];
						$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
						$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>", Array('Charge' => 0.0));
						if ($this->_ubiRecurringCharge->Execute($arrColumns) === FALSE)
						{
							Debug($this->_ubiRecurringCharge->Error());
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
					Debug($this->_insAddToChargesTable->Error());
				}
				
				// update RecuringCharge Table
				$arrColumns = Array();
				$arrColumns['Id']				= $arrCharge['Id'];
				$arrColumns['LastChargedOn']	= $arrCharge['LastChargedOn'];
				$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions + 1");
				$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged + <Charge>", Array('Charge' => $arrCharge['RecursionCharge']));
				if ($this->_ubiRecurringCharge->Execute($arrColumns) === FALSE)
				{
					Debug($this->_ubiRecurringCharge->Error());
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
		//$updCDRSetStatus = new StatementUpdate("CDR", "Credit = 1 AND Status = ".CDR_INVOICED, $arrColumns);
		if ($updCDRSetStatus->Execute($arrColumns, Array()) === FALSE)
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
		$arrColumns = Array();
		$arrColumns['Status']	= CDR_CREDITED;
		if ($updCDRSetStatus->Execute($arrColumns, Array()) === FALSE)
		{
			// ERROR

		}
				
		
		// Report footer
		$arrData['<Total>']		= $intTotal;
		$arrData['<Time>']		= $this->Framework->SplitWatch();
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
	
	//------------------------------------------------------------------------//
	// AddLatePaymenFees
	//------------------------------------------------------------------------//
	/**
	 * AddLatePaymenFees()
	 *
	 * Add Late Payment Fees
	 *
	 * Add Late Payment Fees
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	 function AddLatePaymentFees($strRef=NULL)
	 {
	 	if (!$strRef)
		{
			$strRef = date('my');
		}
	 	// set up charge
		$arrCharge = Array();
		$arrCharge ['Nature']		= 'DR';
		//$arrCharge ['Notes']		= "Late Payment Fee";
		$arrCharge ['Description']	= "Late Payment Fee";
		$arrCharge ['ChargeType']	= "LP$strRef";
		$arrCharge ['Amount']		= 17.27;
		$arrCharge ['Status']		= CHARGE_APPROVED;
		
	 	// for each account that we are allowed to charge late payment fees
		$intCount = 0;
		$selLPAccounts = new StatementSelect('Account', 'Id, AccountGroup', 'DisableLatePayment != 1 AND Archived != 1');
		$selLPAccounts->Execute();
		echo("Account : Overdue\n");
		while ($arrAccount = $selLPAccounts->Fetch())
		{
			// check for an overdue balance
			if (($fltBalance = $this->Framework->GetOverdueBalance($arrAccount['Id'])) > 10)
			{
				// add to report
				//TODO!rich! replace this echo with report output
				echo("{$arrAccount['Id']} : ".number_format($fltBalance,2)."\n");
				
				// add to the count
				$intCount++;
				
				// charge late payment fee
				$arrCharge['Account'] 		= $arrAccount['Id'];
				$arrCharge['AccountGroup'] 	= $arrAccount['AccountGroup'];
				$this->Framework->AddCharge($arrCharge);
			}
		}
		
		// Change late payment fee settings
		// TODO!flame! make this do something
		
		// return count
		return $intCount;
	 }
	
	//------------------------------------------------------------------------//
	// AddActiveInboundFees
	//------------------------------------------------------------------------//
	/**
	 * AddActiveInboundFees()
	 *
	 * Add Active Inbound Fees
	 *
	 * Add Active Inbound Fees
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	 function AddActiveInboundFees()
	 {
	 	// set up charge
		$arrCharge = Array();
		$arrCharge ['Nature']		= 'DR';
		$arrCharge ['Description']	= "Active Inbound Service Fee";
		$arrCharge ['ChargeType']	= "INB15";
		$arrCharge ['ChargedOn']	= date("Y-m-d");
		$arrCharge ['Amount']		= 15.00;
		$arrCharge ['Status']		= CHARGE_APPROVED;
		
	 	// for each active inbound service
		$intCount = 0;
		$selINB15Services = new StatementSelect('CDR', 
												'Service, Account, AccountGroup, COUNT(Id) AS CDRCount', 
												'Service IS NOT NULL AND Credit = 0 AND Status = '.CDR_RATED.' AND ServiceType = '.SERVICE_TYPE_INBOUND, 
												NULL, 
												NULL, 
												"Service \n HAVING CDRCount > 0");
		$selINB15Services->Execute();
		echo("Service : CDR Count\n\n");
		while ($arrService = $selINB15Services->Fetch())
		{			
			// add to report
			//TODO!rich! replace this echo with report output
			echo("{$arrService['Service']} : {$arrService['CDRCount']}\n");
			
			// add to the count
			$intCount++;
			
			// charge late payment fee
			$arrCharge['Service'] 		= $arrService['Service'];
			$arrCharge['Account'] 		= $arrService['Account'];
			$arrCharge['AccountGroup'] 	= $arrService['AccountGroup'];
			$this->Framework->AddCharge($arrCharge);
		}
		
		// return count
		return $intCount;
	 }
	
	//------------------------------------------------------------------------//
	// AddPinnacleMobileFees
	//------------------------------------------------------------------------//
	/**
	 * AddPinnacleMobileFees()
	 *
	 * Add Pinnacle Mobile Fees
	 *
	 * Add Pinnacle Mobile Fees
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	 function AddPinnacleMobileFees()
	 {
	 	// set up charge
		$arrCharge = Array();
		$arrCharge ['Nature']		= 'DR';
		$arrCharge ['Description']	= "Pinnacle Mobile Service Fee";
		$arrCharge ['ChargeType']	= "PM15";
		$arrCharge ['Amount']		= 15.00;
		$arrCharge ['Status']		= CHARGE_APPROVED;
		
	 	// for each Pinnacle Mobile Service
		$intCount = 0;
		$selPM15Services = new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, RatePlan",
												"Service, Account, AccountGroup",
												"RatePlan.Id = ServiceRatePlan.RatePlan AND " .
												"RatePlan.Name = 'Pinnacle' AND " .
												"RatePlan.ServiceType = ".SERVICE_TYPE_MOBILE." AND " .
												"ServiceRatePlan.Id = (" .
												" SELECT SRP.Id" .
												" FROM ServiceRatePlan SRP" .
												" WHERE SRP.Service = Service.Id" .
												" AND NOW() BETWEEN SRP.StartDatetime AND SRP.EndDatetime" .
												" ORDER BY CreatedOn DESC" .
												" LIMIT 1 )");
		$selPM15Services->Execute();
		echo("Service\n\n");
		while ($arrService = $selPM15Services->Fetch())
		{
			// add to report
			//TODO!rich! replace this echo with report output
			echo("{$arrService['Service']}\n");
			
			// add to the count
			$intCount++;
			
			// charge late payment fee
			$arrCharge['Service'] 		= $arrService['Service'];
			$arrCharge['Account'] 		= $arrService['Account'];
			$arrCharge['AccountGroup'] 	= $arrService['AccountGroup'];
			$this->Framework->AddCharge($arrCharge);
		}
		
		// return count
		return $intCount;
	 }
	
	//------------------------------------------------------------------------//
	// AddLLSAndECredits
	//------------------------------------------------------------------------//
	/**
	 * AddLLSAndECredits()
	 *
	 * Add Landline S&E Credits
	 *
	 * Add Landline S&E Credits
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	 function AddLLSAndECredits()
	 {
	 	// set up charge
		$arrCharge = Array();
		$arrCharge ['Nature']		= 'CR';
		$arrCharge ['ChargeType']	= "SEC";
		$arrCharge ['Status']		= CHARGE_APPROVED;
		
	 	// for each LL S&E Credit CDR
		$intCount = 0;
		// RecordType 21 is Landline S&E
		$selSECCDRs = new StatementSelect(	"CDR",
											"Id, Service, Account, AccountGroup, Description, Charge",
											"Credit = 1 AND " .
											"RecordType = 21 AND " .
											"Status = CDR_RATED");
		$arrCols = Array();
		$arrCols['Status']	= NULL;
		$ubiSECCDR = new StatementUpdateById("CDR", $arrCols);
		
		$selSECCDRs->Execute();
		echo("CDR Id : Credited\n\n");
		while ($arrCredit = $selSECCDRs->Fetch())
		{
			// add to report
			//TODO!rich! replace this echo with report output
			echo("{$arrCredit['Id']} : {$arrCredit['Charge']}\n");
			
			// add to the count
			$intCount++;
			
			// charge late payment fee
			$arrCharge['Service'] 		= $arrCredit['Service'];
			$arrCharge['Account'] 		= $arrCredit['Account'];
			$arrCharge['AccountGroup'] 	= $arrCredit['AccountGroup'];
			$arrCharge['Description']	= $arrCredit['Description'];
			$arrCharge['Amount']		= $arrCredit['Charge'];
			$this->Framework->AddCharge($arrCharge);
			
			// Update the CDR
			$arrCredit['Status']		= CDR_CREDIT_ADDED;
			$ubiSECCDR->Execute($arrCredit);
		}
		
		// return count
		return $intCount;
	 }
 }


?>
