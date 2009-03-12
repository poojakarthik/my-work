<?php

/**
 * Version 160 of database update.
 * This version: -
 *
 *	1:	Add the Account.payment_method_id and direct_debit_id Fields
 *	2:	Add the account_history.payment_method_id and new_direct_debit_id Fields (new_direct_debit_id will be renamed in a later rollout)
 *
 *	3:	Convert CreditCard records to direct_debit + direct_debit_credit_card records
 *	4:	Convert DirectDebit records to direct_debit + direct_debit_bank_account records
 *
 *	5:	Convert Account.BillingType to Account.payment_method_id
 *		Convert Account.CreditCard/Account.DirectDebit to Account.direct_debit_id
 *
 *	6:	Convert account_history.billing_type to account_history.payment_method_id
 *		Convert account_history.credit_card_id/account_history.direct_debit_id to account_history.new_direct_debit_id
 */

class Flex_Rollout_Version_000160 extends Flex_Rollout_Version
{
	static public	$arrPaymentMethods	= null;
	static public	$arrCreditCardTypes	= null;
	
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		$arrAccounts			= array();
		$arrDirectDebitConvert	= array('CreditCard'=>array(), 'DirectDebit'=>array());
		
		Log::getLog()->log("Retrieving direct_debit_type Constants...");
		
