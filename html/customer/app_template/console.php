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
	 
	 
	 
	
	 // User FAQ
	 function FAQ()
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

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Customer FAQ");

		$this->LoadPage('faq');

		return TRUE;	 	
	 }



	function GetServices($intAccount, $intFilter=SERVICE_ACTIVE)
	{
		// Load all the services belonging to the account
		// OLD method
		//DBL()->Service->Where->Set("Account = <Account>", Array("Account"=>DBO()->Account->Id->Value));
		//DBL()->Service->OrderBy("ServiceType ASC, FNN ASC, Id DESC");
		//DBL()->Service->Load();
		
		// Retrieve all the services belonging to the account
		$strTables	= "	Service AS S 
						LEFT JOIN ServiceRatePlan AS SRP1 ON S.Id = SRP1.Service AND SRP1.Id = (SELECT SRP2.Id 
								FROM ServiceRatePlan AS SRP2 
								WHERE SRP2.Service = S.Id AND NOW() BETWEEN SRP2.StartDatetime AND SRP2.EndDatetime
								ORDER BY SRP2.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP1 ON SRP1.RatePlan = RP1.Id
						LEFT JOIN ServiceRatePlan AS SRP3 ON S.Id = SRP3.Service AND SRP3.Id = (SELECT SRP4.Id 
								FROM ServiceRatePlan AS SRP4 
								WHERE SRP4.Service = S.Id AND SRP4.StartDatetime BETWEEN NOW() AND SRP4.EndDatetime
								ORDER BY SRP4.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP2 ON SRP3.RatePlan = RP2.Id";
		$arrColumns	= Array("Id" 						=> "S.Id",
							"FNN"						=> "S.FNN",
							"ServiceType"				=> "S.ServiceType", 
							"Status"		 			=> "S.Status",
							"LineStatus"				=> "S.LineStatus",
							"LineStatusDate"			=> "S.LineStatusDate",
							"CreatedOn"					=> "S.CreatedOn", 
							"ClosedOn"					=> "S.ClosedOn",
							"CreatedBy"					=> "S.CreatedBy", 
							"ClosedBy"					=> "S.ClosedBy",
							"NatureOfCreation"			=> "S.NatureOfCreation",
							"NatureOfClosure"			=> "S.NatureOfClosure",
							"LastOwner"					=> "S.LastOwner",
							"NextOwner"					=> "S.NextOwner",
							"CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"CurrentPlanContractTerm"	=> "RP1.ContractTerm",
							"CurrentPlanStartDatetime"	=> "SRP1.StartDatetime",
							"CurrentPlanEndDatetime"	=> "SRP1.EndDatetime",
							"CurrentPlanContractExpiresOn"	=> "SRP1.StartDatetime + INTERVAL RP1.ContractTerm MONTH",

							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanContractTerm"	=> "RP2.ContractTerm",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime",
							"FuturePlanEndDatetime"		=> "SRP3.EndDatetime",
							"FuturePlanContractExpiresOn"	=> "SRP3.StartDatetime + INTERVAL RP2.ContractTerm MONTH");
		$strWhere	= "S.Account = <AccountId> AND (S.ClosedOn IS NULL OR S.CreatedOn <= S.ClosedOn)";
		$arrWhere	= Array("AccountId" => $intAccount);
		$strOrderBy	= ("S.ServiceType ASC, S.FNN ASC, S.Id DESC");
		
		$selServices = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
		if ($selServices->Execute($arrWhere) === FALSE)
		{
			// An error occurred
			return FALSE;
		}
		
		$arrServices	= Array();
		$arrRecord		= $selServices->Fetch();
		while ($arrRecord !== FALSE)
		{
			// Create the Service Array
			$arrService = Array (
									"Id"			=> $arrRecord['Id'],
									"FNN"			=> $arrRecord['FNN'],
									"ServiceType"	=> $arrRecord['ServiceType']
								);

			// Add details about the Service's current plan, if it has one
			if ($arrRecord['CurrentPlanId'] != NULL)
			{
				$arrService['CurrentPlan'] = Array	(
														"Id"	=> $arrRecord['CurrentPlanId'],
														"Name"	=> $arrRecord['CurrentPlanName'],
														"ContractTerm"	=> $arrRecord['CurrentPlanContractTerm'],
														"StartDatetime"	=> $arrRecord['CurrentPlanStartDatetime'],
														"EndDatetime"	=> $arrRecord['CurrentPlanEndDatetime'],
														"ContractExpiresOn"	=> $arrRecord['CurrentPlanContractExpiresOn']
													);
			}
			else
			{
				$arrService['CurrentPlan'] = NULL;
			}
			
			// Add details about the Service's Future scheduled plan, if it has one
			if ($arrRecord['FuturePlanId'] != NULL)
			{
				$arrService['FuturePlan'] = Array	(
														"Id"	=> $arrRecord['FuturePlanId'],
														"Name"	=> $arrRecord['FuturePlanName'],
														"ContractTerm"	=> $arrRecord['CurrentPlanContractTerm'],
														"StartDatetime"	=> $arrRecord['FuturePlanStartDatetime'],
														"EndDatetime"	=> $arrRecord['CurrentPlanEndDatetime'],
														"ContractExpiresOn"	=> $arrRecord['CurrentPlanContractExpiresOn']
													);
			}
			else
			{
				$arrService['FuturePlan'] = NULL;
			}
			
			// Add this record's details to the history array
			$arrService['History']		= Array();
			$arrService['History'][]	= Array	(
													"ServiceId"			=> $arrRecord['Id'],
													"CreatedOn"			=> $arrRecord['CreatedOn'],
													"ClosedOn"			=> $arrRecord['ClosedOn'],
													"CreatedBy"			=> $arrRecord['CreatedBy'],
													"ClosedBy"			=> $arrRecord['ClosedBy'],
													"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
													"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
													"LastOwner"			=> $arrRecord['LastOwner'],
													"NextOwner"			=> $arrRecord['NextOwner'],
													"Status"			=> $arrRecord['Status'],
													"LineStatus"		=> $arrRecord['LineStatus'],
													"LineStatusDate"	=> $arrRecord['LineStatusDate']
												);
			 
			
			// If multiple Service records relate to the one actual service then they will be consecutive in the RecordSet
			// Find each one and add it to the Status history
			while (($arrRecord = $selServices->Fetch()) !== FALSE)
			{
				if ($arrRecord['FNN'] == $arrService['FNN'])
				{
					// This record relates to the same Service
					$arrService['History'][]	= Array	(
															"ServiceId"	=> $arrRecord['Id'],
															"CreatedOn"			=> $arrRecord['CreatedOn'],
															"ClosedOn"			=> $arrRecord['ClosedOn'],
															"CreatedBy"			=> $arrRecord['CreatedBy'],
															"ClosedBy"			=> $arrRecord['ClosedBy'],
															"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
															"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
															"LastOwner"			=> $arrRecord['LastOwner'],
															"NextOwner"			=> $arrRecord['NextOwner'],
															"Status"			=> $arrRecord['Status'],
															"LineStatus"		=> $arrService['LineStatus'],
															"LineStatusDate"	=> $arrService['LineStatusDate']
														);
				}
				else
				{
					// We have moved on to the next Service
					break;
				}
			}
			
			// Add the Service to the array of Services
			$arrServices[] = $arrService;
		}
		
		// Apply the filter
		$strNow = GetCurrentISODateTime();
		if ($intFilter)
		{
			$arrTempServices	= $arrServices;
			$arrServices		= Array();
			
			foreach ($arrTempServices as $arrService)
			{
				switch ($intFilter)
				{
					case SERVICE_ACTIVE:
						// Only keep the Service if ClosedOn IS NULL OR NOW() OR in the future
						if ($arrService['History'][0]['ClosedOn'] == NULL || $arrService['History'][0]['ClosedOn'] >= $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
					
					case SERVICE_DISCONNECTED:
						// Only keep the Service if Status == Disconnected AND ClosedOn < NOW()
						if ($arrService['History'][0]['Status'] == SERVICE_DISCONNECTED && $arrService['History'][0]['ClosedOn'] < $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
					
					case SERVICE_ARCHIVED:
						// Only keep the Service if Status == Archived AND ClosedOn < NOW()
						if ($arrService['History'][0]['Status'] == SERVICE_ARCHIVED && $arrService['History'][0]['ClosedOn'] < $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
				}
			}
		}
		
		return $arrServices;
	}


	 // User Support
	 function Support()
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

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Support Request");

		$bolFoundErrors = FALSE;
		$bolFoundSubmit = FALSE;
		DBO()->ErrorMessage = "";

		if(array_key_exists('intRequestTypeSubmit',$_POST))
		{
			$arrFieldsList = NULL;
			$arrFieldsList = array();
			$arrFieldsList['Request Type'] = $GLOBALS['*arrConstant']['SupportType'][$_POST['intRequestType']]['Description'] . "\n";
			
				switch($_POST['intRequestType'])
				{
					case "1":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["Faulty Line $key"] = "$value";
					}
					break;

					case "2":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["Faulty Line $key"] = "$value";
					}
					$arrFieldsList['Diversions Required'] = $_POST['intDiversionsRequired'];
					$arrFieldsList['Diversion From Number'] = $_POST['intDiversionFromNumber'];
					$arrFieldsList['Diversion To Number'] = $_POST['intDiversionToNumber'] . "\n";
					break; 

					case "3":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["Faulty Line $key"] = "$value";
					}
					break; 
					
					case "4":
					// no additional fields
					break; 
					
					case "5":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["Faulty Line $key"] = "$value";
					}
					break; 

					default:
					// Unable to determine request type..?
					break;
				}


				while(@list($key,$value)=each($_POST['mixServiceType'])) {
					$arrFieldsList["Service Type $key"] = "$value";
				}

			$arrFieldsList['Request Details'] = $_POST['mixCustomerComments'] . "\n";

			$arrFieldsList['Contact Title'] = $_POST['mixContact_Title'];
			$arrFieldsList['Contact Job Title'] = $_POST['mixContact_JobTitle'];
			$arrFieldsList['Contact First Name'] = $_POST['mixContact_FirstName'];
			$arrFieldsList['Contact Last Name'] = $_POST['mixContact_LastName'];
			$arrFieldsList['Contact Email'] = $_POST['mixContact_Email'];
			$arrFieldsList['Contact Phone'] = $_POST['mixContact_Phone'];
			$arrFieldsList['Contact Mobile'] = $_POST['mixContact_Mobile'];
			$arrFieldsList['Contact Fax'] = $_POST['mixContact_Fax'];
			$arrFieldsList['Contact Address Line 1'] = $_POST['mixAccount_Address1'];
			$arrFieldsList['Contact Address Line 2'] = $_POST['mixAccount_Address2'];
			$arrFieldsList['Contact Suburb'] = $_POST['mixAccount_Suburb'];
			$arrFieldsList['Contact State'] = $_POST['mixAccount_State'];
			$arrFieldsList['Contact Postcode'] = $_POST['mixAccount_Postcode'];
			$arrFieldsList['Contact Country'] = $_POST['mixAccount_Country'];

			list($bolFoundErrors,$strErrorResponse) = InputValidation("Email",$_POST['mixContact_Email'],"email",255);
			if($bolFoundErrors)
			{
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			foreach($arrFieldsList as $key=>$val)
			{
				// remove any unwanted code/bad input. this input is later send via email so need to be clean.
				$val=htmlspecialchars(addslashes($val), ENT_QUOTES);
				$$key=$val;
			}
			$bolFoundSubmit = TRUE;

			if($bolFoundErrors)
			{
				$this->LoadPage('support_errors');
				return TRUE;
			}
			if(array_key_exists('intRequestTypeSubmitFinal',$_POST))
			{
				$subject = "Account Support Request";
				$message = "Details below\n\n";
				foreach($arrFieldsList as $key=>$val)
				{
					$message .= "$key: $val\n";
				}
				$message .= "\nKind Regards\n";
				$message .= "Customer Service Group\n";
				$headers .= 'From: Customer Service Group<' . NOTIFICATION_REPLY_EMAIL . ">\r\n" . 'X-Mailer: Flex/' . phpversion();
				mail("ryanjf@gmail.com", $subject, $message, $headers);
				$this->LoadPage('support_confirmed');
				return TRUE;
			}

			$this->LoadPage('support_confirmation');
			return TRUE;
		}
		if(!$bolFoundSubmit)
		{
			$this->LoadPage('support');
			return TRUE;
		}	 	
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
			
			DBO()->ErrorMessage = "";
			$mixFoundError = FALSE;
			if($_POST['mixAccount_NewPassword1'] != "" || $_POST['mixAccount_NewPassword2'] != "")
			{
				if(SHA1($_POST['mixAccount_OldPassword']) != DBO()->Contact->PassWord->Value)
				{
					DBO()->ErrorMessage .= "Invalid input: Old password does not match<br/>";
					$mixFoundError = TRUE;
				}
				if(strlen($_POST['mixAccount_NewPassword1'])>"40" || strlen($_POST['mixAccount_NewPassword1'])<"6")
				{
					DBO()->ErrorMessage .= "Invalid input: New password length is wrong. max = 40, min = 6<br/>";
					$mixFoundError = TRUE;
				}
				if($_POST['mixAccount_NewPassword1'] != $_POST['mixAccount_NewPassword2'])
				{
					DBO()->ErrorMessage .= "Invalid input: New passwords do not match<br/>";
					$mixFoundError = TRUE;
				}
			}

			/* check email */
			list($strFoundError,$strErrorResponse) = InputValidation("Email",$_POST['mixContact_Email'],"email",255);
			if($strFoundError)
			{
				$mixFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}

			// Connect to database
			$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
			$strCustContactEmail = $dbConnection->fetchone("SELECT Id,Email FROM `Contact` WHERE Email = \"$_POST[mixContact_Email]\" AND Id != \"" . DBO()->Contact->Id->Value . "\" LIMIT 1");
			// Check for duplicate email being used...
			if($strCustContactEmail)
			{
				$mixFoundError=TRUE;
				DBO()->ErrorMessage .= "The email address entered already exists.<br/>";
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
			// remove any code from input. no reason why customer would need to use <
			foreach($_POST as $key=>$val)
			{
				$_POST[$key]=str_replace("<","&lt;",$val);
			}
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
		$strWelcome = "<div class=\"welcome_message\">Welcome " . DBO()->Contact->FirstName->Value . ". Last Login: $strShowLastLogin</div>\n";
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
	// Password
	//------------------------------------------------------------------------//
	/**
	 * Password()
	 *
	 * Resends the users password to the primary email.
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Password()
	{
		
		// eventually user will not even see a flex login page, so this page will need to be separate.
		// Password() - doesn't seem to work as expected but does work.
		error_reporting(0);

		// Connect to database
		$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
		
		// Check if the form has been submitted.
		if(array_key_exists('mixUserName', $_POST))
		{
			// By default all password requests will fail.
			DBO()->Fail = TRUE;

			// Check the syntax of the username entered by user..
			$mixInput = $_POST['mixUserName'];
			list($strFoundError,$strErrorResponse) = InputValidation("UserName",$mixInput,"email",255);

			// If there is no UserName errror
			if(!$strFoundError)
			{
				//then we can check the database for a record.
				$strCustEmail = $dbConnection->fetchone("SELECT Id,Email,Account,LastLogin FROM `Contact` WHERE Email = \"$mixInput\" LIMIT 1");

				/* Before we can send the username, check and make sure they have already activated and entered a valid email */
				if($strCustEmail->LastLogin == NULL && $strCustEmail->Id != NULL)
				{
					/* if they don't have an activated account we redirect to activation page */
					unset($_POST['mixFirstName']);
					$bolFoundError=TRUE;
					$this->LoadPage('setup_account');
					return TRUE;
				}

				// if the email address exists in db then we reset the pass..
				if($strCustEmail->Email && $bolFoundError==FALSE)
				{
					// Reset password
					$strTxtPassword = RandomString("10");
					$dbConnection->execute("UPDATE `Contact` SET `PassWord` = SHA1( '$strTxtPassword' ) WHERE Email = \"$mixInput\"");

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
	


	//------------------------------------------------------------------------//
	// Username
	//------------------------------------------------------------------------//
	/**
	 * Username()
	 *
	 * Resends the users Username to the primary email.
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Username()
	{
		
		// eventually user will not even see a flex login page, so this page will need to be separate.
		// Password() - doesn't seem to work as expected but does work.
		error_reporting(0);

		// Connect to database
		$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
		
		// Check if the form has been submitted.
		if(array_key_exists('mixFirstName', $_POST))
		{
			// echo "pass 1. " . $_POST['mixFirstName'];
			// By default all password requests will fail.
			DBO()->Fail = TRUE;

			// Check the syntax of the username entered by user..
			$bolFoundError=FALSE;
			list($strFoundError,$strErrorResponse) = InputValidation("mixFirstName",$_POST['mixFirstName'],"mixed",31);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				// echo "fail 1. $strErrorResponse " . $_POST['mixFirstName'];
			}
			list($strFoundError,$strErrorResponse) = InputValidation("mixLastName",$_POST['mixLastName'],"mixed",31);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				// echo "fail 2. $strErrorResponse " . $_POST['mixLastName'];
			}
			list($strFoundError,$strErrorResponse) = InputValidation("mixEmail",$_POST['mixEmail'],"email",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				// echo "fail 3. $strErrorResponse " . $_POST['mixEmail'];
			}

			// If there is no UserName errror
			if(!$bolFoundError)
			{
				//then we can check the database for a record.
				$strCustEmail = $dbConnection->fetchone("SELECT Account,FirstName,LastName,Email,LastLogin FROM `Contact` WHERE Email = \"$_POST[mixEmail]\" LIMIT 1");
				/* Before we can send the username, check and make sure they have already activated and entered a valid email */
				if($strCustEmail->LastLogin == NULL)
				{
					/* if they don't have an activated account we redirect to activation page */
					unset($_POST['mixFirstName']);
					$bolFoundError=TRUE;
					$this->LoadPage('setup_account');
					return TRUE;
				}
				// if the email address exists in db then we reset the pass..
				if($strCustEmail->FirstName == "$_POST[mixFirstName]" && $strCustEmail->LastName == "$_POST[mixLastName]" && $bolFoundError==FALSE)
				{
					// And send an email...
					$to      = $strCustEmail->Email;
					$subject = "Account Notice #" . $strCustEmail->Account;
					$message = "Hello,\n\n";
					$message .= "Your username is: " . $strCustEmail->Email . "\n\n";
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

		$this->LoadPage('resend_username');
		return TRUE;
	}


	/* 
	 * Function Setup();
	 *
	 * Function sets a new email address and allows user to set his/her password for the first time 
	 * This function will only work if they have not logged in before, otherwise we assume this has already been done...
	 */
	function Setup()
	{
		error_reporting(0);

		// Connect to database
		$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
		
		// Check if the form has been submitted.
		if(array_key_exists('mixFirstName', $_POST))
		{
			// By default all password requests will fail.
			DBO()->Fail = TRUE;
			DBO()->ErrorMessage = "";

			// Check the syntax of the username entered by user..
			$bolFoundError=FALSE;
			list($strFoundError,$strErrorResponse) = InputValidation("First Name",$_POST['mixFirstName'],"mixed",31);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			list($strFoundError,$strErrorResponse) = InputValidation("Last Name",$_POST['mixLastName'],"mixed",31);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			list($strFoundError,$strErrorResponse) = InputValidation("Account Number",$_POST['mixAccountNumber'],"numbers",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			list($strFoundError,$strErrorResponse) = InputValidation("Birth Day",$_POST['mixBirthDay'],"numbers",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			list($strFoundError,$strErrorResponse) = InputValidation("Birth Month",$_POST['mixBirthMonth'],"numbers",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			list($strFoundError,$strErrorResponse) = InputValidation("Birth Year",$_POST['mixBirthYear'],"numbers",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			list($strFoundError,$strErrorResponse) = InputValidation("ABN",$_POST['mixABN'],"numbers",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			if($_POST['mixNewPass1'] != $_POST['mixNewPass2'])
			{
				$bolFoundError = TRUE;
				DBO()->ErrorMessage .= "Invalid input for password fields, passwords do not match<br/>";
			}
			if(strlen($_POST['mixNewPass1'])>"40" || strlen($_POST['mixNewPass1'])<"6")
			{
				$bolFoundError = TRUE;
				DBO()->ErrorMessage .= "Invalid input: password must be greater then 6 chars and less then 40.<br/>";
			}

			// If there is no UserName errror
			if(!$bolFoundError)
			{

				// we can check the database for a record. 1
				// Since there is duplicate account numbers we check there first name...?
				$strCustContact = $dbConnection->fetchone("SELECT Id,FirstName,LastName,DOB,LastLogin,Email,Account FROM `Contact` WHERE Account = \"$_POST[mixAccountNumber]\" AND FirstName LIKE \"$_POST[mixFirstName]\" AND LastName LIKE \"$_POST[mixLastName]\" LIMIT 1");
				
				// we can check the database for a record. 2
				$strCustAccount = $dbConnection->fetchone("SELECT ABN FROM `Account` WHERE Id = \"$_POST[mixAccountNumber]\" LIMIT 1");

				if($strCustContact->LastLogin != NULL)
				{
					// they have logged in before, print error message or redirect, or both!.
					DBO()->ErrorMessage .= "Error you have already setup your account, if you forget your password: <a href=\"" . Href()->ResetPassword() . "\">go here</a>" . "<br/>";

				}
				else if($strCustContact->FirstName == "$_POST[mixFirstName]" && $strCustContact->LastName == "$_POST[mixLastName]" && $strCustContact->DOB == "$_POST[mixBirthYear]-$_POST[mixBirthMonth]-$_POST[mixBirthDay]" && $strCustAccount->ABN == "$_POST[mixABN]")
				{
					DBO()->Fail = FALSE;
					DBO()->Contact->Id = $strCustContact->Id;
					DBO()->Contact->Email = $strCustContact->Email;
					DBO()->Contact->FirstName = $strCustContact->FirstName;
					DBO()->Contact->LastName = $strCustContact->LastName;
					DBO()->Contact->DOB = $strCustContact->DOB;
					DBO()->Account->ABN = $strCustAccount->ABN;
					DBO()->Contact->Account = $strCustContact->Account;
					DBO()->OK = TRUE;
				}
				else
				{
					DBO()->ErrorMessage .= "Invalid input: not all details matched.<br>";
				}
				
			}

			/* they have submitted the first page */
			if(DBO()->OK && DBO()->Fail==FALSE && array_key_exists('mixEmail', $_POST))
			{
				/* if DBO()->OK then we have confirmed all details, we just need to verify the email */
				list($bolFoundEmail,$strErrorResponse) = InputValidation("Email",$_POST['mixEmail'],"email",255);

				// we allow a duplicate email, only if its there's...
				$intIdCheck = DBO()->Contact->Id->Value;
				$strCustContact = $dbConnection->fetchone("SELECT Id,Email FROM `Contact` WHERE Email = \"$_POST[mixEmail]\" AND Id != \"$intIdCheck\" LIMIT 1");
				if($strCustContact)
				{
					$bolFoundEmail=TRUE;
					DBO()->Fail = TRUE;
					DBO()->ErrorMessage .= "The email address entered already exists.<br/>";
					$this->LoadPage('setup_account');
					return TRUE;
				}
				if($bolFoundEmail)
				{
					DBO()->Fail = TRUE;
					DBO()->ErrorMessage .= "$strErrorResponse<br/>";
					$this->LoadPage('setup_account');
					return TRUE;
				}
				if(!$bolFoundEmail)
				{
					$mixNewPass = sha1 ("$_POST[mixNewPass1]");
					$mixNewEmail = "$_POST[mixEmail]";

					$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
					$dbConnection->execute("UPDATE Contact SET PassWord=\"$mixNewPass\",Email=\"$mixNewEmail\" WHERE Id=\"" . DBO()->Contact->Id->Value . "\"");
					
					/* Mail the user with there new password and username, then show thank you page.. */
					$subject = "Account Setup" . DBO()->Contact->Account->Value;
					$message = "Hello,\n\n";
					$message .= "Your username is: " . DBO()->Contact->Email->Value . "\n";
					$message .= "Your password is: " . $_POST['mixNewPass1'] . "\n\n";
					$message .= "Kind Regards\n";
					$message .= "Customer Service Group\n";
					$headers .= 'From: Customer Service Group<' . NOTIFICATION_REPLY_EMAIL . ">\r\n" .
						'X-Mailer: Flex/' . phpversion();
					mail($mixNewEmail, $subject, $message, $headers);
					$this->LoadPage('setup_completed');
					return TRUE;
				}
			}

			// not found in db?
			if(DBO()->Fail)
			{
				// Brute Force attack prevention.
				sleep(9);
			}
		}

		$this->LoadPage('setup_account');
		return TRUE;
	}
	
}
