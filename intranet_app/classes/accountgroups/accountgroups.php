<?php

	//----------------------------------------------------------------------------//
	// accountgroups.php
	//----------------------------------------------------------------------------//
	/**
	 * accountgroups.php
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * @file		accountgroups.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// __construct
	//----------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Controls Searching for an existing account group
	 *
	 * Controls Searching for an existing account group
	 *
	 *
	 * @prefix		ags
	 *
	 * @package		intranet_app
	 * @class		AccountGroups
	 * @extends		Search
	 */
	
	class AccountGroups extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Account Group Searching Routine
		 *
		 * Constructs an Account Group Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('AccountGroups', 'AccountGroup', 'AccountGroup');
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Creates a new Account Group and an Associated Account
		 *
		 * Creates a new Account Group and an Associated Account. This method will
		 * return the account that was created. From here, the Account Group is 
		 * derivative
		 *
		 * @param	AccountGroup				$acgAccountGroup			The AccountGroup where the Account will be added. (NULL = new account group)
		 * @param	Contact						$cntContact					The Primary Contact associated with this Account. (NULL = new contact)
		 * @param	AuthenticatedEmployee		$aemAuthenticatedEmployee	The Employee adding this Account Group / Account / Contact / DD / CC
		 * @param	Array						$arrDetails					Details relating to the Account being created
		 * @return	Integer
		 *
		 * @method
		 */
		
		public function Add (AccountGroup $acgAccountGroup=null, Contact $cntContact=null, AuthenticatedEmployee $aemAuthenticatedEmployee, $arrDetails)
		{
			// Check the primary details are valid
			$abnABN = new ABN ('ABN', '');
			if (!$abnABN->setValue ($arrDetails ['Account']['ABN']))
			{
				throw new Exception ('ABN');
			}
			
			$acnACN = new ACN ('ACN', '');
			if (!$acnACN->setValue ($arrDetails ['Account']['ACN']))
			{
				throw new Exception ('ACN');
			}
			
			$bmeBillingMethods = new BillingMethods ();
			if (!$bmeBillingMethods->setValue ($arrDetails ['Account']['BillingMethod']))
			{
				throw new Exception ('Not a valid BillingMethod: ' . $arrDetails ['Account']['BillingMethod']);
			}
			
			$btyBillingTypes = new BillingTypes ();
			if (!$btyBillingTypes->setValue ($arrDetails ['Account']['BillingType']))
			{
				throw new Exception ('Not a valid BillingType: ' . $arrDetails ['Account']['BillingType']);
			}
			
			if ($cntContact != null)
			{
				// Check the Contact is in the Account Group
				if ($acgAccountGroup->Pull ('Id')->getValue () <> $cntContact->Pull ('AccountGroup')->getValue ())
				{
					throw new Exception ('Contact');
				}
			}
			else
			{
				// If the UserName is not Unique, error
				try
				{
					$cntContact = Contacts::UnarchivedUsername ($_POST ['Contact']['UserName']);
				}
				catch (Exception $e)
				{
				}
				
				if ($cntContact)
				{
					throw new Exception ('UserName Exists');
				}
			}
			
			// If no account group has been specified, create a new one
			if ($acgAccountGroup == null)
			{
				$arrAccountGroup = Array (
					'CreatedBy'			=> '',
					'CreatedOn'			=> new MySQLFunction ("NOW()"),
					'ManagedBy'			=> null,
					'Archived'			=> 0
				);
				
				$insAccountGroup = new StatementInsert ('AccountGroup', $arrAccountGroup);
				$intAccountGroup = $insAccountGroup->Execute ($arrAccountGroup);
				
				$acgAccountGroup = new AccountGroup ($intAccountGroup);
			}
			
			if ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD)
			{
				$crcCreditCard = $acgAccountGroup->AddCreditCard ($arrDetails ['CreditCard']);
				$intCreditCard = $crcCreditCard->Pull ('Id')->getValue ();
			}
			
			if ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT)
			{
				$ddrDirectDebit = $acgAccountGroup->AddDirectDebit ($arrDetails ['DirectDebit']);
				$intDirectDebit = $ddrDirectDebit->Pull ('Id')->getValue ();
			}
			
			// Add the Account
			$arrAccount = Array (
				'BusinessName'			=> $arrDetails ['Account']['BusinessName'],
				'TradingName'			=> $arrDetails ['Account']['TradingName'],
				'ABN'					=> $abnABN->getValue (),
				'ACN'					=> $acnACN->getValue (),
				'Address1'				=> $arrDetails ['Account']['Address1'],
				'Address2'				=> $arrDetails ['Account']['Address2'],
				'Suburb'				=> $arrDetails ['Account']['Suburb'],
				'Postcode'				=> $arrDetails ['Account']['Postcode'],
				'State'					=> $arrDetails ['Account']['State'],
				'Country'				=> 'AU',
				'BillingType'			=> $arrDetails ['Account']['BillingType'],			// (CONSTANT) Account, Credit Card or Direct Debit.
				'PrimaryContact'		=> ($cntContact !== null) ? $cntContact : null,
				'CustomerGroup'			=> $arrDetails ['Account']['CustomerGroup'],
				'CreditCard'			=> ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD) ? $intCreditCard : null,
				'DirectDebit'			=> ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT) ? $intDirectDebit : null, 
				'AccountGroup'			=> $acgAccountGroup->Pull ('Id')->getValue (),
				'LastBilled'			=> null,
				'BillingDate'			=> BILLING_DEFAULT_DATE, 
				'BillingFreq'			=> BILLING_DEFAULT_FREQ,
				'BillingFreqType'		=> BILLING_DEFAULT_FREQ_TYPE,
				'BillingMethod'			=> $arrDetails ['Account']['BillingMethod'],		// (CONSTANT) post or email.
				'PaymentTerms'			=> PAYMENT_TERMS_DEFAULT,
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'DisableDDR'			=> ($arrDetails ['Account']['DisableDDR']) ? $arrDetails ['Account']['DisableDDR'] : 0,
				'DisableLatePayment'	=> $arrDetails ['Account']['DisableLatePayment'],
				'Archived'				=> 0
			);
			
			$insAccount = new StatementInsert ('Account', $arrAccount);
			$intAccount = $insAccount->Execute ($arrAccount);
			
			$actAccount = new Account ($intAccount);
			
			// After a new account is created a System note is generated
			$strEmployeeFirstName = $aemAuthenticatedEmployee->Pull('FirstName')->getValue();
			$strEmployeeLastName = $aemAuthenticatedEmployee->Pull('LastName')->getValue();
			$intEmployeeId = $aemAuthenticatedEmployee->Pull('Id')->getValue();
			$strEmployeeFullName =  "$strEmployeeFirstName $strEmployeeLastName";
		
			$intAccountId = $actAccount->Pull('Id')->getValue();
			$intAccountGroup = $arrAccount['AccountGroup'];
			$strBusinessName = $arrAccount['BusinessName'];
			$strTradingName = $arrAccount['TradingName'];
			$intABN = $arrAccount['ABN'];
			$intPostcode = $arrAccount['Postcode'];
			$strState = $arrAccount['State'];
			$intCustomerGroup = GetConstantDescription($arrAccount['CustomerGroup'], "CustomerGroup");
			$strBillingType= GetConstantDescription($arrAccount['BillingType'], "BillingType");
			$strBillingMethod = GetConstantDescription($arrAccount['BillingMethod'], "BillingMethod");			

			$strNote = "Account created by $strEmployeeFullName on " . date('m/d/y') . "\n";
			$strNote .= "The following details were created:\n";
			$strNote .= "Business Name: $strBusinessName\n";
			$strNote .= "Trading Name: $strTradingName\n";
			$strNote .= "ABN: $intABN\n";
			$strNote .= "Postcode: $intPostcode\n";
			$strNote .= "State: $strState\n";
			$strNote .= "Customer Group: $intCustomerGroup\n";
			$strNote .= "Account Group: $intAccountGroup\n";
			$strNote .= "Billing Type: $strBillingType\n";
			$strNote .= "Billing Method: $strBillingMethod\n";
	
			$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE, $intEmployeeId, $intAccountGroup, $intAccount, NULL, NULL);
			
			if ($cntContact == null)
			{
				// This is only called when we are creating a new contact
				$ctsContacts = new Contacts ();
				$intContact = $ctsContacts->Add (
					$actAccount,
					$arrDetails ['Contact']
				);
				
				$arrPrimaryContact = Array (
					'PrimaryContact'	=> $intContact
				);
				
				$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrPrimaryContact);
				$updAccount->Execute ($arrPrimaryContact, Array ('Id' => $intAccount));
			}
			
			return $actAccount;
		}
	}
	
?>
