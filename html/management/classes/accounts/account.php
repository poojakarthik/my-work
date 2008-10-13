<?php
	
	//----------------------------------------------------------------------------//
	// account.php
	//----------------------------------------------------------------------------//
	/**
	 * account.php
	 *
	 * File containing Account Class
	 *
	 * File containing Account Class
	 *
	 * @file		account.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Account
	//----------------------------------------------------------------------------//
	/**
	 * Account
	 *
	 * An account in the Database
	 *
	 * An account in the Database
	 *
	 *
	 * @prefix	act
	 *
	 * @package		intranet_app
	 * @class		Account
	 * @extends		dataObject
	 */
	
	class Account extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Account
		 *
		 * Constructor for a new Account
		 *
		 * @param	Integer		$intId		The Id of the Account being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccount = new StatementSelect ('Account', '*', 'Id = <Id>', null, 1);
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			
			if ($selAccount->Count () <> 1)
			{
				throw new Exception ('Account [' . $intId . '] does not exist.');
			}
			
			$selAccount->Fetch ($this);
			
			// allow for DisableLatePayment = NULL
			if (!$this->Pull('DisableLatePayment')->getValue())
			{
				// set DisableLatePayment to 0
				$this->Pull('DisableLatePayment')->setValue(0);
			}
			
			// Construct the object
			parent::__construct ('Account', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Update Account Information
		 *
		 * Update Information relating to an Account
		 *
		 * @param	Array		$arrAccount		An associative array representing new Account Information
		 * @return	Void
		 *
		 * @method
		 */
		 
		public function Update ($arrAccount)
		{
			// Ensure the Postcode is Valid
			if (!PostcodeValid ($arrAccount ['Postcode']))
			{
				throw new Exception ('Invalid Postcode');
			}
			
			$arrDetails = Array (
				"BusinessName"			=>	(($arrAccount ['BusinessName'])			? $arrAccount ['BusinessName']			: ''),
				"TradingName"			=>	(($arrAccount ['TradingName'])			? $arrAccount ['TradingName']			: ''),
				"ABN"					=>	(($arrAccount ['ABN'])					? $arrAccount ['ABN']					: ''),
				"ACN"					=>	(($arrAccount ['ACN'])					? $arrAccount ['ACN']					: ''),
				"Address1"				=>	(($arrAccount ['Address1'])				? $arrAccount ['Address1']				: ''),
				"Address2"				=>	(($arrAccount ['Address2'])				? $arrAccount ['Address2']				: ''),
				"Suburb"				=>	(($arrAccount ['Suburb'])				? $arrAccount ['Suburb']				: ''),
				"Postcode"				=>	(($arrAccount ['Postcode'])				? $arrAccount ['Postcode']				: ''),
				"State"					=>	(($arrAccount ['State'])				? $arrAccount ['State']					: ''),
				"DisableDDR"			=>	(($arrAccount ['DisableDDR'])			? $arrAccount ['DisableDDR']			: 0),
				"DisableLatePayment"	=>	(($arrAccount ['DisableLatePayment'])	? $arrAccount ['DisableLatePayment']	: 0),
				"CustomerGroup"			=>	(($arrAccount ['CustomerGroup'])		? $arrAccount ['CustomerGroup']			: 1),
				"BillingMethod"			=>	(($arrAccount ['BillingMethod'])		? $arrAccount ['BillingMethod']			: 0)
			);
			
			$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrDetails, 1);
			$updAccount->Execute ($arrDetails, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Contacts
		//------------------------------------------------------------------------//
		/**
		 * Contacts()
		 *
		 * Retrieves Contact List
		 *
		 * Retrieves Contact List
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		 
		public function Contacts ()
		{
			$oblarrContacts = new dataArray ('Contacts', 'Contact');
			
			$selContacts = new StatementSelect (
				'Contact', 
				'Id', 
				'(AccountGroup = <AccountGroup> AND CustomerContact = 1) ' .
				'OR (Account = <Account> AND CustomerContact = 0)'
			);
			
			$selContacts->Execute (
				Array (
					'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
					'Account'		=> $this->Pull ('Id')->getValue ()
				)
			);
			
			foreach ($selContacts->FetchAll () as $arrContact)
			{
				$oblarrContacts->Push (new Contact ($arrContact ['Id']));
			}
			
			return $oblarrContacts;
		}
		
		//------------------------------------------------------------------------//
		// Contact
		//------------------------------------------------------------------------//
		/**
		 * Contact()
		 *
		 * Retrieves a Contact if it has Access to this Account
		 *
		 * Retrieves a Contact if it has Access to this Account
		 *
		 * @param	Integer		$intContact			The Id of the Contact we are checking
		 * @return	Contact : NULL
		 *
		 * @method
		 */
		 
		public function Contact ($intContact)
		{
			$selContact = new StatementSelect (
				'Contact', 
				'Id', 
				'Id = <Id> AND ' .
				'(' .
					'(AccountGroup = <AccountGroup> AND CustomerContact = 1) ' .
					'OR (Account = <Account> AND CustomerContact = 0)' .
				')'
			);
			
			$selContact->Execute (
				Array (
					'Id'			=> $intContact,
					'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
					'Account'		=> $this->Pull ('Id')->getValue ()
				)
			);
			
			if ($selContact->Count () <> 1)
			{
				throw new Exception ('Contact Not Found');
			}
			
			return new Contact ($intContact);
		}
		
		//------------------------------------------------------------------------//
		// Invoices
		//------------------------------------------------------------------------//
		/**
		 * Invoices()
		 *
		 * Retrieves Invoice List
		 *
		 * Retrieves Invoice List
		 *
		 * @return	Invoices
		 *
		 * @method
		 */
		 
		public function Invoices ()
		{
			/* ORIGINAL
			$ivlInvoices = new Invoices;
			$ivlInvoices->Constrain ('Account', '=', $this->Pull ('Id')->getValue ());
			return $ivlInvoices;
			*/
			
			return new Invoices($this->Pull ('Id')->getValue ());
			
		}
		
		//------------------------------------------------------------------------//
		// Payments
		//------------------------------------------------------------------------//
		/**
		 * Payments()
		 *
		 * Retrieves Applied Payments that have been made specifically against this Account
		 *
		 * Retrieves Applied Payments that have been made specifically against this Account
		 *
		 * @return	Payments
		 *
		 * @method
		 */
		 
		public function Payments ()
		{
			return new AppliedPayments ($this);
		}
		
		//------------------------------------------------------------------------//
		// Payments_new
		//------------------------------------------------------------------------//
		/**
		 * Payments_new()
		 *
		 * Retrieves Payments that have been made specifically against this Account
		 *
		 * Retrieves Payments that have been made specifically against this Account
		 *
		 * @return	Payments
		 *
		 * @method
		 */
		 
		public function Payments_new ()
		{
			return new Payments_new ($this);
		}
		
		
		//------------------------------------------------------------------------//
		// PDFInvoices
		//------------------------------------------------------------------------//
		/**
		 * PDFInvoices()
		 *
		 * Retrieves a PDF Invoice List
		 *
		 * Retrieves a PDF Invoice List
		 *
		 * @return	Invoices_PDFs
		 *
		 * @method
		 */
		 
		public function PDFInvoices ()
		{
			return new Invoices_PDFs ($this);
		}
		
		//------------------------------------------------------------------------//
		// RecurringCharges
		//------------------------------------------------------------------------//
		/**
		 * RecurringCharges()
		 *
		 * Retrieves a Recurring Charges List
		 *
		 * Retrieves a Recurring Charges List
		 *
		 * @return	RecurringCharges
		 *
		 * @method
		 */
		 
		public function RecurringCharges ()
		{
			$rclRecurringCharges = new RecurringCharges ();
			$rclRecurringCharges->Constrain ('Account', '=', $this->Pull ('Id')->getValue ());
			return $rclRecurringCharges;
		}
		
		//------------------------------------------------------------------------//
		// ArchiveStatus
		//------------------------------------------------------------------------//
		/**
		 * ArchiveStatus()
		 *
		 * Update Account Archive Status Information
		 *
		 * Update Account Archive Status Information (Cascade Against Service and Specific Contacts)
		 *
		 * @param	Array		$bolStatus		Boolean representing whether to Archive (true) or unarchive (false)
		 * @return	Void
		 *
		 * @method
		 */
		 
		public function ArchiveStatus ($bolStatus)
		{
			// If we want to Archive the Account
			if ($bolStatus == 1)
			{
				$arrArchiveService = Array (
					"ClosedOn"	=>	new MySQLFunction ('NOW()')
				);
				
				// Cascade down to include the Services
				$updService = new StatementUpdate ('Service', 'Account = <Account> AND (ClosedOn IS NULL OR ClosedOn > Now())', $arrArchiveService);
				$updService->Execute ($arrArchiveService, Array ('Account' => $this->Pull ('Id')->getValue ()));
				
				// Set up an Archive SET clause
				$arrArchive = Array (
					"Archived"	=>	1
				);
				
				// Now we want to include the Contacts
				
				// Here are the preconditions for changing a contact to archived
				// 1.	If the Contact is not a CustomerContact
				// 2.	If the Contact is a Customer Contact
					//	2.1		AND there are no other Unarchived Accounts in the Account Group
				
				$updAccount = new StatementUpdate ('Contact', 'Account = <Account> AND CustomerContact = 0', $arrArchive);
				$updAccount->Execute ($arrArchive, Array ('Account' => $this->Pull ('Id')->getValue ()));
				
				// Count the number of Unarchived Accounts in the Account Group
				$selNumAccounts = new StatementSelect (
					'Account',
					'count(*) AS length',
					'AccountGroup = <AccountGroup> AND Id != <Id> AND Archived = 0'
				);
				
				$selNumAccounts->Execute (
					Array (
						'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
						'Id'			=> $this->Pull ('Id')->getValue ()
					)
				);
				
				$arrLength = $selNumAccounts->Fetch ();
				
				// If there are no "other" Accounts that are unarchived, we want to archive the Customer Contacts
				if ($arrLength ['length'] == 0)
				{
					$updAccount = new StatementUpdate ('Contact', 'Account = <Account> AND CustomerContact = 1', $arrArchive);
					$updAccount->Execute ($arrArchive, Array ('Account' => $this->Pull ('Id')->getValue ()));
				}
				
				
				// Finally - Alter the Account itself
				$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrArchive, 1);
				$updAccount->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
			}
			else
			{
				// If we reach this block, then we want to unarchive the account.
				// But, this is not a Cascadable action. Any services or contacts
				// that need to be unarchived need to be done manually.
				// This is set to avoid conflicts in the system.
				
				// Set up an Archive SET clause
				$arrArchive = Array (
					"Archived"	=>	0
				);
				
				// Alter the Account itself
				$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrArchive, 1);
				$updAccount->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
			}
		}
		
		//------------------------------------------------------------------------//
		// LesseeReceive
		//------------------------------------------------------------------------//
		/**
		 * LesseeReceive()
		 *
		 * Receives a Service to Take Ownership
		 *
		 * Receives a Service to Take Ownership
		 *
		 * @param	Service					$srvService					Boolean representing whether to Archive (true) or unarchive (false)
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee	The person who is performing this request
		 * @param	Array					$arrDetailsDate				The date which this service will begin
		 * @return	Void
		 *
		 * @method
		 */
		
		public function LesseeReceive(Service $srvService, AuthenticatedEmployee $aemAuthenticatedEmployee, $arrDetailsDate)
		{
			$intDate = mktime (0, 0, 0, $arrDetailsDate ['month'], $arrDetailsDate ['day'], $arrDetailsDate ['year']);
			
			// Cancel the Service on this specific date
			$arrService = Array (
				"FNN"				=>	$srvService->Pull ('FNN')->getValue (),
				"ServiceType"		=>	$srvService->Pull ('ServiceType')->getValue (),
				"Indial100"			=>	$srvService->Pull ('Indial100')->getValue (),
				"AccountGroup"		=>	$this->Pull ('AccountGroup')->getValue (),
				"Account"			=>	$this->Pull ('Id')->getValue (),
				"CappedCharge"		=>	0,
				"UncappedCharge"	=>	0,
				"CreatedOn"			=>	sprintf ("%04d", $arrDetailsDate ['year']) . "-" .
										sprintf ("%02d", $arrDetailsDate ['month']) . "-" .
										sprintf ("%02d", $arrDetailsDate ['day']),
				"CreatedBy"			=>	$aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				"Carrier"			=>	$srvService->Pull ('Carrier')->getValue (),
				"CarrierPreselect"	=>	$srvService->Pull ('CarrierPreselect')->getValue (),
				// This line was already here when I went to update this function to handle the new Status property in the Service Table
				//"Status"			=>	$srvService->Pull ('Status')->getValue ()
				// I have changed it to this
				"Status"			=>	SERVICE_ACTIVE
			);
			
			$insService = new StatementInsert ('Service', $arrService);
			$intService = $insService->Execute ($arrService);
			
			return $intService;
		}
		
		//------------------------------------------------------------------------//
		// CreditCard
		//------------------------------------------------------------------------//
		/**
		 * CreditCard()
		 *
		 * Adds the Credit Card information for this account to the Object
		 *
		 * Adds the Credit Card information for this account to the Object
		 *
		 * @return	CreditCard
		 *
		 * @method
		 */
		
		public function CreditCard ()
		{
			// If the Credit Card is already set, return it
			if ($this->_crcCreditCard)
			{
				return $this->_crcCreditCard;
			}
			
			// If the billing method is not VIA credit card, then we can't return anything
			if ($this->Pull ('BillingType')->getValue () != BILLING_TYPE_CREDIT_CARD)
			{
				return null;
			}
			
			// Get the Credit Card
			$intCreditCard = $this->Pull ('CreditCard')->getValue ();
			
			// Make sure the Credit Card is not Blank
			if (!$intCreditCard)
			{
				return null;
			}
			
			// Attach the information
			$oblarrCreditCardContainer = $this->Push (new dataArray ('CreditCardDetails', 'CreditCard'));
			$this->_crcCreditCard = $oblarrCreditCardContainer->Push (new CreditCard ($intCreditCard));
			
			// Return the Credit Card
			return $this->_crcCreditCard;
		}
		
		//------------------------------------------------------------------------//
		// DirectDebit
		//------------------------------------------------------------------------//
		/**
		 * DirectDebit()
		 *
		 * Adds the Direct Debit information for this account to the Object
		 *
		 * Adds the Direct Debit information for this account to the Object
		 *
		 * @return	DirectDebit
		 *
		 * @method
		 */
		
		public function DirectDebit ()
		{
			// If Direct Debit is already set, return it
			if ($this->_ddrDirectDebit)
			{
				return $this->_ddrDirectDebit;
			}
			
			// If the billing method is not VIA Direct Debit, then we can't return anything
			if ($this->Pull ('BillingType')->getValue () != BILLING_TYPE_DIRECT_DEBIT)
			{
				return null;
			}
			
			// Get the Direct Debit information
			$intDirectDebit = $this->Pull ('DirectDebit')->getValue ();
			
			// Make sure the Direct Debit Id is not Blank
			if (!$intDirectDebit)
			{
				return null;
			}
			
			// Attach the information
			$oblarrDirectDebitContainer = $this->Push (new dataArray ('DirectDebitDetails', 'DirectDebit'));
			$this->_ddrDirectDebit = $oblarrDirectDebitContainer->Push (new DirectDebit ($intDirectDebit));
			
			// Return the Direct Debit
			return $this->_ddrDirectDebit;
		}
		
		//------------------------------------------------------------------------//
		// AccountGroup
		//------------------------------------------------------------------------//
		/**
		 * AccountGroup()
		 *
		 * Retrieves the Associated Account Group
		 *
		 * Retrieves the Associated Account Group
		 *
		 * @return	AccountGroup
		 *
		 * @method
		 */
		 
		public function AccountGroup ()
		{
			return new AccountGroup ($this->Pull ('AccountGroup')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// BillingTypeSelect
		//------------------------------------------------------------------------//
		/**
		 * BillingTypeSelect()
		 *
		 * Changes the Account's Billing Type to a Specific Billing Type
		 *
		 * Changes the Account's Billing Type to a Specific Billing Type
		 *
		 * @param	Integer		$intBillingType			The way in which this Account will be billed (based on the BILLING_TYPE_* constants)
		 * @param	Integer		$objBillingVia			If $intBillingType is BILLING_TYPE_CREDIT_CARD, expect CreditCard object
		 * 												If $intBillingType is BILLING_TYPE_DIRECT_DEBIT, expect DirectDebit object
		 * 												If $intBillingType is BILLING_TYPE_ACCOUNT, expect NULL
		 * @return	Void
		 *
		 * @method
		 */
		 
		public function BillingTypeSelect ($intBillingType, $objBillingVia)
		{
			// Check the Billing Type is Valid
			$btyBillingType = new BillingTypes();
			
			if (!$btyBillingType->setValue ($intBillingType))
			{
				throw new Exception ('BillingType Invalid');
			}
			
			// Start the Array to Set Values
			$arrAccountBilling = Array (
				"BillingType"	=> $intBillingType,
				"DirectDebit"	=> null,
				"CreditCard"	=> null
			);
			
			// This Switch ensures that Direct Debit Billing Types have a correct Direct Debit
			// and that Credit Card Billing Types have a correct Credit Card
			switch ($intBillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					if (!($objBillingVia instanceOf DirectDebit))
					{
						throw new Exception ('BillingVia DDR Invalid');
					}
					
					// Update the Direct Debit
					$arrAccountBilling ['DirectDebit'] = $objBillingVia->Pull ('Id')->getValue ();
					break;
					
				case BILLING_TYPE_CREDIT_CARD:
					if (!($objBillingVia instanceOf CreditCard))
					{
						throw new Exception ('BillingVia CC Invalid');
					}
					
					// Update the Credit Card
					$arrAccountBilling ['CreditCard'] = $objBillingVia->Pull ('Id')->getValue ();
					break;
			}
			
			$updAccountBilling = new StatementUpdate('Account', 'Id = <Id>', $arrAccountBilling, 1);
			$updAccountBilling->Execute($arrAccountBilling, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// OverdueAmount
		//------------------------------------------------------------------------//
		/**
		 * OverdueAmount()
		 *
		 * Shows overdue charges
		 *
		 * Attaches an object and returns the same object displaying how much
		 * the current overdue charges are for the account.
		 *
		 * @return	dataFloat
		 *
		 * @method
		 */
		 
		public function OverdueAmount ()
		{
			/*$selOverdue = new StatementSelect ('Invoice', 'SUM(Balance) AS OverdueAmount', 'Now() > DueOn AND SettledOn IS NULL AND Account = <Account>');
			$selOverdue->Execute (Array ('Account' => $this->Pull ('Id')->getValue ()));
			if ($selOverdue->Count () == 1)
			{
				$arrOverdue = $selOverdue->Fetch ();
				$oblfltOverdue->setValue ($arrOverdue ['OverdueAmount']);
			}*/
			
			$fltOverdue = $GLOBALS['fwkFramework']->GetOverdueBalance($this->Pull ('Id')->getValue ());
			
			$oblfltOverdue = $this->Push (new dataFloat ('OverdueAmount'));
			$oblfltOverdue->setValue ($fltOverdue);
			
			return $oblfltOverdue;
		}
		
		//------------------------------------------------------------------------//
		// AddCostCentre
		//------------------------------------------------------------------------//
		/**
		 * AddCostCentre()
		 *
		 * Add a new Cost Centre
		 *
		 * Add a new Cost Centre
		 *
		 * @param	Array	$arrDetails		An associate array of details about the new Cost Centre
		 *
		 * @method
		 */
		
		public function AddCostCentre ($arrDetails)
		{
			$arrData = Array (
				'AccountGroup'		=> $this->Pull ('AccountGroup')->getValue (),
				'Account'			=> $this->Pull ('Id')->getValue (),
				'Name'				=> $arrDetails ['Name']
			);
			
			$insCostCentre = new StatementInsert ('CostCentre');
			$intCostCentre = $insCostCentre->Execute ($arrData);
			
			return $intCostCentre;
		}
		
		//------------------------------------------------------------------------//
		// ChargeAdd
		//------------------------------------------------------------------------//
		/**
		 * ChargeAdd()
		 *
		 * Add a charge against a Service
		 *
		 * Add a charge against a Service
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmploee	The person who is adding this charge to the database
		 * @param	ChargeType				$chgChargeType				The Type of Charge to Assign
		 * @param	String					$strAmount					The amount to charge against. If the charge type is fixed, this value is ignored
		 * @return	Void
		 *
		 * @method
		 */
		
		public function ChargeAdd (AuthenticatedEmployee $aemAuthenticatedEmployee, Service $srvService=NULL, ChargeType $chgChargeType, $strAmount, $intInvoice, $strNotes)
		{
			$fltAmount = 0;
			
			if ($chgChargeType->Pull ('Fixed')->isTrue ())
			{
				$fltAmount = $chgChargeType->Pull ('Amount')->getValue ();
			}
			else
			{
				$fltAmount = $strAmount;
				$fltAmount = preg_replace ('/\$/', '', $fltAmount);
				$fltAmount = preg_replace ('/\s/', '', $fltAmount);
				$fltAmount = preg_replace ('/\,/', '', $fltAmount);
				
				if (!preg_match ('/^([\d]*)(\.[\d]+){0,1}$/', $fltAmount))
				{
					throw new Exception ('Invalid Amount');
				}
			}
			
			$arrCharge = Array (
				'AccountGroup'			=> $this->Pull ('AccountGroup')->getValue (),
				'Account'				=> $this->Pull ('Id')->getValue (),
				'Service'				=> (($srvService) ? $srvService->Pull ('Id')->getValue () : NULL),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'ChargedOn'				=> new MySQLFunction ("NOW()"),
				'ChargeType'			=> $chgChargeType->Pull ('ChargeType')->getValue (),
				'Description'			=> $chgChargeType->Pull ('Description')->getValue (),
				'Nature'				=> $chgChargeType->Pull ('Nature')->getValue (),
				'Amount'				=> $fltAmount,
				'Invoice'				=> $intInvoice,
				'Notes'					=> $strNotes,
				'Status'				=> CHARGE_WAITING
			);
			
			$insCharge = new StatementInsert ('Charge', $arrCharge);
			$insCharge->Execute ($arrCharge);
		}
		
		//------------------------------------------------------------------------//
		// RecurringChargeAdd
		//------------------------------------------------------------------------//
		/**
		 * RecurringChargeAdd()
		 *
		 * Add a RecurringCharge against a Service
		 *
		 * Add a RecurringCharge against a Service
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmploee	The person who is adding this charge to the database
		 * @param	ChargeType				$chgChargeType				The Type of RecurringCharge to Assign
		 * @param	String					$strAmount					The amount to charge against. If the charge type is fixed, this value is ignored
		 * @return	Void
		 *
		 * @method
		 */
		
		public function RecurringChargeAdd (AuthenticatedEmployee $aemAuthenticatedEmployee, RecurringChargeType $rctRecurringChargeType, $strAmount)
		{
			$fltAmount = 0;
			//debug($this);die;
			if ($rctRecurringChargeType->Pull ('Fixed')->isTrue ())
			{
				$fltAmount = $rctRecurringChargeType->Pull ('RecursionCharge')->getValue ();
			}
			else
			{
				$fltAmount = $strAmount;
				$fltAmount = preg_replace ('/\$/', '', $fltAmount);
				$fltAmount = preg_replace ('/\s/', '', $fltAmount);
				$fltAmount = preg_replace ('/\,/', '', $fltAmount);
				
				if (!preg_match ('/^([\d]*)(\.[\d]+){0,1}$/', $fltAmount))
				{
					throw new Exception ('Invalid Amount');
				}
			}
			
			$arrRecurringCharge = Array (
				'AccountGroup'			=> $this->Pull ('AccountGroup')->getValue (),
				'Account'				=> $this->Pull ('Id')->getValue (),
				//'Service'				=> $this->Pull ('Id')->getValue (),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'StartedOn'				=> new MySQLFunction ("NOW()"),
				'LastChargedOn'			=> new MySQLFunction ("NOW()"),
				'ChargeType'			=> $rctRecurringChargeType->Pull ('ChargeType')->getValue (),
				'Description'			=> $rctRecurringChargeType->Pull ('Description')->getValue (),
				'Nature'				=> $rctRecurringChargeType->Pull ('Nature')->getValue (),
				'RecurringFreqType'		=> $rctRecurringChargeType->Pull ('RecurringFreqType')->getValue (),
				'RecurringFreq'			=> $rctRecurringChargeType->Pull ('RecurringFreq')->getValue (),
				'MinCharge'				=> $rctRecurringChargeType->Pull ('MinCharge')->getValue (),
				'RecursionCharge'		=> $fltAmount,
				'CancellationFee'		=> $rctRecurringChargeType->Pull ('CancellationFee')->getValue (),
				'Continuable'			=> $rctRecurringChargeType->Pull ('Continuable')->getValue (),
				'TotalCharged'			=> 0,
				'TotalRecursions'		=> 0,
				'Archived'				=> 0
			);
			
			$insRecurringCharge = new StatementInsert ('RecurringCharge', $arrRecurringCharge);
			$insRecurringCharge->Execute ($arrRecurringCharge);
		}
	}
	
?>
