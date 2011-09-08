<?php
class AppTemplateUser extends ApplicationTemplate {

	function Register() {
		BreadCrumb()->SetCurrentPage("User Registration");

		$bRegistrationSuccessful	= false;

		// Were we POSTed to?
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			throw new Exception("Registration is not yet supported");

			try {
				$oAccountUser	= new Account_User();
				$oAccount		= Account::getForId();
				
				// Validation
				//---------------------------------------------------------------//
				// User: Username
				$oAccountUser->username	= $_POST['user-username'];

				// User: Password
				if (strlen($_POST['user-password']) < 6 || $_POST['user-password'] > 40) {
					throw new Exception("Password must be between ");
				}
				$oAccountUser->password	= $_POST['user-password'];

				// Account: Number

				// Account: Name

				// Account: Invoice Number
				$sAccountInvoiceNumber	= trim($_POST);
				//---------------------------------------------------------------//
			} catch(Exception $oValidationException) {
				// TODO
			}
			
			// Save to DB
			//---------------------------------------------------------------//
			try {
				
			} catch (Exception $oDatabaseException) {
				// TODO
			}
			//---------------------------------------------------------------//

			$bRegistrationSuccessful	= true;
		}

		if ($bRegistrationSuccessful) {
			$this->LoadPage('user_registered');
		} else {
			$this->LoadPage('user_register');
		}
		return true;
	}

};