<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// console
//----------------------------------------------------------------------------//
/**
 * console
 *
 * contains the ApplicationTemplate extended class AppTempalteConsole
 *
 * contains the ApplicationTemplate extended class AppTempalteConsole
 *
 * @file		console.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateConsole
//----------------------------------------------------------------------------//
/**
 * AppTemplateConsole
 *
 * The AppTemplateConsole class
 *
 * The AppTemplateConsole class.
 *
 *
 * @package	web_app
 * @class	AppTemplateConsole
 * @extends	ApplicationTemplate
 */
class AppTemplateConsole extends ApplicationTemplate
{
	
	 // Make a payment.
	 function Pay()
	 {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// Retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
			DBO()->Error->Message = "The contact with contact id: ". DBO()->Contact->Id->Value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		if (DBO()->Account->Id->Value)
		{
			// A specific account has been specified, so load the details of it
			// DBO()->Account->Id has already been initialised
		}
		else
		{
			// No specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();

		// If the user can view all accounts in their account group then load these too
		if (DBO()->Contact->CustomerContact->Value)
		{
			DBL()->Account->AccountGroup = DBO()->Contact->AccountGroup->Value;
			DBL()->Account->Archived = 0;
			DBL()->Account->Load();
		}
		// Make sure that the Account requested belongs to the account group that the contact belongs to
		$bolUserCanViewAccount = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Account->AccountGroup->Value)
			{
				$bolUserCanViewAccount = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Account->Id->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewAccount = TRUE;
		}
		
		if (!$bolUserCanViewAccount)
		{
			// The user does not have permission to view the requested account
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Calculate the Account Balance
		DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account Overdue Amount
		$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		if ($fltOverdue < 0)
		{
			$fltOverdue = 0;
		}
		DBO()->Account->Overdue = $fltOverdue;
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
		DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
		
		// Setup BreadCrumb Menu
		# $strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
		# BreadCrumb()->SetCurrentPage($strWelcome);

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		if (DBO()->Account->BusinessName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Make Payment - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Make Payment - " . substr(DBO()->Account->TradingName->Value, 0, 60));
		}
		else
		{
			// Don't display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Make Payment");
		}
		// Display the details of their primary address

		$this->LoadPage('pay');

		return TRUE;	 	
	 }
	 
	 
	 

//------------------------------------------------------------------------//
// Confirm Edit account details.
//------------------------------------------------------------------------//
/**
 * EditConfirm()
 *
 * Displays a confirmation to the user
 * 
 * Displays a confirmation to the user
 *
 * @return		void
 * @method
 *
 */
 function EditConfirm()
 {


	// Check user authorization and permissions
	AuthenticatedUser()->CheckClientAuth();
	
	// Retrieve the client's details
	DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
	if (!DBO()->Contact->Load())
	{
		// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
		DBO()->Error->Message = "The contact with contact id: ". DBO()->Contact->Id->Value ." could not be found";
		$this->LoadPage('error');
		return FALSE;
	}
	
	if (DBO()->Account->Id->Value)
	{
		// A specific account has been specified, so load the details of it
		// DBO()->Account->Id has already been initialised
	}
	else
	{
		// No specific account has been specified, so load the contact's primary account
		DBO()->Account->Id = DBO()->Contact->Account->Value;
	}
	
	// Load the clients primary account
	DBO()->Account->Load();

	// If the user can view all accounts in their account group then load these too
	if (DBO()->Contact->CustomerContact->Value)
	{
		DBL()->Account->AccountGroup = DBO()->Contact->AccountGroup->Value;
		DBL()->Account->Archived = 0;
		DBL()->Account->Load();
	}
	// Make sure that the Account requested belongs to the account group that the contact belongs to
	$bolUserCanViewAccount = FALSE;
	if (AuthenticatedUser()->_arrUser['CustomerContact'])
	{
		// The user can only view the account, if it belongs to the account group that they belong to
		if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Account->AccountGroup->Value)
		{
			$bolUserCanViewAccount = TRUE;
		}
	}
	elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Account->Id->Value)
	{
		// The user can only view the account, if it is their primary account
		$bolUserCanViewAccount = TRUE;
	}
	
	if (!$bolUserCanViewAccount)
	{
		// The user does not have permission to view the requested account
		BreadCrumb()->Console();
		BreadCrumb()->SetCurrentPage("Error");
		DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
		$this->LoadPage('Error');
		return FALSE;
	}

	// Calculate the Account Balance
	DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
	
	// Calculate the Account Overdue Amount
	$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
	if ($fltOverdue < 0)
	{
		$fltOverdue = 0;
	}
	DBO()->Account->Overdue = $fltOverdue;
	
	// Calculate the Account's total unbilled adjustments (inc GST)
	DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
	
	// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
	DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
	
	// Setup BreadCrumb Menu
	# $strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
	# BreadCrumb()->SetCurrentPage($strWelcome);
	
	// Breadcrumb menu
	BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
	if (DBO()->Account->BusinessName->Value)
	{
		// Display the business name in the bread crumb menu
		BreadCrumb()->SetCurrentPage("Edit Account - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
	}
	elseif (DBO()->Account->TradingName->Value)
	{
		// Display the business name in the bread crumb menu
		BreadCrumb()->SetCurrentPage("Edit Account - " . substr(DBO()->Account->TradingName->Value, 0, 60));
	}
	else
	{
		// Don't display the business name in the bread crumb menu
		BreadCrumb()->SetCurrentPage("Edit Account");
	}


	// Get account Id, we need to auto fill some form details.
	$intAccountId = DBO()->Account->Id->Value;
	$strOldEmailAddress = DBO()->Contact->Email->Value;

	if(array_key_exists('intUpdateAccountId', $_POST))
	{
		$strFoundInputError=FALSE; 

		// If no error was found, continue with processing.
		if(!$strFoundInputError){
			

			$mixFoundError = FALSE;
			if($_POST['mixAccount_NewPassword1'] != "" || $_POST['mixAccount_NewPassword2'] != "")
			{
				if(SHA1($_POST['mixAccount_OldPassword']) != DBO()->Contact->PassWord->Value)
				{
					$mixFoundError = TRUE;
				}
				if(strlen($_POST['mixAccount_NewPassword1'])>"40" || strlen($_POST['mixAccount_NewPassword1'])<"6")
				{
					$mixFoundError = TRUE;
				}
				if($_POST['mixAccount_NewPassword1'] != $_POST['mixAccount_NewPassword2'])
				{
					$mixFoundError = TRUE;
				}
			}
			if($mixFoundError)
			{
				$this->LoadPage('edit_passfail');
				return TRUE;
			}
			$this->LoadPage('edit_confirm');
			return TRUE;
		}
		else
		{
			$this->LoadPage('edit_failure');
			return TRUE;
		}
	}
 }

