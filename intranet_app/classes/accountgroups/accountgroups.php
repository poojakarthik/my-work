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
		 * @param	AccountGroup	$acgAccountGroup	The AccountGroup where the Account will be added. NULL if creating a new account group
		 * @param	Contact			$cntContact			The Primary Contact associated with this Account. NULL If creating a new contact
		 * @param	Array			$arrDetails			Details relating to the Account being created
		 * @return	Integer
		 *
		 * @method
		 */
		
		public function Add (AccountGroup $acgAccountGroup=null, Contact $cntContact=null, $arrDetails)
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
					'CreatedOn'			=> date ('Y-m-d'),
					'ManagedBy'			=> null,
					'Archived'			=> 0
				);
				
				$insAccountGroup = new StatementInsert ('AccountGroup');
				$intAccountGroup = $insAccountGroup->Execute ($arrAccountGroup);
				
				$acgAccountGroup = new AccountGroup ($intAccountGroup);
			}
			
			if ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD)
			{
				$arrCreditCard = Array (
					"AccountGroup"	=> $acgAccountGroup->Pull ('Id')->getValue (),
					"CardType"		=> $arrDetails ['CreditCard']['CardType'],
					"Name"			=> $arrDetails ['CreditCard']['Name'],
					"CardNumber"	=> $arrDetails ['CreditCard']['CardNumber'],
					"ExpMonth"		=> $arrDetails ['CreditCard']['ExpMonth'],
					"ExpYear"		=> $arrDetails ['CreditCard']['ExpYear'],
					"CVV"			=> $arrDetails ['CreditCard']['CVV']
				);
				
				$insCreditCard = new StatementInsert ('CreditCard');
				$intCreditCard = $insCreditCard->Execute ($arrCreditCard);
			}
			
			if ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT)
			{
				$ddrDirectDebit = $acgAccountGroup->AddDirectDebit ($arrDetails ['DirectDebit']);
				$intDirectDebit = $ddrDirectDebit->Pull ('Id')->getValue ();
			}
			
			// Add the Account
			$arrAccount = Array (
				'BusinessName'		=> $arrDetails ['Account']['BusinessName'],
				'TradingName'		=> $arrDetails ['Account']['TradingName'],
				'ABN'				=> $abnABN->getValue (),
				'ACN'				=> $acnACN->getValue (),
				'Address1'			=> $arrDetails ['Account']['Address1'],
				'Address2'			=> $arrDetails ['Account']['Address2'],
				'Suburb'			=> $arrDetails ['Account']['Suburb'],
				'Postcode'			=> $arrDetails ['Account']['Postcode'],
				'State'				=> $arrDetails ['Account']['State'],
				'Country'			=> 'AU',
				'BillingType'		=> $arrDetails ['Account']['BillingType'],			// (CONSTANT) Account, Credit Card or Direct Debit.
				'PrimaryContact'	=> ($cntContact !== null) ? $cntContact : null,
				'CustomerGroup'		=> $arrDetails ['Account']['CustomerGroup'],
				'CreditCard'		=> ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD) ? $intCreditCard : null,
				'DirectDebit'		=> ($arrDetails ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT) ? $intDirectDebit : null, 
				'AccountGroup'		=> $acgAccountGroup->Pull ('Id')->getValue (),
				'LastBilled'		=> null,
				'BillingDate'		=> BILLING_DEFAULT_DATE, 
				'BillingFreq'		=> BILLING_DEFAULT_FREQ,
				'BillingFreqType'	=> BILLING_DEFAULT_FREQ_TYPE,
				'BillingMethod'		=> $arrDetails ['Account']['BillingMethod'],		// (CONSTANT) post or email.
				'PaymentTerms'		=> PAYMENT_TERMS_DEFAULT,
				'Archived'			=> 0
			);
			
			$insAccount = new StatementInsert ('Account');
			$intAccount = $insAccount->Execute ($arrAccount);
			
			$actAccount = new Account ($intAccount);
			
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
