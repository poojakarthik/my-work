<?php
class AppTemplateUser extends ApplicationTemplate {

	const	DEBUG	= false;

	const	ACCOUNT_NAME_LEVENSHTEIN_ENABLED					= true;
	const	ACCOUNT_NAME_LEVENSHTEIN_DISTANCE_MAX_PERCENTAGE	= 0.2;	// Distance can be a maximum of 20% of the string length
	const	ACCOUNT_NAME_LEVENSHTEIN_MIN_LENGTH					= 8;	// Minimum length of the real Account Name for Levenshtein allowance is enabled

	const	USER_PASSWORD_LENGTH_MIN	= 6;
	const	USER_PASSWORD_LENGTH_MAX	= 40;

	const	USER_USERNAME_LENGTH_MIN	= 3;
	const	USER_USERNAME_LENGTH_MAX	= 30;

	public function Register() {
		try {
			$oCustomerGroup	= Customer_Group::getForBaseURL(($_SERVER['HTTPS'] ? 'https://' : 'http://').$_SERVER['HTTP_HOST']);

			BreadCrumb()->SetCurrentPage("User Registration");

			$bRegistrationSuccessful	= false;

			// Were we POSTed to?
			if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
				//throw new Exception("Registration is not yet supported");

				$aValidationErrors	= array();

				$oAccountUser	= new Account_User();
				
				// Validation
				//---------------------------------------------------------------//
				// User: Username
				try {
					$sUsername	= self::_validateUsername($_POST['user-username']);
				} catch (AppTemplateUserExceptionValidation $oUsernameException) {
					$aValidationErrors['user-username']	= $oUsernameException->getRelevantMessage();
				}
				$oAccountUser->username	= $sUsername;

				// User: Password
				try {
					$sPassword	= self::_validatePassword($_POST['user-password'], $_POST['user-password-confirm']);
				} catch (AppTemplateUserExceptionValidation $oPasswordException) {
					$aValidationErrors['user-password']	= $oPasswordException->getRelevantMessage();
				}
				$oAccountUser->password	= sha1($sPassword);

				// User: Actual Name
				$oAccountUser->given_name	= $_POST['user-givenname'];
				$oAccountUser->family_name	= $_POST['user-familyname'];
				if (!strlen($oAccountUser->given_name)) {
					$aValidationErrors['user-givenname']	= 'Please supply your Given Name';
				}
				if (!strlen($oAccountUser->family_name)) {
					$aValidationErrors['user-familyname']	= 'Please supply your Family Name';
				}

				// User: Email
				$oAccountUser->email	= trim($_POST['user-email']);
				if (!preg_match('/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i', $oAccountUser->email)) {
					$aValidationErrors['user-email']	= "The supplied email address is invalid.";
				}

				// Account
				try {
					$oAccount	= self::_validateAccount($_POST['account-number'], $_POST['account-name'], $_POST['account-invoice-number'], $oCustomerGroup);
				} catch (AppTemplateUserExceptionValidation $oAccountException) {
					$aValidationErrors['account']	= $oAccountException->getRelevantMessage();
				}
				$oAccountUser->account_id	= $oAccount->Id;
				//---------------------------------------------------------------//
				
				if (!$aValidationErrors) {
					// Save to DB
					//---------------------------------------------------------------//
					$oDataAccess	= DataAccess::getDataAccess();
					
					// Start Transaction
					$oDataAccess->TransactionStart();
					try {
						$oAccountUser->status_id	= STATUS_ACTIVE;
						$oAccountUser->save();

						//throw new Exception("DEBUG LOL! Saving: ".print_r($oAccountUser->toArray(), true));

						// Commit
						$oDataAccess->TransactionCommit();
					} catch (Exception $oDatabaseException) {
						// Rollback and re-throw
						$oDataAccess->TransactionRollback();
						throw $oDatabaseException;
					}
					//---------------------------------------------------------------//

					// Log the user in automatically and redirect to the console
					//---------------------------------------------------------------//
					// HACKHACKHACK: Lovely hacks.  Fake authentication then redirect to the console.
					$_POST['VixenUserName']	= $sUsername;
					$_POST['VixenPassword']	= $sPassword;
					AuthenticatedUser()->CheckClientAuth();

					header("Location: ".Href()->Home());
					exit;
					//---------------------------------------------------------------//
				} else {
					DBO()->GeneralError->Message	= 'There were errors with some of the information you supplied.  Please look below for more details.';
					DBO()->ValidationErrors->Fields	= $aValidationErrors;
				}
			}
		} catch (Exception $oException) {
			// TODO: Somehow pass a "general" 500-style error through to user_register
			// FIXME: This error text should probably be handled in the renderer.
			//$sGeneralError	= "An internal error caused your registration to fail.  If you continue to see this message, contact us using one of the methods listed on [CUSTOMER_GROUP_URL]";
			DBO()->GeneralError->Message	= (self::DEBUG) ? $oException->getMessage() : "An internal error caused your registration to fail — please try again.";
		}