		$arrDirectDebitTypes	= array();
		$resDirectDebitTypes = $dbAdmin->query("SELECT * FROM direct_debit_type WHERE 1");
		if (PEAR::isError($resDirectDebitTypes))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of direct_debit_type Constants. ' . $resDirectDebitTypes->getMessage() . " (DB Error: " . $resDirectDebitTypes->getUserInfo() . ")");
		}
		while ($arrDirectDebitType = $resDirectDebitTypes->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$arrDirectDebitTypes[$arrDirectDebitType['const_name']]	= $arrDirectDebitType;
		}
		
		Log::getLog()->log("Retrieving status Constants...");
		
		$arrStatuses	= array();
		$resStatuses = $dbAdmin->query("SELECT * FROM status WHERE 1");
		if (PEAR::isError($resStatuses))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of status Constants. ' . $resStatuses->getMessage() . " (DB Error: " . $resStatuses->getUserInfo() . ")");
		}
		while ($arrStatus = $resStatuses->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$arrStatuses[$arrStatus['const_name']]	= $arrStatus;
		}
		
		Log::getLog()->log("Adding Account Fields...");
		
		// 1:	Add the Account.payment_method_id and direct_debit_id Fields
		$strSQL =	"ALTER TABLE Account " .
					"ADD payment_method_id	BIGINT(20)		UNSIGNED	NOT NULL	DEFAULT 1	COMMENT '(FK) Account\'s Payment Method', " .
					"ADD direct_debit_id	BIGINT(20)		UNSIGNED	NULL					COMMENT '(FK) Current Direct Debit Details', " .
					" " .
					"ADD CONSTRAINT fk_account_payment_method_id	FOREIGN KEY (payment_method_id)	REFERENCES payment_method(id)		ON UPDATE CASCADE ON DELETE RESTRICT, " .
					"ADD CONSTRAINT fk_account_direct_debit_id		FOREIGN KEY (direct_debit_id)	REFERENCES direct_debit(id)	ON UPDATE CASCADE ON DELETE SET NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Account.payment_method_id and direct_debit_id Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE Account " .
								"DROP FOREIGN KEY fk_account_direct_debit_id, " .
								"DROP FOREIGN KEY fk_account_payment_method_id, " .
								"DROP direct_debit_id, " .
								"DROP payment_method_id;";
								
		Log::getLog()->log("Adding account_history Fields...");
		
		// 2:	Add the account_history.payment_method_id and new_direct_debit_id Fields
		$strSQL =	"ALTER TABLE account_history " .
					"ADD payment_method_id		BIGINT(20)		UNSIGNED	NOT NULL	DEFAULT 1	COMMENT '(FK) Account\'s Payment Method', " .
					"ADD new_direct_debit_id	BIGINT(20)		UNSIGNED	NULL					COMMENT '(FK) Direct Debit Method Details', " .
					" " .
					"ADD CONSTRAINT fk_account_history_payment_method_id	FOREIGN KEY (payment_method_id)		REFERENCES payment_method(id)	ON UPDATE CASCADE ON DELETE RESTRICT, " .
					"ADD CONSTRAINT fk_account_history_direct_debit_id		FOREIGN KEY (new_direct_debit_id)	REFERENCES direct_debit(id)		ON UPDATE CASCADE ON DELETE SET NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_history.payment_method_id and new_direct_debit_id Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE account_history " .
								"DROP FOREIGN KEY fk_account_history_direct_debit_id, " .
								"DROP FOREIGN KEY fk_account_history_payment_method_id, " .
								"DROP new_direct_debit_id, " .
								"DROP payment_method_id;";
		
		//--------------------------------------------------------------------//
		// Retrieve a list of CreditCard records
		//--------------------------------------------------------------------//
		Log::getLog()->log("Retrieving list of Credit Cards...");
		$strCreditCardSQL	=	"SELECT CreditCard.*, Account.Id AS account_id FROM CreditCard JOIN Account USING (AccountGroup) WHERE 1;";
		$resCreditCards		= $dbAdmin->query($strCreditCardSQL);
		if (PEAR::isError($resCreditCards))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Credit Cards. ' . $resCreditCards->getMessage() . " (DB Error: " . $resCreditCards->getUserInfo() . ")");
		}
		Log::getLog()->log("Converting Credit Cards...");
		while ($arrCreditCard = $resCreditCards->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// 3:	Convert CreditCard records to direct_debit + direct_debit_credit_card records
			$objDirectDebit							= new Direct_Debit();
			$objDirectDebit->account_id				= (int)$arrCreditCard['account_id'];
			$objDirectDebit->direct_debit_type_id	= (int)$arrDirectDebitTypes['DIRECT_DEBIT_TYPE_CREDIT_CARD']['id'];
			$objDirectDebit->created_employee_id	= ((int)$arrCreditCard['employee_id']) ? (int)$arrCreditCard['employee_id'] : Employee::SYSTEM_EMPLOYEE_ID;
			$objDirectDebit->created_on				= $arrCreditCard['created_on'];
			$objDirectDebit->modified_employee_id	= $objDirectDebit->created_employee_id;
			$objDirectDebit->modified_on			= $arrCreditCard['created_on'];
			$objDirectDebit->status_id				= ((int)$arrCreditCard['Archived']) ? (int)$arrStatuses['STATUS_INACTIVE']['id'] : (int)$arrStatuses['STATUS_ACTIVE']['id'];
			$objDirectDebit->save();
			
			$objCreditCard						= new Direct_Debit_Credit_Card();
			$objCreditCard->direct_debit_id		= $objDirectDebit->id;
			$objCreditCard->credit_card_type_id	= self::_convertCreditCardType((int)$arrCreditCard['CardType']);
			$objCreditCard->card_name			= $arrCreditCard['Name'];
			$objCreditCard->card_number			= $arrCreditCard['CardNumber'];
			$objCreditCard->expiry_month		= (int)$arrCreditCard['ExpMonth'];
			if (strlen(trim($arrCreditCard['ExpYear'])) === 4)
			{
				$objCreditCard->expiry_year		= (int)$arrCreditCard['ExpYear'];
			}
			else
			{
				$objCreditCard->expiry_year		= 2000 + (int)$arrCreditCard['ExpYear'];
			}
			$objCreditCard->cvv					= $arrCreditCard['Name'];
			$objCreditCard->save();
			
			// Add to CreditCard.id=>direct_debit.id conversion array
			$arrDirectDebitConvert['CreditCard'][(int)$arrCreditCard['Id']]	= $objDirectDebit->id;
		}
		
		//--------------------------------------------------------------------//
		// Retrieve a list of DirectDebit records
		//--------------------------------------------------------------------//
		Log::getLog()->log("Retrieving list of Direct Debits...");
		$strDirectDebitSQL	=	"SELECT DirectDebit.*, Account.Id AS account_id FROM DirectDebit JOIN Account USING (AccountGroup) WHERE 1;";
		$resDirectDebits	= $dbAdmin->query($strDirectDebitSQL);
		if (PEAR::isError($resDirectDebits))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Direct Debits. ' . $resDirectDebits->getMessage() . " (DB Error: " . $resDirectDebits->getUserInfo() . ")");
		}
		Log::getLog()->log("Converting Direct Debits...");
		while ($arrDirectDebit = $resDirectDebits->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// 3:	Convert CreditCard records to direct_debit + direct_debit_credit_card records
			$objDirectDebit							= new Direct_Debit();
			$objDirectDebit->account_id				= (int)$arrDirectDebit['account_id'];
			$objDirectDebit->direct_debit_type_id	= (int)$arrDirectDebitTypes['DIRECT_DEBIT_TYPE_BANK_ACCOUNT']['id'];
			$objDirectDebit->created_employee_id	= ((int)$arrDirectDebit['employee_id']) ? (int)$arrDirectDebit['employee_id'] : Employee::SYSTEM_EMPLOYEE_ID;
			$objDirectDebit->created_on				= $arrDirectDebit['created_on'];
			$objDirectDebit->modified_employee_id	= $objDirectDebit->created_employee_id;
			$objDirectDebit->modified_on			= $arrDirectDebit['created_on'];
			$objDirectDebit->status_id				= ((int)$arrDirectDebit['Archived']) ? (int)$arrStatuses['STATUS_INACTIVE']['id'] : (int)$arrStatuses['STATUS_ACTIVE']['id'];
			$objDirectDebit->save();
			
			$objBankAccount						= new Direct_Debit_Bank_Account();
			$objBankAccount->direct_debit_id	= $objDirectDebit->id;
			$objBankAccount->bank_name			= $arrDirectDebit['BankName'];
			$objBankAccount->bank_bsb			= $arrDirectDebit['BSB'];
			$objBankAccount->account_number		= $arrDirectDebit['AccountNumber'];
			$objBankAccount->account_name		= $arrDirectDebit['AccountName'];
			$objBankAccount->save();
			
			// Add to CreditCard.id=>direct_debit.id conversion array
			$arrDirectDebitConvert['DirectDebit'][(int)$arrDirectDebit['Id']]	= $objDirectDebit->id;
		}

		//--------------------------------------------------------------------//
		// Retrieve a list of Accounts
		//--------------------------------------------------------------------//
		Log::getLog()->log("Retrieving list of Accounts...");
		$strAccountSQL	=	"SELECT Id, BillingType, CreditCard, DirectDebit FROM Account WHERE 1;";
		$resAccounts	= $dbAdmin->query($strAccountSQL);
		if (PEAR::isError($resAccounts))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Accounts. ' . $resAccounts->getMessage() . " (DB Error: " . $resAccounts->getUserInfo() . ")");
		}
		Log::getLog()->log("Converting Accounts...");
		while ($arrAccount = $resAccounts->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// 5:	Convert Account.BillingType to Account.payment_method_id
			//		Convert Account.CreditCard/Account.DirectDebit to Account.direct_debit_id
			$arrAccount['payment_method_id']	= self::_convertBillingTypeToPaymentMethod((int)$arrAccount['BillingType']);
			switch ($arrAccount['BillingType'])
			{
				case 1:	// Direct Debit
					$arrAccount['direct_debit_id']	= $arrDirectDebitConvert['DirectDebit'][(int)$arrAccount['DirectDebit']];
					break;
					
				case 2:	// Credit Card
					$arrAccount['direct_debit_id']	= $arrDirectDebitConvert['CreditCard'][(int)$arrAccount['CreditCard']];
					break;
				
				default:
					$arrAccount['direct_debit_id']	= 'NULL';
					break;
			}
			
			// Save the Account back to the DB
			$strAccountSaveSQL	=	"UPDATE Account " .
									"SET " .
									"	direct_debit_id	= {$arrAccount['direct_debit_id']}, " .
									"	payment_method_id	= {$arrAccount['payment_method_id']} " .
									"WHERE Id = {$arrAccount['Id']};";
			$resAccountSave		= $dbAdmin->query($strAccountSaveSQL);
			if (PEAR::isError($resAccountSave))
			{
				throw new Exception(__CLASS__ . ' Failed to convert Direct Debit details for Account #'.$arrAccount['Id'].'. ' . $resAccountSave->getMessage() . " (DB Error: " . $resAccountSave->getUserInfo() . ")");
			}
			
			$arrAccounts[(int)$arrAccount['id']]	= $arrAccount;
		}
		
		//--------------------------------------------------------------------//
		// Retrieve a list of account_history Records
		//--------------------------------------------------------------------//
		Log::getLog()->log("Retrieving list of account_history Records...");
		$strAccountHistorySQL	=	"SELECT id, billing_type, credit_card_id, direct_debit_id FROM account_history WHERE 1;";
		$resAccountHistories	= $dbAdmin->query($strAccountHistorySQL);
		if (PEAR::isError($resAccountHistories))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of account_history Records. ' . $resAccountHistories->getMessage() . " (DB Error: " . $resAccountHistories->getUserInfo() . ")");
		}
		Log::getLog()->log("Converting account_history Records...");
		while ($arrAccountHistory = $resAccountHistories->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// 6:	Convert account_history.billing_type to account_history.payment_method_id
			//		Convert account_history.credit_card_id/account_history.direct_debit_id to account_history.new_direct_debit_id
			$arrAccountHistory['payment_method_id']	= self::_convertBillingTypeToPaymentMethod((int)$arrAccountHistory['billing_type']);
			switch ($arrAccountHistory['BillingType'])
			{
				case 1:	// Direct Debit
					$arrAccountHistory['new_direct_debit_id']	= $arrDirectDebitConvert['DirectDebit'][$arrAccountHistory['direct_debit_id']];
					break;
					
				case 2:	// Credit Card
					$arrAccountHistory['new_direct_debit_id']	= $arrDirectDebitConvert['CreditCard'][$arrAccountHistory['credit_card_id']];
					break;
				
				default:
					$arrAccountHistory['new_direct_debit_id']	= 'NULL';
					break;
			}
			
			// Save the Account History record back to the DB
			$strAccountHistorySaveSQL	=	"UPDATE account_history " .
									"SET " .
									"	new_direct_debit_id	= {$arrAccountHistory['new_direct_debit_id']}, " .
									"	payment_method_id	= {$arrAccountHistory['payment_method_id']} " .
									"WHERE id = {$arrAccountHistory['id']};";
			$resAccountHistorySave		= $dbAdmin->query($strAccountHistorySaveSQL);
			if (PEAR::isError($resAccountHistorySave))
			{
				throw new Exception(__CLASS__ . ' Failed to convert Direct Debit details for account_history #'.$arrAccountHistory['Id'].'. ' . $resAccountHistorySave->getMessage() . " (DB Error: " . $resAccountHistorySave->getUserInfo() . ")");
			}
			
			$arrAccountHistories[(int)$arrAccountHistory['id']]	= $arrAccountHistory;
		}
		
		// TEST MODE
		//throw new Exception("TEST MODE");
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
	
	private static function _convertBillingTypeToPaymentMethod($intBillingType)
	{
		static	$dbAdmin;
		$dbAdmin	= (isset($dbAdmin)) ? $dbAdmin : Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		if (self::$arrPaymentMethods === null)
		{
			self::$arrPaymentMethods	= array();
			
			// Load the Payment Methods
			$result = $dbAdmin->query("SELECT * FROM payment_method WHERE 1");
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to retrieve the payment_method Constants. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			while ($arrPaymentMethod = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				self::$arrPaymentMethods[$arrPaymentMethod['const_name']]	= $arrPaymentMethod;
			}
		}
		
		switch ($intBillingType)
		{
			case 1:	// Direct Debit
			case 2:	// Credit Card
				return (int)self::$arrPaymentMethods['PAYMENT_METHOD_DIRECT_DEBIT']['id'];
				break;
			
			case 3:	// Account
				return (int)self::$arrPaymentMethods['PAYMENT_METHOD_ACCOUNT']['id'];
				break;
				
			default:
				throw new Exception("Unable to convert BillingType of '{$intBillingType}' to payment_method equivalent");
				break;
		}
	}
	
	private static function _convertCreditCardType($intCreditCardType)
	{
		static	$dbAdmin;
		$dbAdmin	= (isset($dbAdmin)) ? $dbAdmin : Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		if (self::$arrCreditCardTypes === null)
		{
			self::$arrCreditCardTypes	= array();
			
			// Load the Payment Methods
			$result = $dbAdmin->query("SELECT * FROM credit_card_type WHERE 1");
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to retrieve the credit_card_type Constants. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			while ($arrCreditCardType = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				self::$arrCreditCardTypes[$arrCreditCardType['const_name']]	= $arrCreditCardType;
			}
		}
		
		switch ($intCreditCardType)
		{
			case 1:	// VISA
				return (int)self::$arrPaymentMethods['CREDIT_CARD_TYPE_VISA']['id'];
				break;
				
			case 2:	// MasterCard
				return (int)self::$arrPaymentMethods['CREDIT_CARD_TYPE_MASTERCARD']['id'];
				break;
			
			case 4:	// AMEX
				return (int)self::$arrPaymentMethods['CREDIT_CARD_TYPE_AMEX']['id'];
				break;
			
			case 5:	// Diners
				return (int)self::$arrPaymentMethods['CREDIT_CARD_TYPE_DINERS']['id'];
				break;
				
			default:
				throw new Exception("Unable to convert CreditCard type of '{$intCreditCardType}' to credit_card_type equivalent");
				break;
		}
	}
}

?>