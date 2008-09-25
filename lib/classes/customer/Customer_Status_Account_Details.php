<?php
/*
 * Created on 25/08/2008
 *
 * This will store all the details that will be required of the Customer Status Criteria, so that it can be tested, and retrieved easily
 * 
 */

class Customer_Status_Account_Details
{
	private $accountId = NULL;
	private $billingMethod = NULL;
	private $billingType = NULL;
	private $creditControlStatus = NULL;
	private $tioReferenceNumber = NULL;
	private $invoiceSettledOn = NULL;
	private $invoiceBalance = NULL;
	private $invoiceDisputed = NULL;
	private $invoiceRunId = NULL;
	private $invoiceId = NULL;
	private $invoiceActionHistory = NULL;
	
	private $_arrServicePlans = NULL;
	
	// Store these statically so that they only need to be initialised once
	static private $_selAccountDetails		= NULL;
	static private $_selInvoice				= NULL;
	static private $_selInvoiceActionHistory	= NULL;
	
	// Pre: The Account had an Invoice produced for InvoiceRun $intInvoiceRunId
	public function __construct($intAccountId, $intInvoiceRunId)
	{
		$this->accountId	= $intAccountId;
		$this->invoiceRunId	= $intInvoiceRunId;
		
		if ($this->_selAccountDetails === NULL)
		{
			// Build the Account StatementSelect object
			$arrColumns = array(	"BillingMethod"			=> "BillingMethod",
									"BillingType"			=> "BillingType",
									"credit_control_status"	=> "credit_control_status",
									"tio_reference_number"	=> "tio_reference_number"
								);
			$this->_selAccountDetails = new StatementSelect("Account", $arrColumns, "Id = <AccountId>");
		}
		if ($this->_selInvoice === NULL)
		{
			// Build the Invoice StatementSelect object
			// It is assumed only one invoice is produced during an invoice_r
			$arrColumns = array(	"InvoiceId"			=> "i.Id",
									"InvoiceSettledOn"	=> "i.SettledOn",
									"InvoiceBalance"	=> "i.Balance",
									"InvoiceDisputed"	=> "i.Disputed"
								);
			$strWhere	= "i.Account = <AccountId> AND ir.id = <InvoiceRunId>";
			$strTables	= "Invoice AS i INNER JOIN InvoiceRun AS ir ON i.invoice_run_id = ir.Id";
			$this->_selInvoice = new StatementSelect($strTables, $arrColumns, $strWhere);
		}
		if ($this->_selInvoiceActionHistory === NULL)
		{
			// Build the InvoiceActionHistory StatementSelect object
			$arrColumns = array(	"to_action", 
									"from_action",
									"change_datetime");
			$this->_selInvoiceActionHistory = new StatementSelect("automatic_invoice_action_history", $arrColumns, "account = <AccountId> AND invoice_run_id = <InvoiceRunId>", "id ASC");
		}
		// Build the ServiceRatePlans StatementSelect object
		// This retrieves the current plan details for each service belonging to the account, which is currently active AND not scheduled to be closed in the future
		// It will also return the current service id of services that don't have plans, but are currently active and not scheduled to be closed in the future 
		// NOTE that this object cannot be cached because the AccountId is used in the FROM clause
		$arrColumns = array("ServiceId"			=> "current_service_rate_plan_records.service_id",
							"FNN"				=> "s1.FNN",
							"LineStatus"		=> "s1.LineStatus",
							"CreatedOn"			=> "s1.CreatedOn",
							"ClosedOn"			=> "s1.ClosedOn",
							"RatePlanId"		=> "rp1.Id",
							"ContractTerm"		=> "rp1.ContractTerm",
							"StartDatetime"		=> "srp1.StartDatetime",
							"EndDatetime"		=> "srp1.EndDatetime",
							"ContractExpiresOn"	=> "srp1.StartDatetime + INTERVAL rp1.ContractTerm MONTH"
							);
		$strTables	= "(	SELECT current_services.service_id AS service_id, MAX(srp2.Id) AS service_rate_plan_id
							FROM ServiceRatePlan AS srp2 RIGHT JOIN (	SELECT MAX(Id) AS service_id
																		FROM Service
																		WHERE Account = $intAccountId AND CreatedOn < NOW() AND (ClosedOn IS NULL OR ClosedOn > NOW())
																		GROUP BY FNN
																	) AS current_services
																	ON srp2.Service = current_services.service_id AND NOW() BETWEEN srp2.StartDatetime AND srp2.EndDatetime
							GROUP BY current_services.service_id
						) AS current_service_rate_plan_records LEFT JOIN ServiceRatePlan AS srp1 ON (current_service_rate_plan_records.service_rate_plan_id = srp1.Id) 
						LEFT JOIN RatePlan AS rp1 ON srp1.RatePlan = rp1.Id INNER JOIN Service AS s1 ON current_service_rate_plan_records.service_id = s1.Id";
		$selServiceRatePlans = new StatementSelect($strTables, $arrColumns);
		
