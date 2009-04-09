<?php

/**
 * Version 165 of database update.
 * This version: -
 *
 *	1:	Conversion of Employees
 *	2:	Conversion of Dealers
 *	3:	Conversion of Accounts
 *	4:	Conversion of Contacts
 */

class Flex_Rollout_Version_000165 extends Flex_Rollout_Version
{
	const			MONTH_CONVERSION		= 28;						// 1 month = today + 28 days
	const			SAMPLE_MONTH_CONVERSION	= 31;						// 1 month = today + 31 days
	
	static public	$arrPaymentMethods	= null;
	static public	$arrCreditCardTypes	= null;
	
	private			$_arrEmployeeToPerson			= array();
	private			$_arrDealerToPerson				= array();
	private			$_arrContactToPerson			= array();
	private			$_arrTicketingContactToPerson	= array();
	
	private			$_cgAccountType;
	private			$_cgStatus;
	private			$_cgPersonGroup;
	private			$_cgAddressType;
	
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		// Register Constant Groups if they don't already exist
		$this->_cgAccountType	= Constant_Group::loadFromTable($dbAdmin	, 'account_type'	, false	, false	, true);
		$this->_cgStatus		= Constant_Group::loadFromTable($dbAdmin	, 'status'			, false	, false	, true);
		$this->_cgPersonGroup	= Constant_Group::loadFromTable($dbAdmin	, 'person_group'	, false	, false	, true);
		$this->_cgAddressType	= Constant_Group::loadFromTable($dbAdmin	, 'address_type'	, false	, false	, true);
		
