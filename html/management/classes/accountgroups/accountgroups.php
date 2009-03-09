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
					$cntContact = Contacts::UnarchivedUsername ($_POST ['Contact']['Email']);
				}
				catch (Exception $e)
				{
				}
				
				if ($cntContact)
				{
					throw new Exception ('Contact Email Exists');
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
			
			// Establish the payment terms for this customer group
			require_once("../../lib/classes/ORM.php");
			require_once("../../lib/classes/payment/Payment_Terms.php");
			
			$objPaymentTerms = Payment_Terms::getCurrentForCustomerGroup($arrDetails ['Account']['CustomerGroup']);
			
			if ($objPaymentTerms === NULL)
			{
				throw new Exception("Payment Terms have not been defined for this Customer Group");
			}
			
			$intPaymentTerms	= $objPaymentTerms->paymentTerms;
			$intBillingDate		= $objPaymentTerms->invoiceDay;
			
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
				'PrimaryContact'		=> ($cntContact !== null) ? $cntContact->Pull('Id')->getValue() : null,
				'CustomerGroup'			=> $arrDetails ['Account']['CustomerGroup'],
				'CreditCard'			=> ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD) ? $intCreditCard : null,
				'DirectDebit'			=> ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT) ? $intDirectDebit : null, 
				'AccountGroup'			=> $acgAccountGroup->Pull ('Id')->getValue (),
				'LastBilled'			=> null,
				'BillingDate'			=> $intBillingDate, 
				'BillingFreq'			=> BILLING_DEFAULT_FREQ,
				'BillingFreqType'		=> BILLING_DEFAULT_FREQ_TYPE,
				'BillingMethod'			=> $arrDetails ['Account']['BillingMethod'],		// (CONSTANT) post or email.
				'PaymentTerms'			=> $intPaymentTerms,
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'DisableDDR'			=> ($arrDetails ['Account']['DisableDDR']) ? $arrDetails ['Account']['DisableDDR'] : 0,
				'DisableLatePayment'	=> $arrDetails ['Account']['DisableLatePayment'],
				'Archived'				=> ACCOUNT_STATUS_PENDING_ACTIVATION
			);
			
			$insAccount = new StatementInsert ('Account', $arrAccount);
			$intAccount = $insAccount->Execute ($arrAccount);
			
			$actAccount = new Account ($intAccount);
			
			// Generate a system note declaring the creation of the Account
			$intEmployeeId		= $aemAuthenticatedEmployee->Pull('Id')->getValue();
			$intAccountId		= $actAccount->Pull('Id')->getValue();
			$intAccountGroup	= $arrAccount['AccountGroup'];
			
			// Retrieve the CustomerGroup from the database
			$selCustomerGroup	= new StatementSelect("CustomerGroup", "internal_name", "Id = <Id>");
			$selCustomerGroup->Execute(Array("Id" => $arrAccount['CustomerGroup']));
			$arrCustomerGroup	= $selCustomerGroup->Fetch();
			$strCustomerGroup	= $arrCustomerGroup['internal_name'];
		
			$strBillingType		= GetConstantDescription($arrAccount['BillingType'], "BillingType");
			$strBillingMethod	= GetConstantDescription($arrAccount['BillingMethod'], "BillingMethod");			

			$strNote  = "Account created with the following details:\n";
			$strNote .= "Business Name: {$arrAccount['BusinessName']}\n";
			$strNote .= "Trading Name: {$arrAccount['TradingName']}\n";
			$strNote .= "ABN: {$arrAccount['ABN']}\n";
			$strNote .= "ACN: {$arrAccount['ACN']}\n";
			$strNote .= "Address line 1: {$arrAccount['Address1']}\n";
			$strNote .= "Address line 2: {$arrAccount['Address2']}\n";
			$strNote .= "Suburb: {$arrAccount['Suburb']}\n";
			$strNote .= "Postcode: {$arrAccount['Postcode']}\n";
			$strNote .= "State: {$arrAccount['State']}\n";
			$strNote .= "Customer Group: $strCustomerGroup\n";
			$strNote .= "Account Group: $intAccountGroup\n";
			$strNote .= "Billing Type: $strBillingType\n";
			$strNote .= "Billing Method: $strBillingMethod\n";
	
			$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, $intEmployeeId, $intAccountGroup, $intAccount, NULL, NULL);
			
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