		$arrWhere = array("AccountId" => $intAccountId, "InvoiceRunId" => $intInvoiceRunId);
		
		// Fetch account details
		if (($intRecCount = $this->_selAccountDetails->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to retrieve details from Account table for account: $intAccountId - ". $this->_selAccountDetails->Error());
		}
		if ($intRecCount != 1)
		{
			// Could not find the account
			throw new Exception("Failed to find details from Account table for account: $intAccountId");
		}
		// Save the properties
		$arrRecord = $this->_selAccountDetails->Fetch();
		foreach ($arrRecord as $strProperty=>$mixValue)
		{
			$this->{$this->tidyName($strProperty)} = $mixValue;
		}
		
		// Fetch invoice details
		if (($intRecCount = $this->_selInvoice->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to retrieve details from Invoice table for account: $intAccountId, and InvoiceRunId: $intInvoiceRunId - ". $this->_selInvoice->Error());
		}
		if ($intRecCount == 1)
		{
			// An invoice was found.  Save the properties
			$arrRecord = $this->_selInvoice->Fetch();
			foreach ($arrRecord as $strProperty=>$mixValue)
			{
				$this->{$this->tidyName($strProperty)} = $mixValue;
			}
		}
		
		// Fetch Invoice Action History
		if (($intRecCount = $this->_selInvoiceActionHistory->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to retrieve details from automatic_invoice_action_history table for account: $intAccountId, and InvoiceRunId: $intInvoiceRunId - ". $this->_selInvoiceActionHistory->Error());
		}
		if ($intRecCount > 0)
		{
			$this->invoiceActionHistory = $this->_selInvoiceActionHistory->FetchAll();
		}
		else
		{
			// No actions have taken place regarding this Account/InvoiceRun
			$this->invoiceActionHistory = array();
		} 
		
		// Fetch Service Plan details
		if (($intRecCount = $selServiceRatePlans->Execute()) === FALSE)
		{
			throw new Exception("Failed to retrieve Service Plan details for account: $intAccountId - ". $selServiceRatePlans->Error());
		}
		
		if ($intRecCount > 0)
		{
			$this->_arrServicePlans = $selServiceRatePlans->FetchAll();
		}
		else
		{
			// There are no currently active services that aren't sched
			$this->_arrServicePlans = array();
		}
		
	}
	
	// returns TRUE if the automatic invoice action ($intAction) has been performed on this account, for this invoice run
	public function hadAutomaticInvoiceAction($intAction)
	{
		foreach ($this->invoiceActionHistory as $arrAction)
		{
			if ($arrAction["to_action"] == $intAction)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	// returns TRUE if the account has at least 1 service which is currently still in contract and its contract term is at least $intMinContractTermInMonths months long.
	// Note that this will return false if the account has a service that has a plan with a contract term >= $intMinContractTermInMonths AND the contract expires in the future
	// BUT the EndDatetime of the plan's association with the service < the contract's expiry date even if (EndDatetime - StartDatetime) > $intMinContractTermInMonths
	public function isInContract($intMinContractTermInMonths)
	{
		$strNow = GetCurrentISODateTime();

		foreach ($this->_arrServicePlans as $arrService)
		{
			if (	$arrService['ContractTerm'] >= $intMinContractTermInMonths && 
					$arrService['ContractExpiresOn'] > $strNow && 
					$arrService['EndDatetime'] >= $arrService['ContractExpiresOn'] && 
					($arrService['ClosedOn'] === NULL || $arrService['ClosedOn'] >= $arrService['ContractExpiresOn'])
				)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	// If an Account has no currently active/pending services then it is considered lost
	// If an Account has all its active services with LineStatus == Churned | Rejected | Disconnected, then it is considered lost
	public function hasAllServicesLost()
	{
		if (count($this->_arrServicePlans) == 0)
		{
			// There are no active services
			return TRUE;
		}
		
		foreach ($this->_arrServicePlans as $arrService)
		{
			if ($arrService['LineStatus'] != SERVICE_LINE_DISCONNECTED && $arrService['LineStatus'] != SERVICE_LINE_CHURNED && $arrService['LineStatus'] != SERVICE_LINE_REJECTED)
			{
				// This service is not lost
				return FALSE;
			}
		}
		
		// To have gotten this far, then each service must be lost
		return TRUE;
	}
	

	// Any protected data memeber can be accessed as long as it doesn't begin with '_'
	public function __get($strName)
	{
		if ($strName[0] === '_')
		{
			// Don't allow access to data attributes that start with '_'
			return NULL;
		}
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}

	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
	
	
}
 
?>
