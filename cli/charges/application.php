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
								  "AND RecurringFreq > 0 " .
								  "AND RecurringCharge.Archived = 0 " .
								  "AND Account.Archived = ".ACCOUNT_STATUS_ACTIVE." " .
								  "AND (Service.Status = ".SERVICE_ACTIVE." OR Service.Status IS NULL) " .
								  "(" .
								  "		Continuable = 1 " .
								  "		OR " .
								  "		(Continuable = 0 AND MinCharge > TotalCharged)" .
								  ") " .
								  "AND NOW() >= ADDDATE(StartedOn, INTERVAL RecurringFreq * (TotalRecursions + IF(in_advance = 1, 0, 1)) MONTH)";
								  
		$this->_selGetCharges	= new StatementSelect("(RecurringCharge JOIN Account ON Account.Id = RecurringCharge.Account) LEFT JOIN Service ON Service.Id = RecurringCharge.Service", "RecurringCharge.*", $arrWhere, NULL, "1000");
		
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
		$arrColumns = Array();
		$arrColumns['Status']	= CDR_TEMP_CREDIT;
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
				switch ($arrCharge['RecurringFreqType'])
				{
					case BILLING_FREQ_DAY:
						CliEcho("Flex no longer supports BILLING_FREQ_DAY for Recurring Charges!");
						continue;
					case BILLING_FREQ_MONTH:
						$intTotalRecursions	= ($arrCharge['in_advance']) ? $arrCharge['TotalRecursions'] : $arrCharge['TotalRecursions'] + 1;
						$strDate			= date("Y-m-d", strtotime("+".$arrCharge['RecurringFreq']." months", strtotime($arrCharge['StartedOn'])));
						break;
					case BILLING_FREQ_HALF_MONTH:
						CliEcho("Flex no longer supports BILLING_FREQ_HALF_MONTH for Recurring Charges!");
						continue;
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
				$arrData['ApprovedBy']		= $arrCharge['CreatedBy'];
				$arrData['ChargeType']		= $arrCharge['ChargeType'];
				$arrData['Description']		= $arrCharge['Description'];
				$arrData['ChargedOn']		= $strDate;
				$arrData['Nature']			= $arrCharge['Nature'];
				$arrData['Amount']			= $arrCharge['RecursionCharge'];
				$arrData['OriginType']		= CHARGE_LINK_RECURRING;
				$arrData['OriginId']		= $arrCharge['Id'];
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
					$intNotUnique = FALSE; // Added as false, just so that it is initialised!
					//$intNotUnique = // Note: It looks like this is referred to as $intNonUnique elsewhere!!!
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
	 	// No longer used
	 	return FALSE;
	 	/*
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
		$selLPAccounts = new StatementSelect('Account', 'Id, AccountGroup, DisableLatePayment', 'DisableLatePayment < 1 AND Archived = 0');
		$ubiAccount = new StatementUpdateById("Account", Array('DisableLatePayment' => NULL));
		$selLPAccounts->Execute();
		echo("Account : Overdue\n");
		while ($arrAccount = $selLPAccounts->Fetch())
		{
			// Reduce the number of times the customer can make a late payment & update
			if ($arrAccount['DisableLatePayment'] < 0)
			{
				$arrData = Array();
				$arrData['Id']					= $arrAccount['Id'];
				$arrData['DisableLatePayment']	= $arrAccount['DisableLatePayment'] + 1;
				$ubiAccount->Execute($arrData);
				
				// Skip this account
				continue;
			}
			
			// check for an overdue balance
			if (($fltBalance = $this->Framework->GetOverdueBalance($arrAccount['Id'])) > 10)
			{
				// add to report
				//TO DO!rich! replace this echo with report output
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
		// TO DO!flame! make this do something
		
		// return count
		return $intCount;
		*/
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
		$arrCharge ['Description']	= "Inbound Service Fee";
		$arrCharge ['ChargeType']	= "INB15";
		$arrCharge ['ChargedOn']	= date("Y-m-d");
		$arrCharge ['Amount']		= 15.00;
		$arrCharge ['Status']		= CHARGE_APPROVED;
		
	 	// for each active inbound service
		$intCount = 0;
		$selINB15Services = new StatementSelect('CDR JOIN Account ON Account.Id = CDR.Account', 
												'Service, Account, Account.AccountGroup, COUNT(CDR.Id) AS CDRCount', 
												"Account.Archived NOT IN (1, 3) AND Service IS NOT NULL AND Credit = 0 AND Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND ServiceType = ".SERVICE_TYPE_INBOUND, 
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
		$arrCharge ['ChargedOn']	= date("Y-m-d");
		$arrCharge ['Amount']		= 15.00;
		$arrCharge ['Status']		= CHARGE_APPROVED;
		
	 	// for each Pinnacle Mobile Service
		$intCount = 0;
		$selPM15Services = new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service",
												"Service, Account, AccountGroup",
												"(Service.ClosedOn IS NULL OR Service.ClosedOn > CURDATE()) AND " .
												"ServiceRatePlan.RatePlan = 20 AND " .
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
		$arrCharge ['ChargedOn']	= date("Y-m-d");
		$arrCharge ['ChargeType']	= "SEC";
		$arrCharge ['Status']		= CHARGE_APPROVED;
		$arrCharge ['LinkType']		= CHARGE_LINK_CDR_CREDIT;
		
	 	// for each LL S&E Credit CDR
		$intCount = 0;
		// RecordType 21 is Landline S&E
		$selSECCDRs = new StatementSelect(	"CDR",
											"Id, Service, Account, AccountGroup, Description, Charge",
											"Credit = 1 AND " .
											"RecordType = 21 AND " .
											"Status IN (".CDR_RATED.", ".CDR_CREDIT_MATCH_NOT_FOUND.")");
		$arrCols = Array();
		$arrCols['Status']	= NULL;
		$ubiSECCDR = new StatementUpdateById("CDR", $arrCols);
		
		$selSECCDRs->Execute();
		while ($arrCredit = $selSECCDRs->Fetch())
		{
			// add to report
			//TODO!rich! replace this echo with report output
			
			// add to the count
			$intCount++;
			
			// charge late payment fee
			$arrCharge['Service'] 		= $arrCredit['Service'];
			$arrCharge['Account'] 		= $arrCredit['Account'];
			$arrCharge['AccountGroup'] 	= $arrCredit['AccountGroup'];
			$arrCharge['Description']	= $arrCredit['Description'];
			$arrCharge['Amount']		= $arrCredit['Charge'];
			$arrCharge['LinkId']		= $arrCredit['Id'];
			if (!$this->Framework->AddCharge($arrCharge))
			{
				// Don't set this CDR to CDR_CREDIT_ADDED
				CliEcho("\t + Couldn't Credit CDR #{$arrCredit['Id']}! (\${$arrCredit['Charge']})");
			}
			else
			{
				// Update the CDR
				CliEcho("\t + Credited CDR #{$arrCredit['Id']} for \${$arrCredit['Charge']}\n");
				$arrCredit['Status']		= CDR_CREDIT_ADDED;
				$ubiSECCDR->Execute($arrCredit);
			}
		}
		
		// return count
		return $intCount;
	 }
	
	//------------------------------------------------------------------------//
	// AddNonDDRFee
	//------------------------------------------------------------------------//
	/**
	 * AddNonDDRFee()
	 *
	 * Add Non-Direct Debit Fees
	 *
	 * Add Non-Direct Debit Fees
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	 function AddNonDDRFee()
	 {
	 	// No longer used
	 	return FALSE;
	 	
	 	// set up charge
		$arrCharge = Array();
		$arrCharge ['Nature']		= 'DR';
		$arrCharge ['Description']	= "Account Processing Fee";
		$arrCharge ['ChargeType']	= "AP250";
		$arrCharge ['ChargedOn']	= date("Y-m-d");
		$arrCharge ['CreatedOn']	= date("Y-m-d");
		$arrCharge ['Amount']		= 2.50;
		$arrCharge ['Status']		= CHARGE_APPROVED;
		$arrCharge ['Notes']		= "";
		
	 	// for each account without a DDR Fee Waive and no/out-of-date CC or DDR info
		$intCount = 0;
		$selNDDRAccounts = new StatementSelect(	'Account', 
												'*', 
												'Archived = 0 AND DisableDDR = 0 AND BillingType = '.BILLING_TYPE_ACCOUNT);
		$selTollingAccounts = new StatementSelect(	"CDR USE INDEX (Account_2)",
													"CDR.Id AS Id",
													"CDR.Account = <Account> AND " .
													"CDR.Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND " .
													"CDR.Credit = 0 " .
													"\nLIMIT 1\n" .
													"UNION\n" .
													"SELECT Charge.Id AS Id\n" .
													"FROM Charge\n" .
													"WHERE Charge.Account = <Account> AND \n" .
													"Charge.Status = ".CHARGE_APPROVED." AND " .
													"Charge.Nature = 'DR'\n" .
													"LIMIT 1");
		$intTotal = $selNDDRAccounts->Execute();
		$intSkipped = 0;
		echo("Account\n\n");
		while ($arrAccount = $selNDDRAccounts->Fetch())
		{
			// Check if this Account is tolling
			$arrWhere = Array();
			$arrWhere['Account']	= $arrAccount['Id'];
			if (!$selTollingAccounts->Execute($arrWhere))
			{
				// There were no results, so skip the Account
				$intSkipped++;
				continue;
			}
			
			// add to report
			//TODO!rich! replace this echo with report output
			echo $arrAccount['Id'];
			
			// charge NDDR fee
			$arrCharge['Account'] 		= $arrAccount['Id'];
			$arrCharge['AccountGroup'] 	= $arrAccount['AccountGroup'];
			if ($this->Framework->AddCharge($arrCharge))
			{
				// add to the count
				$intCount++;
				echo "\t\t[   OK   ]\n";
			}
			else
			{
				echo "\t\t[ FAILED ]\n";
			}
			
			ob_flush();
		}
		
		echo "\n\nTotal: $intTotal; Passed: $intCount; Skipped: $intSkipped\n\n";
		
		// return count
		return $intCount;
	 }
	
	//------------------------------------------------------------------------//
	// MarkInboundSAndECDR
	//------------------------------------------------------------------------//
	/**
	 * MarkInboundSAndECDR()
	 *
	 * Marks Inbound S&E CDRs so that they are not invoiced
	 *
	 * Marks Inbound S&E CDRs so that they are not invoiced
	 *
	 * @return			VOID
	 *
	 * @method
	 */
	 function MarkInboundSAndECDR()
	 {
	 	$arrColumns = Array();
	 	$arrColumns['Status']	= CDR_IGNORE_INBOUND_SE; 
	 	$updInboundSECDRs = new StatementUpdate("CDR",
												/*"Status = ".CDR_RATED." AND " .*/
												"RecordType = 30 AND " .
												"Charge < 30",
												$arrColumns);
												
		echo "\n\nUpdating Inbound S&E CDRs...\t\t\t";
		if (!$intCount = $updInboundSECDRs->Execute($arrColumns, Array()))
		{
			echo "[ FAILED ]\n\n";
			return FALSE;
		}
		
		echo "[   OK   ]\n\nCOMPLETED!  $intCount CDRs updated.\n";
	 }
 }


?>
