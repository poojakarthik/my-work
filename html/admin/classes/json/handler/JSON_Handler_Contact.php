<?php

class JSON_Handler_Contact extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	protected	$_aPermissions	= array(PERMISSION_OPERATOR);
	
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
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to view this contact.'));
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
				$aDOB							= split( '-', $oContact->DOB );
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
				throw(new JSON_Handler_DataReport_Exception('You do not have permission to save contact details.'));
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
			if (!isset($oContactDetails->sTitle) || $oContactDetails->sTitle == '')
			{
				$aValidationErrors[]	= 'Title missing';
			}
			
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
			
			if (!is_numeric($oContactDetails->iPhone) && !is_numeric($oContactDetails->iMobile == ''))
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
}

class JSON_Handler_Contact_Exception extends Exception
{
	// No changes
}

?>