	//------------------------------------------------------------------------//
	// Edit account details.
	//------------------------------------------------------------------------//
	/**
	 * Edit()
	 *
	 * Allow user to modfy account,contact and billing details.
	 * 
	 * Allow user to modfy account,contact and billing details.
	 *
	 * @return		void
	 * @method
	 *
	 */
	 function Edit()
	 {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// Retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
			DBO()->Error->Message = "The contact with contact id: ". DBO()->Contact->Id->Value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		if (DBO()->Account->Id->Value)
		{
			// A specific account has been specified, so load the details of it
			// DBO()->Account->Id has already been initialised
		}
		else
		{
			// No specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();

		// If the user can view all accounts in their account group then load these too
		if (DBO()->Contact->CustomerContact->Value)
		{
			DBL()->Account->AccountGroup = DBO()->Contact->AccountGroup->Value;
			DBL()->Account->Archived = 0;
			DBL()->Account->Load();
		}
		// Make sure that the Account requested belongs to the account group that the contact belongs to
		$bolUserCanViewAccount = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Account->AccountGroup->Value)
			{
				$bolUserCanViewAccount = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Account->Id->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewAccount = TRUE;
		}
		
		if (!$bolUserCanViewAccount)
		{
			// The user does not have permission to view the requested account
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Calculate the Account Balance
		DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account Overdue Amount
		$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		if ($fltOverdue < 0)
		{
			$fltOverdue = 0;
		}
		DBO()->Account->Overdue = $fltOverdue;
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
		DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
		
		// Setup BreadCrumb Menu
		# $strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
		# BreadCrumb()->SetCurrentPage($strWelcome);
		
		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		if (DBO()->Account->BusinessName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Edit Account - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Edit Account - " . substr(DBO()->Account->TradingName->Value, 0, 60));
		}
		else
		{
			// Don't display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Edit Account");
		}

		// Get account Id, we need to auto fill some form details.
		$intAccountId = DBO()->Account->Id->Value;
		$strOldEmailAddress = DBO()->Contact->Email->Value;

		if(array_key_exists('intUpdateAccountId', $_POST))
		{
			$strFoundInputError=FALSE; 

			// If no error was found, continue with processing.
			if(!$strFoundInputError){
				
				DBO()->Account->Address1 = $_POST['mixAccount_Address1'];
				DBO()->Account->Address2 = $_POST['mixAccount_Address2'];
				DBO()->Account->Suburb = $_POST['mixAccount_Suburb'];
				DBO()->Account->State = $_POST['mixAccount_State'];
				DBO()->Account->Postcode = $_POST['mixAccount_Postcode'];
				DBO()->Account->BillingMethod = $_POST['mixAccount_BillingMethod'];
				DBO()->Account->Country = $_POST['mixAccount_Country'];
				DBO()->Account->SetColumns("Address1,Address2,Suburb,State,Postcode,BillingMethod,Country");
				DBO()->Account->Save();
				# Debug.
				# var_dump($_POST);exit;

				DBO()->Contact->FirstName = $_POST['mixContact_FirstName'];
				DBO()->Contact->LastName = $_POST['mixContact_LastName'];
				DBO()->Contact->Title = $_POST['mixContact_Title'];
				DBO()->Contact->JobTitle = $_POST['mixContact_JobTitle'];
				DBO()->Contact->Email = $_POST['mixContact_Email'];
				DBO()->Contact->Phone = $_POST['mixContact_Phone'];
				DBO()->Contact->Mobile = $_POST['mixContact_Mobile'];
				DBO()->Contact->Fax = $_POST['mixContact_Fax'];

				$mixFoundError = FALSE;
				if($_POST['mixAccount_OldPassword'] == "" || $_POST['mixAccount_NewPassword1'] == "" || $_POST['mixAccount_NewPassword2'] == "")
				{
					//echo "error 1<br/>";
					$mixFoundError = TRUE;
				}
				if(SHA1($_POST['mixAccount_OldPassword']) != DBO()->Contact->PassWord->Value)
				{
					//echo "error 2<br/>";
					$mixFoundError = TRUE;
				}

				if($_POST['mixAccount_NewPassword1'] != $_POST['mixAccount_NewPassword2'])
				{
					//echo "error 3<br/>";
					$mixFoundError = TRUE;
				}
				if(strlen($_POST['mixAccount_NewPassword1'])>"40" || strlen($_POST['mixAccount_NewPassword1'])<"6")
				{
					//echo "error 4<br/>";
					$mixFoundError = TRUE;
				}
				if($mixFoundError == FALSE)
				{
					DBO()->Contact->SetColumns("FirstName,LastName,Title,JobTitle,Email,Phone,Mobile,Fax,PassWord");
					DBO()->Contact->PassWord = SHA1($_POST['mixAccount_NewPassword1']);
				}
				if($mixFoundError)
				{
					DBO()->Contact->SetColumns("FirstName,LastName,Title,JobTitle,Email,Phone,Mobile,Fax");
				}
				DBO()->Contact->Save();

				$to      = $_POST['mixContact_Email'];
				$subject = "Account Updated #$intAccountId";
				$message = "The account changes below have been made:\n\n";

				$message .= "FirstName: " . $_POST['mixContact_FirstName'] . "\n";
				$message .= "LastName: " . $_POST['mixContact_LastName'] . "\n";
				$message .= "Title: " . $_POST['mixContact_Title'] . "\n";
				$message .= "JobTitle: " . $_POST['mixContact_JobTitle'] . "\n";
				$message .= "Email: " . $_POST['mixContact_Email'] . "\n";
				$message .= "Phone: " . $_POST['mixContact_Phone'] . "\n";
				$message .= "Mobile: " . $_POST['mixContact_Mobile'] . "\n";
				$message .= "Fax: " . $_POST['mixContact_Fax'] . "\n";
				$message .= "Address1: " . $_POST['mixAccount_Address1'] . "\n";
				$message .= "Address2: " . $_POST['mixAccount_Address2'] . "\n";
				$message .= "Suburb: " . $_POST['mixAccount_Suburb'] . "\n";
				$message .= "State: " . $_POST['mixAccount_State'] . "\n";
				$message .= "Postcode: " . $_POST['mixAccount_Postcode'] . "\n";

				$intBillMethod = htmlspecialchars($_POST['mixAccount_BillingMethod']);
				$strNewBillingMethod = $GLOBALS['*arrConstant']['BillingMethod'][$intBillMethod]['Description'];

				$message .= "BillingMethod: $strNewBillingMethod\n";

				$message .= "Country: $_POST[mixAccount_Country]\n\n";

				$message .= "Kind Regards\n";
				$message .= "Customer Service Group\n";
				if($strOldEmailAddress!="$_POST[mixContact_Email]")
				{
					$headers .= "CC: $_POST[mixContact_Email]\r\n";
				}
				$headers .= 'From: Customer Service Group<' . NOTIFICATION_REPLY_EMAIL . ">\r\n" .
					'X-Mailer: Flex/' . phpversion();
				# supress email errors.
				@mail($strOldEmailAddress, $subject, $message, $headers);

				$this->LoadPage('edit_successful');
				return TRUE;
			}
			else
			{
				$this->LoadPage('edit_failure');
				return TRUE;
			}
		}

		$this->LoadPage('edit');
		return TRUE;	 	
	 }
	 
	 
	function Home()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// Retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
			DBO()->Error->Message = "The contact with contact id: ". DBO()->Contact->Id->Value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		if (DBO()->Account->Id->Value)
		{
			// A specific account has been specified, so load the details of it
			// DBO()->Account->Id has already been initialised
		}
		else
		{
			// No specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();

		// If the user can view all accounts in their account group then load these too
		if (DBO()->Contact->CustomerContact->Value)
		{
			DBL()->Account->AccountGroup = DBO()->Contact->AccountGroup->Value;
			DBL()->Account->Archived = 0;
			DBL()->Account->Load();
		}
		// Make sure that the Account requested belongs to the account group that the contact belongs to
		$bolUserCanViewAccount = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Account->AccountGroup->Value)
			{
				$bolUserCanViewAccount = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Account->Id->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewAccount = TRUE;
		}
		
		if (!$bolUserCanViewAccount)
		{
			// The user does not have permission to view the requested account
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Calculate the Account Balance
		DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account Overdue Amount
		$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		if ($fltOverdue < 0)
		{
			$fltOverdue = 0;
		}
		DBO()->Account->Overdue = $fltOverdue;
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
		DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
		
		$strShowLastLogin = "Never";
		$intLastLogin = DBO()->Contact->LastLogin->Value;
		if($intLastLogin != "")
		{
			$strShowLastLogin = date("F j, Y, g:i a",$intLastLogin);
		}
		// Setup BreadCrumb Menu
		$strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account. Last Login: $strShowLastLogin\n";
		BreadCrumb()->SetCurrentPage($strWelcome);
		
		$this->LoadPage('console');

		return TRUE;
	}

	//------------------------------------------------------------------------//
	// Logout
	//------------------------------------------------------------------------//
	/**
	 * Logout()
	 *
	 * Performs the logic for logging out the user
	 * 
	 * Performs the logic for logging out the user
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Logout()
	{
		if ($this->_objAjax != NULL)
		{
			// This method was executed via an ajax call.  Use a popup to notify the user, that they have been logged out
			AuthenticatedUser()->LogoutClient();
			
			//TODO! Check if they were successfully logged out, or if their session is not the most recent.
			// I have done this for the case, where this method is executed from a url.  It can be found in HtmlTemplateLoggedOut
			
			// Redirect the user to the main page of the website
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "Logout successful", "Location" => Href()->MainPage()));
		}
		else
		{
			// This method was executed via a url.  Use a page to notify the user, that they have been logged out
			AuthenticatedUser()->LogoutClient();
			
			$this->LoadPage('logged_out');
		}
		
		return TRUE;
	}


    //----- DO NOT REMOVE -----//
    
    
    
  
	//------------------------------------------------------------------------//
	// Logout
	//------------------------------------------------------------------------//
	/**
	 * Logout()
	 *
	 * Performs the logic for logging out the user
	 * 
	 * Performs the logic for logging out the user
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Password()
	{


		// Connect to database
		$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
		
		// Check if the form has been submitted.
		if(array_key_exists('mixUserName', $_POST))
		{
			// By default all password requests will fail.
			DBO()->Fail = TRUE;

			// Check the syntax of the username entered by user..
			$mixInput = $_POST['mixUserName'];
			list($strFoundError,$strErrorResponse) = InputValidation("UserName",$mixInput,"mixed",31);

			// If there is no UserName errror
			if(!$strFoundError)
			{
				//then we can check the database for a record.
				$strCustEmail = $dbConnection->fetchone("SELECT Email,Account FROM `Contact` WHERE UserName = \"$mixInput\" LIMIT 1");

				// if the email address exists in db then we reset the pass..
				if($strCustEmail->Email)
				{
					// Reset password
					$strTxtPassword = RandomString("10");
					$dbConnection->execute("UPDATE `Contact` SET `PassWord` = SHA1( '$strTxtPassword' ) WHERE UserName = \"$mixInput\"");

					// And send an email...
					$to      = $strCustEmail->Email;
					$subject = "Account Updated #" . $strCustEmail->Account;
					$message = "The account changes below have been made:\n\n";
					$message .= "New Password: $strTxtPassword\n\n";
					$message .= "Kind Regards\n";
					$message .= "Customer Service Group\n";
					$headers .= 'From: Customer Service Group<' . NOTIFICATION_REPLY_EMAIL . ">\r\n" .
						'X-Mailer: Flex/' . phpversion();
					# supress email errors.
					@mail($to, $subject, $message, $headers);
					DBO()->Fail = FALSE;
				}
			}

			// email not found in db?
			if(DBO()->Fail)
			{
				// Brute Force attack prevention.
				sleep(9);
			}
		}

		$this->LoadPage('reset_password');
		return TRUE;
	}
	
}
