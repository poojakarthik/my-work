<?php

class JSON_Handler_Contact extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	protected	$_aPermissions	= array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL);
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iContactId)
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_Contact_Exception('You do not have permission to view this contact.'));
			}
			
			// Get array of contact titles
			$aContactTitles			= Contact_Title::getAll();
			$aStdClassContactTitles	= array();
			
			foreach ($aContactTitles as $iId => $oTitle)
			{
				$aStdClassContactTitles[]	= $oTitle->name;
			}
			
			if (is_null($iContactId))
			{
				// Just return the titles
				return 	array(
							"Success"			=> true,
							"aContactTitles"	=> $aStdClassContactTitles,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Get the contact info
				$oContact			= Contact::getForId($iContactId);
				$oStdClassContact	= $oContact->toStdClass();
				
				// Split DOB
				$aDOB							= explode( '-', $oContact->DOB );
				$oStdClassContact->dob_day		= (int)$aDOB[2];
				$oStdClassContact->dob_month	= (int)$aDOB[1];
				$oStdClassContact->dob_year		= (int)$aDOB[0];
				
				return 	array(
							"Success"			=> true,
							"oContact"			=> $oStdClassContact,
							"aContactTitles"	=> $aStdClassContactTitles,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Contact_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function save($oContactDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'There was an error accessing the database' : '',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_aPermissions))
			{
				throw(new JSON_Handler_Contact_Exception('You do not have permission to save contact details.'));
			}
			
			// Create a Contact object
			if ($oContactDetails->iId)
			{
				// Details have id, must be an update
				$oContact	= Contact::getForId($oContactDetails->iId);
			}
			else
			{
				// No id given, must be a new object
				$oContact	= new Contact();
				
				// The following fields are not supplied by the interface, defaults are set here
				$oContact->SessionExpire	= '0000-00-00';
				$oContact->SessionId		= '';
			}
			
			// Validate oContactDetails
			$aValidationErrors = array();
			
			// Check each required field and each fields value
			if (!isset($oContactDetails->sFirstName) || $oContactDetails->sFirstName == '')
			{
				$aValidationErrors[]	= 'First Name missing';
			}
			
			if (!isset($oContactDetails->sLastName) || $oContactDetails->sLastName == '')
			{
				$aValidationErrors[]	= 'Last Name missing';
			}
			
			if (!is_numeric($oContactDetails->iDOBDay) || !is_numeric($oContactDetails->iDOBMonth) || !is_numeric($oContactDetails->iDOBYear))
			{
				$aValidationErrors[]	= 'Incomplete Date of Birth';
			}
			
			if (!isset($oContactDetails->sEmail) || $oContactDetails->sEmail == '')
			{
				$aValidationErrors[]	= 'Email address missing';
			}
			else
			{
				$aContactsWithEmail	= Contact::getForEmailAddress($oContactDetails->sEmail);
				
				// Remove self from the array
				if ($oContact->Id && array_key_exists($oContact->Id, $aContactsWithEmail))
				{
					unset($aContactsWithEmail[$oContact->Id]);
				}
				
				// Are there any instances remaining?
				if (count($aContactsWithEmail))
				{
					$aValidationErrors[]	= "Email address is already in use";
				}
			}
			
			if (!is_numeric($oContactDetails->iPhone) && !is_numeric($oContactDetails->iMobile))
			{
				$aValidationErrors[]	= 'Phone number and mobile number missing, one of them is required.';
			}
			else
			{
				if (is_numeric($oContactDetails->iPhone) && !PhoneNumberValid($oContactDetails->iPhone))
				{
					$aValidationErrors[]	= 'Invalid phone number';
				}
				
				if (is_numeric($oContactDetails->iMobile) && !PhoneNumberValid($oContactDetails->iMobile))
				{
					$aValidationErrors[]	= 'Invalid mobile number';
				}
			}
			
			if (is_numeric($oContactDetails->iFax) && !PhoneNumberValid($oContactDetails->iFax))
			{
				$aValidationErrors[]	= 'Invalid fax number';
			}
			
			if (!is_numeric($oContactDetails->iCustomerContact))
			{
				$aValidationErrors[]	= 'Account Access missing';
			}
			
			if (count($aValidationErrors) > 0)
			{
				// Validation errors found, rollback transaction and return errors
				$oDataAccess->TransactionRollback();
				
				return array(
							"Success"			=> false,
							"aValidationErrors"	=> $aValidationErrors,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Create an account object to the get the account group
				$oAccount	= Account::getForId($oContactDetails->iAccount);
				
				// Update orm object
				$oContact->AccountGroup		= $oAccount->AccountGroup;
				$oContact->Account			= $oContactDetails->iAccount;
				$oContact->Title			= $oContactDetails->sTitle;
				$oContact->FirstName		= $oContactDetails->sFirstName;
				$oContact->LastName			= $oContactDetails->sLastName;
				$oContact->JobTitle			= $oContactDetails->sJobTitle;
				$oContact->DOB				= "{$oContactDetails->iDOBYear}-{$oContactDetails->iDOBMonth}-{$oContactDetails->iDOBDay}";
				$oContact->Email			= $oContactDetails->sEmail;
				$oContact->Phone			= $oContactDetails->iPhone;
				$oContact->Mobile			= $oContactDetails->iMobile;
				$oContact->Fax				= $oContactDetails->iFax;
				$oContact->CustomerContact	= $oContactDetails->iCustomerContact;
				$oContact->Archived			= $oContactDetails->iArchived;
				
				if (isset($oContactDetails->sPassword) && $oContactDetails->sPassword !== '')
				{
					$oContact->PassWord		= sha1($oContactDetails->sPassword);
				}
				
				$oContact->save();
				
				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"	=> true,
							"oContact"	=> $oContact->toStdClass(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Contact_Exception $oException)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getLinkedContactsForAccount($iAccountId) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			$oSourceAccount = Account::getForId($iAccountId);
			$oAccountGroup	= Account_Group::getForId($oSourceAccount->AccountGroup);
			$aAccounts		= $oAccountGroup->getAccounts(true);
			$aStdContacts	= array();
			foreach($aAccounts as $iId => $oAccount) {
				$aContacts = $oAccount->getContacts(true);
				foreach ($aContacts as $iId => $oContact) {
					if ($oContact->Archived == 0) {
						// Return only non-archived contacts
						$aStdContacts[$iId] = $oContact->toStdClass();
					}
				}
			}
			
			return array(
				'bSuccess' 	=> true,
				'oData'		=> $aStdContacts
			);
		} catch (Exception $oException) {
			return array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}
	
	public function mergeContacts($oMergedContact, $aMergedContactIds) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			// Start transaction
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false) {
				throw new Exception("Failed to start db transaction");
			}
			try {
				// Modify the merged contact
				$oContact = Contact::getForId($oMergedContact->Id);
				
				// Note: Uncomment this to get reversal information in the log output
				//Log::getLog()->log("REVERSE LOG: Pre-merged contact {$oMergedContact->Id} = ".print_r($oContact->toArray(), true));
				
				$oContact->Title 			= $oMergedContact->Title;
				$oContact->FirstName 		= $oMergedContact->FirstName;
				$oContact->LastName 		= $oMergedContact->LastName;
				$oContact->JobTitle 		= $oMergedContact->JobTitle;
				$oContact->Email 			= $oMergedContact->Email;
				$oContact->Phone 			= $oMergedContact->Phone;
				$oContact->Mobile 			= $oMergedContact->Mobile;
				$oContact->Fax 				= $oMergedContact->Fax;
				$oContact->DOB 				= $oMergedContact->DOB;
				$oContact->CustomerContact	= ($oMergedContact->CustomerContact ? 1 : 0);
				$oContact->PassWord			= Contact::getForId($oMergedContact->password_contact_id)->PassWord;
				$oContact->save();
				
				Log::getLog()->log("Saved merged contact {$oContact->Id}");
				
				// Disable the other 'dud' contacts
				$aReferencesToUpdate = array(
					0 => array(
						"table"			=> "Account",
						"id"			=> "Id",
						"contact_id"	=> "PrimaryContact"
					),
					1 => array(
						"table"			=> "employee_account_log",
						"id"			=> "id",
						"contact_id"	=> "contact_id"
					),
					2 => array(
						"table"			=> "Note",
						"id"			=> "Id",
						"contact_id"	=> "Contact"
					),
					3 => array(
						"table"			=> "credit_card_payment_history",
						"id"			=> "id",
						"contact_id"	=> "contact_id"
					),
					4 => array(
						"table"			=> "survey_completed",
						"id"			=> "id",
						"contact_id"	=> "contact_id"
					)
				);
				
				foreach ($aMergedContactIds as $iContactId) {
					$oDudContact = Contact::getForId($iContactId);
					
					// Note: Uncomment this to get reversal information in the log output
					//Log::getLog()->log("REVERSE LOG: Discarding contact {$iContactId} = ".print_r($oDudContact->toArray(), true));
					
					$oDudContact->AccountGroup	= 0;
					$oDudContact->Title			= null;
					$oDudContact->FirstName 	= '';
					$oDudContact->LastName 		= '';
					$oDudContact->JobTitle 		= '';
					$oDudContact->Email 		= null;
					$oDudContact->Account 		= 0;
					$oDudContact->Phone 		= '';
					$oDudContact->Mobile 		= '';
					$oDudContact->Fax 			= '';
					$oDudContact->Archived 		= 0;
					$oDudContact->save();
					Log::getLog()->log("Discarded contact {$oDudContact->Id}");
					
					// Update all references to the 'dud' contact
					foreach ($aReferencesToUpdate as $aDetails) {
						// Note: Uncomment this to get reversal information in the log output
						/*$mResult = Query::run("	SELECT	{$aDetails['id']} AS table_id
												FROM	{$aDetails['table']}
												WHERE	{$aDetails['contact_id']} = {$oDudContact->Id};");
						$aIds = array();
						while ($aRow = $mResult->fetch_assoc())
						{
							$aIds[] = $aRow['table_id'];
						}
						if (count($aIds) > 0)
						{
							Log::getLog()->log("REVERSE LOG: UPDATE	{$aDetails['table']} SET {$aDetails['contact_id']} = {$oDudContact->Id} WHERE {$aDetails['id']} IN (".implode(',', $aIds).");");
						}*/
						// END: Reversal log output

						Query::run("UPDATE	{$aDetails['table']} 
									SET 	{$aDetails['contact_id']} = {$oContact->Id} 
									WHERE 	{$aDetails['contact_id']} = {$oDudContact->Id};");
						Log::getLog()->log("Updated contact reference for discarded contact {$oDudContact->Id} in table {$aDetails['table']}");
					}
				}
			} catch (Exception $oEx) {
				// Exception, rollback transaction
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception("Failed to rollback db transaction. Exception=".$oEx->getMessage());
				}
				throw $oEx;
			}
			
			// Commit transaction
			if ($oDataAccess->TransactionCommit() === false) {
				throw new Exception("Failed to commit db transaction");
			}
			
			return array(
				'bSuccess' 	=> true,
				'sDebug'	=> $bUserIsGod ? $this->_JSONDebug : ''
			);
		} catch (Exception $oException) {
			return array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.',
				'sDebug'	=> $bUserIsGod ? $this->_JSONDebug : ''
			);
		}
	}
}

class JSON_Handler_Contact_Exception extends Exception
{
	// No changes
}

?>