		$this->LoadPage('user_register');
		return true;
	}

	private static function _validateUsername($sUsername) {
		$sCleanedUsername	= trim($sUsername);

		// Validate
		if (!strlen($sCleanedUsername)) {
			throw new AppTemplateUserExceptionValidation("Please supply a username.");
		}
		if (!preg_match('/^[0-9a-zA-Z_-]{'.self::USER_USERNAME_LENGTH_MIN.','.self::USER_USERNAME_LENGTH_MAX.'}$/', $sCleanedUsername)) {
			throw new AppTemplateUserExceptionValidation("'{$sUsername}' contains illegal characters.  Please try another.");
		}

		// Ensure there are no other users with this username
		if (Query::run("
			SELECT		*
			FROM		account_user au
			WHERE		au.username = <sUsername> /* Case-insensitive match */
		", array('sUsername'=>$sCleanedUsername))->num_rows) {
			throw new AppTemplateUserExceptionValidation("{$sUsername} is already in use.  Please try another username.");
		}

		return $sCleanedUsername;
	}

	private static function _validatePassword($sPassword, $sPasswordConfirmation) {
		if ($sPassword !== $sPasswordConfirmation) {
			throw new AppTemplateUserExceptionValidation("The supplied passwords did not match.");
		} else if (strlen($sPassword) < self::USER_PASSWORD_LENGTH_MIN) {
			throw new AppTemplateUserExceptionValidation("Your password is too short.");
		} else if (strlen($sPassword) > self::USER_PASSWORD_LENGTH_MAX) {
			throw new AppTemplateUserExceptionValidation("Your password is too long.");
		}
		return $sPassword;
	}

	private static function _validateAccount($sAccountNumber, $sAccountName, $sRecentInvoice, $aAllowableCustomerGroups) {
		if (!trim($sAccountNumber)) {
			throw new AppTemplateUserExceptionValidation("Please supply your Account Number.");
		}
		if (!trim($sAccountName)) {
			throw new AppTemplateUserExceptionValidation("Please supply your Account Name.");
		}
		if (!trim($sRecentInvoice)) {
			throw new AppTemplateUserExceptionValidation("Please supply one of your 3 most recent Invoice Numbers.");
		}

		// Account Number
		//-------------------------------------------------------------------//
		// Validate format
		$iAccountNumber	= (int)$sAccountNumber;
		if (!preg_match('/^[0-9]+$/', trim($sAccountNumber))) {
			throw new AppTemplateUserExceptionValidation("{$sAccountNumber} is not a valid Account Number", "We couldn't verify your Account details.  Please double-check them and try again.");
		}
		// Find the relevant Account
		try {
			$oAccount	= Account::getForId((int)$iAccountNumber);
		} catch (Exception_ORM $oAccountNotFoundException) {
			throw new AppTemplateUserExceptionValidation("Account '{$iAccountNumber}' doesn't exist", "We couldn't verify your Account details.  Please double-check them and try again.");
		}

		// Ensure that the Account is from the correct Customer Group
		$oAccountCustomerGroup	= Customer_Group::getForId($oAccount->CustomerGroup);
		if (!in_array($oAccountCustomerGroup->id, array_keys($aAllowableCustomerGroups))) {
			$aCustomerGroupNames	= array();
			foreach ($aAllowableCustomerGroups as $oAllowableCustomerGroup) {
				$aCustomerGroupNames[]	= $oAllowableCustomerGroup->internal_name;
			}
			throw new AppTemplateUserExceptionValidation("Account {$iAccountNumber} is a {$oAccountCustomerGroup->internal_name} Account, but URL restricts to [".implode(', ', $aCustomerGroupNames)."]", "We couldn't verify your Account details.  Please double-check them and try again.");
		}
		//-------------------------------------------------------------------//

		// Account Name
		//-------------------------------------------------------------------//
		$sMinifiedSuppliedName	= preg_replace('/[^a-z0-9]/', '', strtolower($sAccountName));
		$sMinifiedActualName	= preg_replace('/[^a-z0-9]/', '', strtolower($oAccount->BusinessName));

		if (self::ACCOUNT_NAME_LEVENSHTEIN_ENABLED && strlen($sMinifiedActualName) >= self::ACCOUNT_NAME_LEVENSHTEIN_MIN_LENGTH) {
			// Calculate Levenshtein distance (allow close matches)
			$iAccountNameLevenshtein		= levenshtein($sMinifiedSuppliedName, $sMinifiedActualName);
			$iPermittedLevenshteinDistance	= floor(strlen($sMinifiedActualName) * self::ACCOUNT_NAME_LEVENSHTEIN_DISTANCE_MAX_PERCENTAGE);
			if ($iAccountNameLevenshtein === -1) {
				// Algortithm only works up to 255 characters
				throw new AppTemplateUserExceptionValidation("Account Name exceeds limits of Levenshtein algorithm (Supplied: '{$sMinifiedSuppliedName}'; Actual: '{$sMinifiedActualName}'; Distance: {$iAccountNameLevenshtein}; Permitted Distance: {$iPermittedLevenshteinDistance})", "We couldn't verify your Account details.  Please double-check them and try again.");
			} elseif ($iAccountNameLevenshtein > $iPermittedLevenshteinDistance) {
				throw new AppTemplateUserExceptionValidation("Account Name Levenshtein distance is too great (Supplied: '{$sMinifiedSuppliedName}'; Actual: '{$sMinifiedActualName}'; Distance: {$iAccountNameLevenshtein}; Permitted Distance: {$iPermittedLevenshteinDistance})", "We couldn't verify your Account details.  Please double-check them and try again.");
			}
		} else {
			if ($sMinifiedSuppliedName !== $sMinifiedActualName) {
				throw new AppTemplateUserExceptionValidation("Account Names do not match (Supplied: '{$sMinifiedSuppliedName}'; Actual: '{$sMinifiedActualName}')", "We couldn't verify your Account details.  Please double-check them and try again.");
			}
		}
		//-------------------------------------------------------------------//

		// Recent Invoice
		//-------------------------------------------------------------------//
		// Validate format
		$iInvoiceNumber	= (int)$sRecentInvoice;
		if (!preg_match('/^[0-9]+$/', trim($sRecentInvoice))) {
			throw new AppTemplateUserExceptionValidation("{$sRecentInvoice} is not a valid Invoice Number", "We couldn't verify your Account details.  Please double-check them and try again.");
		}
		// Find the relevant Invoice
		try {
			$oInvoice	= Invoice::getForId((int)$iInvoiceNumber);
		} catch (Exception_ORM $oInvoiceNotFoundException) {
			throw new AppTemplateUserExceptionValidation("Invoice '{$iAccountNumber}' doesn't exist", "We couldn't verify your Account details.  Please double-check them and try again.");
		}
		// Are the Account and Invoice linked?
		if ($oInvoice->Account != $iAccountNumber) {
			throw new AppTemplateUserExceptionValidation("Invoice '{$iInvoiceNumber}' doesn't belong to Account {$iAccountNumber}", "We couldn't verify your Account details.  Please double-check them and try again.");
		}

		// Is this Invoice one of the 3 most recent?
		if (!Query::run("
			SELECT		i_recent.*
			FROM		Account a
						JOIN (
							SELECT		i_recent_i.*
							FROM		Invoice i_recent_i
										JOIN InvoiceRun i_recent_ir ON (i_recent_ir.Id = i_recent_i.invoice_run_id)
							WHERE		i_recent_i.Account = <iAccountId>
										AND i_recent_ir.invoice_run_type_id IN (
											".INVOICE_RUN_TYPE_LIVE.",
											".INVOICE_RUN_TYPE_INTERIM.",
											".INVOICE_RUN_TYPE_FINAL.",
											".INVOICE_RUN_TYPE_INTERIM_FIRST."
										)
							ORDER BY	i_recent_i.CreatedOn DESC,
										i_recent_i.Id DESC
							LIMIT		3
						) i_recent
			WHERE		a.Id = <iAccountId>
						AND i_recent.Id = <iInvoiceId>;
		", array('iAccountId'=>$iAccountNumber, 'iInvoiceId'=>$iInvoiceNumber))->num_rows) {
			// No match
			throw new AppTemplateUserExceptionValidation("Invoice '{$iInvoiceNumber}' is too old", "We couldn't verify your Account details.  Please double-check them and try again.");
		}
		//-------------------------------------------------------------------//

		return $oAccount;
	}

};

class AppTemplateUserExceptionValidation extends Exception {
	public static $bForceDebugMessages	= false;	// DEBUG

	public function __construct($sDebugMessage, $sUserMessage=null) {
		$this->_sDebugMessage	= $sDebugMessage;
		$this->_sUserMessage	= ($sUserMessage) ? $sUserMessage : $sDebugMessage;
		parent::__construct($this->getRelevantMessage());
	}

	public function getRelevantMessage() {
		return self::$bForceDebugMessages ? $this->_sDebugMessage : $this->_sUserMessage;
	}

	public function getUserMessage() {
		return $this->_sUserMessage;
	}

	public function getDebugMessage() {
		return $this->_sDebugMessage;
	}
};

?>