		// Convert Employees to Person-Employees
		$resEmployee = $dbAdmin->query("SELECT * FROM Employee WHERE 1");
		if (PEAR::isError($resEmployee))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Old Employees. ' . $resEmployee->getMessage() . " (DB Error: " . $resEmployee->getUserInfo() . ")");
		}
		while ($arrOldEmployee = $resEmployee->fetchRow())
		{
			$this->_convertEmployee($arrOldEmployee);
		}
		
		// Convert Dealers to Person-Dealers
		$resDealer = $dbAdmin->query("SELECT * FROM dealer WHERE 1");
		if (PEAR::isError($resDealer))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Old Dealers. ' . $resDealer->getMessage() . " (DB Error: " . $resDealer->getUserInfo() . ")");
		}
		while ($arrOldDealer = $resDealer->fetchRow())
		{
			$this->_convertDealer($arrOldDealer);
		}
		
		// Convert Accounts
		$resAccount	= $dbAdmin->query("SELECT * FROM Account WHERE 1");
		if (PEAR::isError($resAccount))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Old Accounts. ' . $resAccount->getMessage() . " (DB Error: " . $resAccount->getUserInfo() . ")");
		}
		while ($arrOldAccount = $resAccount->fetchRow())
		{
			$this->_convertAccount($arrOldAccount);
		}
		
		// Convert Contacts
		$resContact	= $dbAdmin->query("SELECT Contact.*, Account.Id AS account_id, Account.PrimaryContact FROM Contact LEFT JOIN Account ON (Account.PrimaryContact = Contact.Id OR (Account.AccountGroup = Contact.AccountGroup AND Contact.CustomerContact = 1)) WHERE 1");
		if (PEAR::isError($resContact))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Old Contacts. ' . $resContact->getMessage() . " (DB Error: " . $resContact->getUserInfo() . ")");
		}
		while ($arrOldContact = $resContact->fetchRow())
		{
			$this->_convertContact($arrOldContact);
		}
		
		// Convert Telemarketing Contacts
		$resTelemarketingContact	= $dbAdmin->query("SELECT telemarketing_contact.*, telemarketing_contact_account.account_id FROM telemarketing_contact LEFT JOIN telemarketing_contact_account ON telemarketing_contact.id = telemarketing_contact_account.telemarketing_contact_id WHERE 1");
		if (PEAR::isError($resTelemarketingContact))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Old Telenmarketing Contacts. ' . $resTelemarketingContact->getMessage() . " (DB Error: " . $resTelemarketingContact->getUserInfo() . ")");
		}
		while ($arrOldTelemarketingContact = $resTelemarketingContact->fetchRow())
		{
			$this->_convertTelemarketingContact($arrOldTelemarketingContact);
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
	
	private function _convertAccount($arrOldAccount)
	{
		$arrNewAccount								= array();
		$arrNewAccount['id']						= (int)$arrOldAccount['Id'];
		$arrNewAccount['customer_group_id']			= (int)$arrOldAccount['CustomerGroup'];
		$arrNewAccount['account_type_id']			= (trim($arrOldAccount['ABN']) || trim($arrOldAccount['ACN'])) ? $this->_cgAccountType->getValue('ACCOUNT_TYPE_BUSINESS') : $this->_cgAccountType->getValue('ACCOUNT_TYPE_RESIDENTIAL');
		$arrNewAccount['account_name']				= $arrOldAccount['BusinessName'];
		$arrNewAccount['trading_name']				= $arrOldAccount['TradingName'];
		$arrNewAccount['abn']						= str_replace(' ', '', $arrOldAccount['ABN']);
		$arrNewAccount['acn']						= str_replace(' ', '', $arrOldAccount['ACN']);
		$arrNewAccount['payment_term']				= (int)$arrOldAccount['PaymentTerms'];
		$arrNewAccount['created_by_person_id']		= self::_getPersonForEmployeeId((int)$arrOldAccount['Employee']);
		$arrNewAccount['created_on_timestamp']		= $arrOldAccount['CreatedOn'];
		$arrNewAccount['waive_payment_method_fee']	= (int)$arrOldAccount['DisableDDR'];
		
		$intDisableLatePayment	= (int)$arrOldAccount['DisableLatePayment'];
		if ($intDisableLatePayment < 0)
		{
			// Calculate expiry date
			$arrNewAccount['late_payment_fee_amnesty']	= date("Y-m-d H:i:s", strtotime("+".Flex_Rollout_Version_000165::MONTH_CONVERSION." days", time()));
		}
		elseif ($intDisableLatePayment > 0)
		{
			// Set to End of Time
			$arrNewAccount['late_payment_fee_amnesty']	= Flex_Date::TIMESTAMP_END_OF_TIME;
		}
		else
		{
			// Set to Beginning of Time
			$arrNewAccount['late_payment_fee_amnesty']	= Flex_Date::TIMESTAMP_START_OF_TIME;
		}
		
		if ($arrOldAccount['LatePaymentAmnesty'] === '9999-12-31')
		{
			// Set to End of Time
			$arrNewAccount['late_notice_amnesty']	= Flex_Date::TIMESTAMP_END_OF_TIME;
		}
		elseif (!$arrOldAccount['LatePaymentAmnesty'])
		{
			// Set to End of Time
			$arrNewAccount['late_notice_amnesty']	= Flex_Date::TIMESTAMP_START_OF_TIME;
		}
		else
		{
			// Set to Beginning of Time
			$arrNewAccount['late_notice_amnesty']	= $arrOldAccount['LatePaymentAmnesty'];
		}
		
		$intSample	= (int)$arrOldAccount['Sample'];
		if ($intSample < 0)
		{
			// Calculate expiry date
			$arrNewAccount['sample_until']	= date("Y-m-d H:i:s", strtotime("+".Flex_Rollout_Version_000165::SAMPLE_MONTH_CONVERSION." days", time()));
		}
		elseif ($intSample > 0)
		{
			// Set to End of Time
			$arrNewAccount['sample_until']	= Flex_Date::TIMESTAMP_END_OF_TIME;
		}
		else
		{
			// Set to Beginning of Time
			$arrNewAccount['sample_until']	= Flex_Date::TIMESTAMP_START_OF_TIME;
		}
		
		$arrNewAccount['credit_control_status_id']		= (int)$arrOldAccount['credit_control_status'];
		$arrNewAccount['automatic_barring_status_id']	= (int)$arrOldAccount['automatic_barring_status'];
		$arrNewAccount['automatic_barring_timestamp']	= $arrOldAccount['audomatic_barring_datetime'];
		$arrNewAccount['tio_reference_number']			= $arrOldAccount['tio_reference_number'];
		$arrNewAccount['is_vip']						= (int)$arrOldAccount['vip'];
		$arrNewAccount['delivery_method_id']			= (int)$arrOldAccount['delivery_method_id'];
		$arrNewAccount['payment_method_id']				= (int)$arrOldAccount['payment_method_id'];
		$arrNewAccount['direct_debit_id']				= (int)$arrOldAccount['direct_debit_id'];
		$arrNewAccount['is_telemarketing_blacklisted']	= 0;
		$arrNewAccount['dealer_person_id']				= null;
		$arrNewAccount['account_status_id']				= (int)$arrOldAccount['Archived'];
		
		$strInsertAccountSQL	=	"INSERT INTO person (id, customer_group_id, account_type_id, account_name, trading_name, abn, acn, payment_term, created_by_person_id, created_on_timestamp, waive_payment_method_fee, late_payment_fee_amnesty, late_notice_amnesty, sample_until, credit_control_status_id, automatic_barring_status_id, automatic_barring_timestamp, tio_reference_number, is_vip, delivery_method_id, payment_method_id, direct_debit_id, is_telemarketing_blacklisted, dealer_person_id, account_status_id) VALUES " .
								"(" .
								"	".$dbAdmin->quote($arrNewAccount['id']								, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['customer_group_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['account_type_id']					, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['account_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewAccount['trading_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewAccount['abn']								, 'text').", " .
								"	".$dbAdmin->quote($arrNewAccount['acn']								, 'text').", " .
								"	".$dbAdmin->quote($arrNewAccount['payment_term']					, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['created_by_person_id']			, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['created_on_timestamp']			, 'timestamp').", " .
								"	".$dbAdmin->quote($arrNewAccount['waive_payment_method_fee']		, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['late_payment_fee_amnesty']		, 'timestamp').", " .
								"	".$dbAdmin->quote($arrNewAccount['late_notice_amnesty']				, 'timestamp').", " .
								"	".$dbAdmin->quote($arrNewAccount['sample_until']					, 'timestamp').", " .
								"	".$dbAdmin->quote($arrNewAccount['credit_control_status_id']		, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['automatic_barring_status_id']		, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['automatic_barring_timestamp']		, 'timestamp').", " .
								"	".$dbAdmin->quote($arrNewAccount['tio_reference_number']			, 'text').", " .
								"	".$dbAdmin->quote($arrNewAccount['is_vip']							, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['delivery_method_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['payment_method_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['direct_debit_id']					, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['is_telemarketing_blacklisted']	, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['dealer_person_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewAccount['account_status_id']				, 'integer') .
								")";
		
		$resAccountInsert	= $dbAdmin->exec($strInsertAccountSQL);
		if (PEAR::isError($resAccountInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Account '.$arrOldAccount['Id'].'. ' . $resPersonInsert->getMessage() . " (DB Error: " . $resPersonInsert->getUserInfo() . ")");
		}
		$arrNewAccount['id']	= (int)$dbAdmin->lastInsertID();
		
		if ($arrNewAccount['id'] !== (int)$arrOldAccount['Id'])
		{
			throw new Exception(__CLASS__ . ' Failed to convert Account '.$arrOldAccount['Id'].' -- New and Old Account Ids do not match! (Old: '.((int)$arrOldAccount['Id']).'; New: '.$arrNewAccount['id'].'). ');
		}
		
		// Create Address for this Account (only if there is one already specified)
		if (trim($arrOldAccount['Suburb']))
		{
			if ($intAddressId = self::_createAddress($arrOldAccount['Address1'], $arrOldAccount['Address2'], $arrOldAccount['Suburb'], $arrOldAccount['Postcode'], $arrOldAccount['State'], $arrOldAccount['Country']))
			{
				$strInsertAddressSQL	=	"INSERT INTO account_address (account_id, address_id, address_type_id) VALUES " .
											"(".$dbAdmin->quote($arrNewAccount['id'], 'integer').", ".$dbAdmin->quote($intAddressId, 'integer').", ".$dbAdmin->quote($this->_cgAddresstype->getValue('ADDRESS_TYPE_POSTAL'), 'integer').")";
			}
			else
			{
				// Locality Matching Failed
				throw new Exception("Unable to convert Suburb of '{$arrOldAccount['Suburb']}' to a Locality for Account #{$arrOldAccount['Id']}!  Please manually edit this Account in Flex");
			}
		}
		else
		{
			$this->log("Skipping Address creation for Account #{$arrOldAccount['Id']}");
		}
		
		// Convert Account History
		$resAccountHistory	= $dbAdmin->query("SELECT * FROM account_history WHERE account_id = {$arrOldAccount['Id']}");
		if (PEAR::isError($resAccountHistory))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of Old account_history for Account #'.$arrOldAccount['Id'].'. ' . $resAccountHistory->getMessage() . " (DB Error: " . $resAccountHistory->getUserInfo() . ")");
		}
		while ($arrOldAccountHistory = $resAccountHistory->fetchRow())
		{
			$this->_convertAccountHistory($arrOldAccountHistory);
		}
	}
	
	private function _convertAccountHistory($arrOldAccountHistory)
	{
		$dbAdmin	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		$arrOldAccountHistory['person_id']				= (int)$this->_arrEmployeeToPerson[(int)$arrOldAccountHistory['employee_id']]['person_id'];
		
		$strUpdateAccountHistory	=	"UPDATE account_history " .
										"SET	person_id	= ".$dbAdmin->quote($arrOldAccountHistory['person_id']	, 'integer').", " .
										"WHERE	id			= ".$dbAdmin->quote($arrOldAccountHistory['id']			, 'integer').";";
		$resAccountHistoryUpdate	= $dbAdmin->exec($strUpdateAccountHistory);
		if (PEAR::isError($resAccountHistoryUpdate))
		{
			throw new Exception(__CLASS__ . ' Failed to convert account_history #'.$arrOldAccountHistory['id'].'. ' . $resAccountHistoryUpdate->getMessage() . " (DB Error: " . $resAccountHistoryUpdate->getUserInfo() . ")");
		}
		return true;
	}
	
	private function _convertEmployee($arrOldEmployee)
	{
		$dbAdmin	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// Person Details
		$arrNewPerson									= array();
		$arrNewPerson['salutation_id']					= null;
		$arrNewPerson['first_name']						= $arrOldEmployee['FirstName'];
		$arrNewPerson['middle_names']					= null;
		$arrNewPerson['last_name']						= $arrOldEmployee['LastName'];
		$arrNewPerson['username']						= $arrOldEmployee['UserName'];
		$arrNewPerson['password']						= $arrOldEmployee['PassWord'];
		$arrNewPerson['date_of_birth']					= $arrOldEmployee['DOB'];
		$arrNewPerson['is_telemarketing_blacklisted']	= 0;
		$arrNewPerson['status_id']						= ((int)$arrOldEmployee['Archived']) ? $this->_cgStatus->getValue('STATUS_INACTIVE') : $this->_cgStatus->getValue('STATUS_ACTIVE');
		
		$strInsertPersonSQL	=	"INSERT INTO person (salutation_id, first_name, middle_names, last_name, username, password, date_of_birth, is_telemarketing_blacklisted, status_id) VALUES " .
								"(" .
								"	".$dbAdmin->quote($arrNewPerson['salutation_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewPerson['first_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['middle_names']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['last_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['username']						, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['password']						, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['date_of_birth']				, 'date').", " .
								"	".$dbAdmin->quote($arrNewPerson['is_telemarketing_blacklisted']	, 'integer').", " .
								"	".$dbAdmin->quote($arrNewPerson['status_id']					, 'integer') .
								")";
		
		$resPersonInsert	= $dbAdmin->exec($strInsertPersonSQL);
		if (PEAR::isError($resPersonInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Employee '.$arrOldEmployee['Id'].' to a person. ' . $resPersonInsert->getMessage() . " (DB Error: " . $resPersonInsert->getUserInfo() . ")");
		}
		$arrNewPerson['id']	= (int)$dbAdmin->lastInsertID();
		
		// Person-Employee Details
		$arrNewPersonEmployee							= array();
		$arrNewPersonEmployee['person_id']				= $arrNewPerson['id'];
		$arrNewPersonEmployee['employee_role_id']		= (int)$arrOldEmployee['user_role_id'];
		
		$strInsertPersonEmployeeSQL	=	"INSERT INTO person_employee (person_id, employee_role_id) VALUES " .
										"(" .
										"	".$dbAdmin->quote($arrNewPersonEmployee['person_id']		, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonEmployee['employee_role_id']	, 'integer') .
										")";
		
		$resPersonEmployeeInsert	= $dbAdmin->exec($strInsertPersonEmployeeSQL);
		if (PEAR::isError($resPersonEmployeeInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Employee '.$arrOldEmployee['Id'].' to a person_employee. ' . $resPersonEmployeeInsert->getMessage() . " (DB Error: " . $resPersonEmployeeInsert->getUserInfo() . ")");
		}
		$arrNewPersonEmployee['id']	= (int)$dbAdmin->lastInsertID();
		
		// Add to Employee Person-Group
		$resPersonGroupEmployeeInsert	= $dbAdmin->exec("INSERT INTO person_person_group (person_id, person_group_id) VALUES ({$arrNewPerson['id']}, ".$this->_cgPersonGroup->getValue('PERSON_GROUP_EMPLOYEE').");");
		if (PEAR::isError($resPersonGroupEmployeeInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to add Employee '.$arrOldEmployee['Id'].' to the Employee person_group. ' . $resPersonGroupEmployeeInsert->getMessage() . " (DB Error: " . $resPersonGroupEmployeeInsert->getUserInfo() . ")");
		}
		
		$this->_arrEmployeeToPerson[$arrOldEmployee['Id']]	= array
															(
																'person_id'				=> $arrNewPerson['id'],
																'person_employee_id'	=> $arrNewPersonEmployee['id']
															);
	}
	
	private function _convertDealer($arrOldDealer)
	{
		$arrNewPerson	= array();
		if (array_key_exists((int)$arrOldDealer['employee_id'], $this->_arrEmployeeToPerson))
		{
			// Extend the Employee Person
			$arrNewPerson['id']	= $this->_arrEmployeeToPerson[(int)$arrOldDealer['employee_id']]['person_id'];
		}
		else
		{
			// Create a new Person
			// Person Details
			$arrNewPerson['salutation_id']					= null;
			$arrNewPerson['first_name']						= $arrOldDealer['first_name'];
			$arrNewPerson['middle_names']					= null;
			$arrNewPerson['last_name']						= $arrOldDealer['last_name'];
			$arrNewPerson['username']						= $arrOldDealer['username'];
			$arrNewPerson['password']						= $arrOldDealer['password'];
			$arrNewPerson['date_of_birth']					= null;
			$arrNewPerson['is_telemarketing_blacklisted']	= 0;
			$arrNewPerson['status_id']						= $this->_cgStatus->getValue('STATUS_ACTIVE');
			
			$strInsertPersonSQL	=	"INSERT INTO person (salutation_id, first_name, middle_names, last_name, username, password, date_of_birth, is_telemarketing_blacklisted, status_id) VALUES " .
									"(" .
									"	".$dbAdmin->quote($arrNewPerson['salutation_id']				, 'integer').", " .
									"	".$dbAdmin->quote($arrNewPerson['first_name']					, 'text').", " .
									"	".$dbAdmin->quote($arrNewPerson['middle_names']					, 'text').", " .
									"	".$dbAdmin->quote($arrNewPerson['last_name']					, 'text').", " .
									"	".$dbAdmin->quote($arrNewPerson['username']						, 'text').", " .
									"	".$dbAdmin->quote($arrNewPerson['password']						, 'text').", " .
									"	".$dbAdmin->quote($arrNewPerson['date_of_birth']				, 'date').", " .
									"	".$dbAdmin->quote($arrNewPerson['is_telemarketing_blacklisted']	, 'integer').", " .
									"	".$dbAdmin->quote($arrNewPerson['status_id']					, 'integer') .
									")";
			
			$resPersonInsert	= $dbAdmin->exec($strInsertPersonSQL);
			if (PEAR::isError($resPersonInsert))
			{
				throw new Exception(__CLASS__ . ' Failed to convert Dealer '.$arrOldDealer['Id'].' to a person. ' . $resPersonInsert->getMessage() . " (DB Error: " . $resPersonInsert->getUserInfo() . ")");
			}
			$arrNewPerson['id']	= (int)$dbAdmin->lastInsertID();
		}
		
		// Person-Dealer Details
		$arrNewPersonDealer									= array();
		$arrNewPersonDealer['person_id']					= $arrNewPerson['id'];
		$arrNewPersonDealer['manager_person_id']			= null;
		$arrNewPersonDealer['can_verify']					= (int)$arrOldDealer['can_verify'];
		$arrNewPersonDealer['business_name']				= $arrOldDealer['business_name'];
		$arrNewPersonDealer['trading_name']					= $arrOldDealer['trading_name'];
		$arrNewPersonDealer['abn']							= $arrOldDealer['abn'];
		$arrNewPersonDealer['is_abn_registered']			= (int)$arrOldDealer['abn_registered'];
		$arrNewPersonDealer['bank_account_bsb']				= $arrOldDealer['bank_account_bsb'];
		$arrNewPersonDealer['bank_account_number']			= $arrOldDealer['bank_account_number'];
		$arrNewPersonDealer['bank_account_name']			= $arrOldDealer['bank_account_name'];
		$arrNewPersonDealer['is_gst_registered']			= (int)$arrOldDealer['gst_registered'];
		$arrNewPersonDealer['termination_timestamp']		= $arrOldDealer['termination_date'];
		$arrNewPersonDealer['clawback_period']				= (int)$arrOldDealer['clawback_period'];
		$arrNewPersonDealer['carrier_id']					= (int)$arrOldDealer['carrier_id'];
		$arrNewPersonDealer['cascade_manager_restrictions']	= (int)$arrOldDealer['user_role_id'];
		
		$strInsertPersonDealerSQL	=	"INSERT INTO person_dealer (person_id, manager_person_id, can_verify, business_name, trading_name, abn, is_abn_registered, bank_account_bsb, bank_account_number, bank_account_name, is_gst_registered, termination_timestamp, clawback_period, carrier_id, cascade_manager_restrictions) VALUES " .
										"(" .
										"	".$dbAdmin->quote($arrNewPersonDealer['person_id']						, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['manager_person_id']				, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['can_verify']						, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['business_name']					, 'text').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['trading_name']					, 'text').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['abn']							, 'text').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['is_abn_registered']				, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['bank_account_bsb']				, 'text').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['bank_account_number']			, 'text').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['bank_account_name']				, 'text').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['is_gst_registered']				, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['termination_timestamp']			, 'timestamp').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['clawback_period']				, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['carrier_id']						, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonDealer['cascade_manager_restrictions']	, 'integer').
										")";
		
		$resPersonDealerInsert	= $dbAdmin->exec($strInsertPersonDealerSQL);
		if (PEAR::isError($resPersonDealerInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Dealer '.$arrOldDealer['Id'].' to a person_dealer. ' . $resPersonDealerInsert->getMessage() . " (DB Error: " . $resPersonDealerInsert->getUserInfo() . ")");
		}
		$arrNewPersonDealer['id']	= (int)$dbAdmin->lastInsertID();
		
		// Add to Dealer Person-Group
		$resPersonGroupDealerInsert	= $dbAdmin->exec("INSERT INTO person_person_group (person_id, person_group_id) VALUES ({$arrNewPerson['id']}, ".$this->_cgPersonGroup->getValue('PERSON_GROUP_DEALER').");");
		if (PEAR::isError($resPersonGroupDealerInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to add Dealer '.$arrOldDealer['Id'].' to the Dealer person_group. ' . $resPersonGroupDealerInsert->getMessage() . " (DB Error: " . $resPersonGroupDealerInsert->getUserInfo() . ")");
		}
		
		if ((int)$arrOldDealer['employee_id'])
		{
			$arrNewPerson['id']	= $this->_arrEmployeeToPerson[(int)$arrOldDealer['employee_id']]['person_dealer_id'];
		}
		
		$this->_arrDealerToPerson[$arrOldDealer['Id']]	= array
														(
															'person_id'				=> $arrNewPerson['id'],
															'person_dealer_id'		=> $arrNewPersonDealer['id']
														);
		
		// Create Address for this Dealer (only if there is one already specified)
		if (trim($arrOldDealer['suburb']))
		{
			if ($intAddressId = self::_createAddress($arrOldDealer['address_line_1'], $arrOldDealer['address_line_2'], $arrOldDealer['suburb'], $arrOldDealer['postcode'], $arrOldDealer['State'], $arrOldDealer['Country']))
			{
				$strInsertAddressSQL	=	"INSERT INTO account_address (account_id, address_id, address_type_id) VALUES " .
											"(".$dbAdmin->quote($arrNewPerson['id'], 'integer').", ".$dbAdmin->quote($intAddressId, 'integer').", ".$dbAdmin->quote($this->_cgAddresstype->getValue('ADDRESS_TYPE_POSTAL'), 'integer').")";
			}
			else
			{
				// Locality Matching Failed
				throw new Exception("Unable to convert Suburb of '{$arrOldAccount['Suburb']}' to a Locality for Account #{$arrOldAccount['Id']}!  Please manually edit this Account in Flex");
			}
		}
		else
		{
			$this->log("Skipping Address creation for Account #{$arrOldAccount['Id']}");
		}
	}
	
	private function _convertContact($arrOldContact)
	{
		$dbAdmin	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// Person Details
		$arrNewPerson									= array();
		$arrNewPerson['salutation_id']					= null;
		$arrNewPerson['first_name']						= $arrOldContact['FirstName'];
		$arrNewPerson['middle_names']					= null;
		$arrNewPerson['last_name']						= $arrOldContact['LastName'];
		$arrNewPerson['username']						= ((int)$arrOldContact['LastLogin']) ? trim($arrOldContact['Email']) : null;
		$arrNewPerson['password']						= ((int)$arrOldContact['LastLogin']) ? trim($arrOldContact['PassWord']) : null;
		$arrNewPerson['date_of_birth']					= $arrOldContact['DOB'];
		$arrNewPerson['is_telemarketing_blacklisted']	= 0;
		$arrNewPerson['status_id']						= ((int)$arrOldContact['Archived']) ? $this->_cgStatus->getValue('STATUS_INACTIVE') : $this->_cgStatus->getValue('STATUS_ACTIVE');
		
		$strInsertPersonSQL	=	"INSERT INTO person (salutation_id, first_name, middle_names, last_name, username, password, date_of_birth, is_telemarketing_blacklisted, status_id) VALUES " .
								"(" .
								"	".$dbAdmin->quote($arrNewPerson['salutation_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewPerson['first_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['middle_names']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['last_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['username']						, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['password']						, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['date_of_birth']				, 'date').", " .
								"	".$dbAdmin->quote($arrNewPerson['is_telemarketing_blacklisted']	, 'integer').", " .
								"	".$dbAdmin->quote($arrNewPerson['status_id']					, 'integer') .
								")";
		
		$resPersonInsert	= $dbAdmin->exec($strInsertPersonSQL);
		if (PEAR::isError($resPersonInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Contact '.$arrOldContact['Id'].' to a person. ' . $resPersonInsert->getMessage() . " (DB Error: " . $resPersonInsert->getUserInfo() . ")");
		}
		$arrNewPerson['id']	= (int)$dbAdmin->lastInsertID();
		
		// Person-Account Contact Details
		$arrNewPersonContactAccount							= array();
		$arrNewPersonContactAccount['person_id']				= $arrNewPerson['id'];
		$arrNewPersonContactAccount['position_title']			= $arrOldContact['JobTitle'];
		
		$strInsertPersonEmployeeSQL	=	"INSERT INTO person_contact_account (person_id, position_title) VALUES " .
										"(" .
										"	".$dbAdmin->quote($arrNewPersonContactAccount['person_id']		, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonContactAccount['position_title']	, 'text') .
										")";
		
		$resPersonContactAccountInsert	= $dbAdmin->exec($strInsertPersonEmployeeSQL);
		if (PEAR::isError($resPersonContactAccountInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Contact '.$arrOldContact['Id'].' to a person_contact_account. ' . $resPersonContactAccountInsert->getMessage() . " (DB Error: " . $resPersonContactAccountInsert->getUserInfo() . ")");
		}
		$arrNewPersonContactAccount['id']	= (int)$dbAdmin->lastInsertID();
		
		// Add to Account Contact Person-Group
		$resPersonGroupContactAccountInsert	= $dbAdmin->exec("INSERT INTO person_person_group (person_id, person_group_id) VALUES ({$arrNewPerson['id']}, ".$this->_cgPersonGroup->getValue('PERSON_GROUP_CONTACT_ACCOUNT').");");
		if (PEAR::isError($resPersonGroupContactAccountInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to add Contact '.$arrOldContact['Id'].' to the Account Contact person_group. ' . $resPersonGroupContactAccountInsert->getMessage() . " (DB Error: " . $resPersonGroupContactAccountInsert->getUserInfo() . ")");
		}
		
		$this->_arrContactToPerson[$arrOldContact['Id']]	= array
															(
																'person_id'					=> $arrNewPerson['id'],
																'person_contact_account_id'	=> $arrNewPersonContactAccount['id']
															);
	}
	
	private function _convertTicketingContact($arrOldTicketingContact)
	{
		$dbAdmin	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// Person Details
		$arrNewPerson									= array();
		$arrNewPerson['salutation_id']					= null;
		$arrNewPerson['first_name']						= $arrOldTicketingContact['first_name'];
		$arrNewPerson['middle_names']					= null;
		$arrNewPerson['last_name']						= $arrOldTicketingContact['last_name'];
		$arrNewPerson['username']						= null;
		$arrNewPerson['password']						= null;
		$arrNewPerson['date_of_birth']					= null;
		$arrNewPerson['is_telemarketing_blacklisted']	= 0;
		$arrNewPerson['status_id']						= self::_activeStatusToStatus($arrOldTicketingContact['status']);
		
		$strInsertPersonSQL	=	"INSERT INTO person (salutation_id, first_name, middle_names, last_name, username, password, date_of_birth, is_telemarketing_blacklisted, status_id) VALUES " .
								"(" .
								"	".$dbAdmin->quote($arrNewPerson['salutation_id']				, 'integer').", " .
								"	".$dbAdmin->quote($arrNewPerson['first_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['middle_names']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['last_name']					, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['username']						, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['password']						, 'text').", " .
								"	".$dbAdmin->quote($arrNewPerson['date_of_birth']				, 'date').", " .
								"	".$dbAdmin->quote($arrNewPerson['is_telemarketing_blacklisted']	, 'integer').", " .
								"	".$dbAdmin->quote($arrNewPerson['status_id']					, 'integer') .
								")";
		
		$resPersonInsert	= $dbAdmin->exec($strInsertPersonSQL);
		if (PEAR::isError($resPersonInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Contact '.$arrOldTicketingContact['Id'].' to a person. ' . $resPersonInsert->getMessage() . " (DB Error: " . $resPersonInsert->getUserInfo() . ")");
		}
		$arrNewPerson['id']	= (int)$dbAdmin->lastInsertID();
		
		// Person-Ticketing Contact Details
		$arrNewPersonContactTicketing							= array();
		$arrNewPersonContactTicketing['person_id']				= $arrNewPerson['id'];
		$arrNewPersonContactTicketing['auto_reply_enabled']		= self::_activeStatusToFlag($arrOldTicketingContact['auto_reply']);
		
		$strInsertPersonEmployeeSQL	=	"INSERT INTO person_contact_ticketing (person_id, position_title) VALUES " .
										"(" .
										"	".$dbAdmin->quote($arrNewPersonContactTicketing['person_id']			, 'integer').", " .
										"	".$dbAdmin->quote($arrNewPersonContactTicketing['auto_reply_enabled']	, 'integer') .
										")";
		
		$resPersonContactTicketingInsert	= $dbAdmin->exec($strInsertPersonEmployeeSQL);
		if (PEAR::isError($resPersonContactTicketingInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to convert Contact '.$arrOldTicketingContact['Id'].' to a person_contact_ticketing. ' . $resPersonContactTicketingInsert->getMessage() . " (DB Error: " . $resPersonContactTicketingInsert->getUserInfo() . ")");
		}
		$arrNewPersonContactTicketing['id']	= (int)$dbAdmin->lastInsertID();
		
		// Add to Ticketing Contact Person-Group
		$resPersonGroupContactTicketingInsert	= $dbAdmin->exec("INSERT INTO person_person_group (person_id, person_group_id) VALUES ({$arrNewPerson['id']}, ".$this->_cgPersonGroup->getValue('PERSON_GROUP_CONTACT_TICKETING').");");
		if (PEAR::isError($resPersonGroupContactTicketingInsert))
		{
			throw new Exception(__CLASS__ . ' Failed to add Contact '.$arrOldTicketingContact['Id'].' to the Account Contact person_group. ' . $resPersonGroupContactTicketingInsert->getMessage() . " (DB Error: " . $resPersonGroupContactTicketingInsert->getUserInfo() . ")");
		}
		
		$this->_arrTicketingContactToPerson[$arrOldTicketingContact['Id']]	= array
																			(
																				'person_id'						=> $arrNewPerson['id'],
																				'person_contact_ticketing_id'	=> $arrNewPersonContactTicketing['id']
																			);
	}
	
	private static function _activeStatusToStatus($intActiveStatusId)
	{
		static	$cgActiveStatus;
		static	$cgStatus;
		$cgActiveStatus	= ($cgActiveStatus)	? $cgActiveStatus	: Constant_Group::loadFromTable(Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)	, 'active_status'	, false	, false	, true);
		$cgStatus		= ($cgStatus)		? $cgStatus			: Constant_Group::loadFromTable(Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)	, 'status'			, false	, false	, true);
		
		switch ((int)$intActiveStatusId)
		{
			case $cgActiveStatus->getValue('ACTIVE_STATUS_ACTIVE'):
				return $cgStatus->getValue('STATUS_ACTIVE');
			
			case $cgActiveStatus->getValue('ACTIVE_STATUS_INACTIVE'):
				return $cgStatus->getValue('STATUS_INACTIVE');
			
			default:
				throw new Exception("Unknown Active Status with Id '{".((int)$intActiveStatusId)."}'");
		}
	}
	
	private static function _activeStatusToFlag($intActiveStatusId)
	{
		static	$cgActiveStatus;
		$cgActiveStatus	= ($cgActiveStatus)	? $cgActiveStatus	: Constant_Group::loadFromTable(Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)	, 'active_status'	, false	, false	, true);
		
		switch ((int)$intActiveStatusId)
		{
			case $cgActiveStatus->getValue('ACTIVE_STATUS_ACTIVE'):
				return 1;
			
			case $cgActiveStatus->getValue('ACTIVE_STATUS_INACTIVE'):
				return 0;
			
			default:
				throw new Exception("Unknown Active Status with Id '{".((int)$intActiveStatusId)."}'");
		}
	}
	
	private static function _createAddress($strAddressLine1, $strAddressLine2, $strLocality, $strPostcode, $mixState=null, $mixCountry=null)
	{
		static		$dbAdmin;
		$dbAdmin	= ($dbAdmin) ? $dbAdmin : Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// Find the Locality for this Address
		$strSelectSQL	= "SELECT id FROM address_locality WHERE UPPER(TRIM(name)) = '".strtoupper(trim($strLocality))."' AND postcode = '{$strPostcode}'";
		$resSelect		= $dbAdmin->query($strSelectSQL);
		if (PEAR::isError($resSelect))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve Locality information. ' . $resSelect->getMessage() . " (DB Error: " . $resSelect->getUserInfo() . ")");
		}
		elseif ($arrLocality = $resSelect->fetchRow())
		{
			// Use this Locality
			$strInsertSQL	=	"INSERT INTO address (address_line_1, address_line_2, address_locality_id) VALUES " .
								"(".$dbAdmin->quote($strAddressLine1, 'text').", ".$dbAdmin->quote($strAddressLine2, 'text').", ".$dbAdmin->quote((int)$arrLocality['id'], 'integer').");";
			$resInsert		= $dbAdmin->exec($strInsertSQL);
			if (PEAR::isError($resInsert))
			{
				throw new Exception(__CLASS__ . ' Failed to convert Address information. ' . $resInsert->getMessage() . " (DB Error: " . $resInsert->getUserInfo() . ")");
			}
			else
			{
				return (int)$dbAdmin->lastInsertID();
			}
		}
		elseif (!in_array(strtoupper($strCountry), array('AU', 'AUS')))
		{
			// Non-Australian address
		}
		else
		{
			// No match -- need to manually correct
			return null;
		}
	}
}

?>