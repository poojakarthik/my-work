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
		

		$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);

		if(!isset($_GET['start']) || !is_numeric($_GET['start']))
		{
			$_GET['start']="0";
		}

		// This portion of the code builds the search query.
		$intStart = $_GET['start'];
		$intResultsPerPage = "10";
		$mixSearch = $_GET['s'];

		// view single faq
		if(array_key_exists('view',$_GET))
		{
			$intView = 0;
			$arrFAQResults = NULL;
			if(is_numeric($_GET['view']))
			{
				$intView = $_GET['view'];
				$strSelect= "SELECT * FROM customer_faq WHERE id = \"$intView\" AND customer_group_id=\"" . DBO()->Account->CustomerGroup->Value . "\"";
				$dbConnection->execute("UPDATE `customer_faq` SET hits=hits+1 WHERE id = \"$intView\"");
				$arrFAQResults = $dbConnection->fetchone("$strSelect");
			}
			// Return an array with results to our page.
			DBO()->FAQ->View = $arrFAQResults;
			$this->LoadPage('faq_view');
			return TRUE;	
		}

		// view all faqs
		if (array_key_exists('all',$_GET))
		{

			$select= "SELECT SQL_CALC_FOUND_ROWS * FROM customer_faq WHERE customer_group_id=\"" . DBO()->Account->CustomerGroup->Value . "\" ORDER BY hits DESC LIMIT $intStart,$intResultsPerPage";
			// This portion of the code exeutes the query fetching an array..
			$arrCustomerFAQ = $dbConnection->fetch("$select",$array=true);

			// Count how many results there are, this can be used for pagination.
			$resCountResults = $dbConnection->execute("SELECT FOUND_ROWS()");
			list($intTotalResults) = $dbConnection->fetch_array($resCountResults);

			// links 
			list($intNext,$mixLinksDisplay) = MakePagination($intStart,$intResultsPerPage,$intTotalResults,"./flex.php/Console/FAQ/?all=1");

			DBO()->Total->Search = "$intTotalResults";
			DBO()->Total->Start = "$intStart";
			DBO()->Total->NextPage = $intNext;
			DBO()->Search->Pages = "$mixLinksDisplay";
			DBO()->FAQ->All = $arrCustomerFAQ;

			$this->LoadPage('faq_all');
			return TRUE;	
		}

		// search faqs
		if(array_key_exists('s',$_GET))
		{

			$mixSelect = "
				SELECT SQL_CALC_FOUND_ROWS * ,
				MATCH (
				title, contents
				)
				AGAINST (
				'title'
				IN BOOLEAN
				MODE
				) AS rank
				FROM customer_faq
				WHERE customer_group_id =2
				AND MATCH (
				title, contents
				)
				AGAINST (
				\"$mixSearch\"
				IN BOOLEAN
				MODE
				) >=0
				HAVING (
			";

			$splitted = split ('[ +]', $mixSearch);

			$count=0;
 			$bolFoundWord = FALSE;
			foreach($splitted as $word){
				if($count != "0"&&strlen($word)>="2"){
					$mixSelect.= " OR ";
				}
				if(strlen($word)>="2"){
					$bolFoundWord = TRUE;
					$mixSelect.="title LIKE '%$word%' OR contents LIKE '%$word%'";
					$count++;
				}
			}
			// By default we show all results if the search term is less then 2 chars.
			if(!$bolFoundWord)
			{
				// todo error.
				$mixSelect.="title LIKE '%' OR contents LIKE '%'";
			}



			$mixSelect .= ")
				ORDER BY rank DESC
				LIMIT $intStart,$intResultsPerPage;";
			
			// debug query
			// echo "$mixSelect";
			
			// This portion of the code exeutes the query fetching an array..
			$strCustomerFAQ = $dbConnection->fetch("$mixSelect",$array=true);

			// Count how many results there are, this can be used for pagination.
			$resCountResults = $dbConnection->execute("SELECT FOUND_ROWS()");
			list($intTotalResults) = $dbConnection->fetch_array($resCountResults);

			// links 
			list($intNext,$mixLinksDisplay) = MakePagination($intStart,$intResultsPerPage,$intTotalResults,"./flex.php/Console/FAQ/?s=");

			// Return an array with results to our page.
			DBO()->Search->Result = $strCustomerFAQ;
			DBO()->Total->Search = "$intTotalResults";
			DBO()->Total->Start = "$intStart";
			DBO()->Total->NextPage = $intNext;
			DBO()->Search->Pages = "$mixLinksDisplay";
			//echo "<hr>$intNext,$mixLinksDisplay,$intStart,$intResultsPerPage,$intTotalResults<hr>";
			$this->LoadPage('faq');
			return TRUE;	

		}

	
		// show top ten
		$select= "SELECT * FROM customer_faq WHERE customer_group_id=\"" . DBO()->Account->CustomerGroup->Value . "\" ORDER BY hits DESC LIMIT 10";
		// This portion of the code exeutes the query fetching an array..
		$strCustomerTop10 = $dbConnection->fetch("$select",$array=true);

		// Return an array with results to our page.
		DBO()->Search->Topten = $strCustomerTop10;
		$this->LoadPage('faq');
		return TRUE;
		
	 }





	 // User Survey
	 function Survey()
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
		BreadCrumb()->SetCurrentPage("Customer Survey");

		DBO()->Survey->Form = NULL;
		DBO()->Survey->Error = NULL;
		DBO()->Survey->Results = FALSE;

		$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);

		// Survey form has not been submitted. run the query required to build the page.
		if(!array_key_exists('intSurveyId',$_POST))
		{

			$mixDate = date("Y-m-d H:i:s", time());
			$mixSelect = "
			SELECT *
			FROM survey 
			WHERE id NOT IN (	SELECT survey_id
						FROM survey_completed
						WHERE contact_id = \"" . DBO()->Contact->Id->Value . "\"
					)
			AND customer_group_id = \"" . DBO()->Account->CustomerGroup->Value . "\"
			ORDER BY id ASC 
			LIMIT 1";
			$arrSurvey = $dbConnection->fetchone("$mixSelect");
			if($arrSurvey)
			{
				foreach($arrSurvey as $key=>$val)
				{
					$$key=$val;
				}
			}
			$mixSelect = "SELECT sq.*, sqo.*
			FROM survey_question AS sq 
			INNER JOIN survey_question_option AS sqo 
			ON sq.id = sqo.survey_question_id
			WHERE sq.survey_id = \"$id\"
			ORDER BY sqo.survey_question_id,sqo.survey_question_option_response_type_id";
			// DEBUG
			// echo "$mixSelect";
			$arrSurvey = $dbConnection->fetch("$mixSelect",$array=true);

			$arrInputTypes = array(
				"1" => "<select [REPLACE]>", 
				"2" => "<input type='checkbox' [REPLACE]>",
				"3" => "<input type='radio' [REPLACE]>",
				"4" => "<textarea [REPLACE]>",
				"5" => "<input type='text' [REPLACE]>"
			);

			$arrEndInputTypes = array(
				"1" => "</select>", 
				"2" => "",
				"3" => "",
				"4" => "</textarea>",
				"5" => ""
			);

			$mixOutPut .= "";
			$arrNumbers = array();
			$intCount = 0;
			$intSubCount = 0;

			//var_dump($arrSurvey);
			foreach($arrSurvey as $results)
			{
				foreach($results as $key=>$val){
					$$key=$val;
				}

				$mixQuestionStart = "
				<div class='customer-standard-table-title-style-survey'>[BOX_TITLE]</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
					<TD>";
				$mixQuestionEnd = "
					</TD>
				</TR>
				</TABLE>
				</div>
				<br/>";

				// check if this one exists in array. end it.
				$bolFound = FALSE;
				foreach($arrNumbers as $key=>$val)
				{
					if($val == "$survey_question_id")
					{
						$bolFound = TRUE;
					}
				}
				// It doesnt exist, it's a main item, e.g. <select> or <input text>
				if(!$bolFound)
				{
					if(count($arrNumbers)>1)
					{
						$mixOutPut .= "$arrEndInputTypes[$survey_question_response_type_id]" . "\n";
						$mixOutPut .= "$mixQuestionEnd"; // End
						$intSubCount=0;
					}
					$mixOutPut .= str_replace("[BOX_TITLE]","$question",$mixQuestionStart); // Start
					$intSubCount++;
					##################################
					# put main item here
					##################################
					switch($survey_question_response_type_id)
					{
						case "1":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]'","$arrInputTypes[$survey_question_response_type_id]");
						$mixOutPut .= str_replace("[REPLACE]","$option_name","<option value='[REPLACE]'>[REPLACE]</option>");
						break;

						case "2":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]' value='$option_name'","$arrInputTypes[$survey_question_response_type_id]");
						$mixOutPut .= " $option_name<br>";
						break;

						case "3":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]' value='$option_name'","$arrInputTypes[$survey_question_response_type_id]");
						$mixOutPut .= " $option_name<br>";
						break;

						default:
						break;
					}
					switch($survey_question_option_response_type_id)
					{
						case "4":
						$mixOutPut .= "If $option_name: " . str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]'","$arrInputTypes[$survey_question_option_response_type_id]") . "<br>";
						break;
						case "5":
						$mixOutPut .= "If $option_name: " . str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]'","$arrInputTypes[$survey_question_option_response_type_id]") . "<br>";
						break;

						default:
						break;
					}
					##################################
					//$mixOutPut .= "$intSubCount. $response_type $question_id<br>"; // for debug only..
				}
				$intCount++;
				$arrNumbers[$intCount] = "$survey_question_id";

				// already exists, its a sub item. e.g. <option value=>
				if($bolFound)
				{
					$intSubCount++;
					##################################
					# put sub item here
					##################################
					switch($survey_question_response_type_id)
					{
						case "1":
						$mixOutPut .= str_replace("[REPLACE]","$option_name","<option value='[REPLACE]'>[REPLACE]</option>");
						break;
						
						case "2":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]' value='$option_name'","$arrInputTypes[$survey_question_response_type_id]") . " $option_name<br>";
						break;
						
						case "3":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]' value='$option_name'","$arrInputTypes[$survey_question_response_type_id]");
						$mixOutPut .= " $option_name<br>";
						break;

						case "4":
						break;
						
						case "5":
						break;

						default:
						break;
					}
					switch($survey_question_option_response_type_id)
					{
						case "4":
						$mixOutPut .= "$option_name: " . str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]'","$arrInputTypes[$survey_question_option_response_type_id]") . "<br>";
						break;
						case "5":
						$mixOutPut .= "$option_name: " . str_replace("[REPLACE]","name='arrAnswer[$survey_question_id||$id||$response_required]'","$arrInputTypes[$survey_question_option_response_type_id]") . "<br>";
						break;

						default:
						break;
					}
					//$mixOutPut .= "$intSubCount. $response_type $question_id<br>"; // for debug only..
				}

			}
		
			// it will never end itself..
			if(count($arrNumbers)>1)
			{
				$mixOutPut .= "
					$mixQuestionEnd
				" . str_replace("[BOX_TITLE]","Survey Conditions",$mixQuestionStart) . str_replace("\n","<br>",$conditions) . "
					$mixQuestionEnd";
			}
	
			$mixOutPut .= "<input type=\"hidden\" name=\"intSurveyId\" value=\"$survey_id\">";
			DBO()->Survey->Title = $title;
			DBO()->Survey->Form = $mixOutPut;

		}

		// Survey form has been submitted.
		if(array_key_exists('intSurveyId',$_POST) && is_numeric("$_POST[intSurveyId]"))
		{
			
			// Clean input
			$_POST = CleanFormInput($_POST);

			// Prevent the same survey from being completed twice.
			$arrCheckIfCompleted = $dbConnection->fetch("SELECT * FROM survey_completed WHERE contact_id = \"" . DBO()->Contact->Id->Value . "\" AND survey_id=\"$_POST[intSurveyId]\"",$array=true);
			if($arrCheckIfCompleted)
			{
				DBO()->Survey->Error = "Error, you have already completed this survey, please try again later when a new survey becomes available.";
			}


			// Build an array of our valid responses.
			$arrInput = array();
			$intCounter = 0;

			while(@list($mixKey,$mixVal)=each($_POST['arrAnswer'])) {
				$intCounter++;
				$mixBit = explode("||",$mixKey);
				$arrInput[$intCounter++] = "$mixBit[0]";
			}

			// Select all survey questions so we know what our sql insert statement will look.
			// This portion of the code also uses the results to check which fields are required and matches it against the input.
			$mixSelect = "
			SELECT sq.*, sqo.*
			FROM survey_question AS sq 
			INNER JOIN survey_question_option AS sqo 
			ON sq.id = sqo.survey_question_id
			WHERE sq.survey_id = \"$_POST[intSurveyId]\"
			ORDER BY sqo.survey_question_id,sqo.survey_question_option_response_type_id";
			$arrSurveyTest = $dbConnection->fetch("$mixSelect",$array=true);
			
			$intQueryCount = 0;
			$mixQuerie = array();
			$bolSurveyDebugMode = FALSE;

			if($bolSurveyDebugMode)
			{
			print "
				<TABLE border=1>
				<TR>
					<TD>SQO_id</TD>
					<TD>S_id</TD>
					<TD>response_required</TD>
					<TD>SQ_response_type_id</TD>
					<TD>question_num</TD>
					<TD>SQ_id</TD>
					<TD>option_name</TD>
					<TD>SQO_response_type_id</TD>
					<TD>Answer</TD>
				</TR>";
			}

			$arrNumbers = array();
			$count = 0;
			foreach($arrSurveyTest as $results)
			{
				foreach($results as $key=>$val){
					$$key=$val;
				}


				$mixAnswerInput = $_POST["arrAnswer"]["$survey_question_id||$id||$response_required"];
				if($mixAnswerInput)
				{
					// Its an answer to an option.
					if($survey_question_option_response_type_id)
					{
						$mixQuerie[$intQueryCount++] = "INSERT INTO survey_completed_response(survey_question_id, response_text, survey_completed_id) 
						VALUES (\"$survey_question_id\", \"$option_name\", \"[replace_survey_completed_id]\");";
						$mixQuerie[$intQueryCount++] = "INSERT INTO survey_completed_response_option(survey_question_option_id, option_text, survey_completed_response_id)
						VALUES (\"$id\", \"$mixAnswerInput\", LAST_INSERT_ID());";
						if($bolSurveyDebugMode)
						{
							echo "
							<TR>
								<TD COLSPAN=\"9\">
								INSERT INTO survey_completed_response(survey_question_id, response_text, survey_completed_id) 
								VALUES (\"$survey_question_id\", \"$option_name\", SC_id);<BR>
								INSERT INTO survey_completed_response_option(survey_question_option_id, option_text, survey_completed_response_id)
								VALUES (\"$id\", \"$mixAnswerInput\", LAST_INSERT_ID());<br>\n</TD>
							</TR>";
						}
					}
					// Its an answer
					else
					{
						$mixQuerie[$intQueryCount++] = "INSERT INTO survey_completed_response(survey_question_id, response_text, survey_completed_id) 
						VALUES (\"$survey_question_id\", \"$mixAnswerInput\", \"[replace_survey_completed_id]\");";
						if($bolSurveyDebugMode)
						{
							echo "
							<TR>
								<TD COLSPAN=\"9\">
								INSERT INTO survey_completed_response(survey_question_id, response_text, survey_completed_id) 
								VALUES (\"$survey_question_id\", \"$mixAnswerInput\", SC_id);<br>\n</TD>
							</TR>";
						}
					}
				}
				$bolFound = FALSE;
				foreach($arrNumbers as $key=>$val)
				{
					if($val == "$survey_question_id")
					{
						$bolFound = TRUE;
					}
				}
				if(!$bolFound && $response_required == "1")
				{
					$count++;
					$arrNumbers[$count] = "$survey_question_id";
					if($bolSurveyDebugMode)
					{
						echo "
						<TR>
							<TD COLSPAN=\"9\">Found a question that is required $id.</TD>
						</TR>";
					}
					$bolFoundResponse = FALSE;
					foreach($arrInput as $value)
					{
						if($value == "$survey_question_id")
						{
							$bolFoundResponse = TRUE;
						}
					}
					if(!$bolFoundResponse)
					{
						DBO()->Survey->Error = "Error, you must answer this question:<br>\n$question<br><br>\n";
					}
				}
				if(!$bolFound && $response_required == "0")
				{
					$count++;
					$arrNumbers[$count] = "$survey_question_id";
					if($bolSurveyDebugMode)
					{
						echo "
						<TR>
							<TD COLSPAN=\"9\">Found a question that is NOT required $id.</TD>
						</TR>";
					}
					$bolFoundResponse = FALSE;
					foreach($arrInput as $value)
					{
						if($value == "$id")
						{
							$bolFoundResponse = TRUE;
						}
					}
				}
				if($survey_question_option_response_type_id)
				{
					if($bolSurveyDebugMode)
					{
						echo "
						<TR>
							<TD COLSPAN=\"9\">Found an option  $id.</TD>
						</TR>";
					}
				}
				if($bolSurveyDebugMode)
				{
					echo "
					<TR>
						<TD>$id</TD>
						<TD>$survey_id</TD>
						<TD>$response_required</TD>
						<TD>$survey_question_response_type_id</TD>
						<TD>$question_num</TD>
						<TD>$survey_question_id</TD>
						<TD>$option_name</TD>
						<TD>$survey_question_option_response_type_id</TD>
						<TD>$mixAnswerInput</TD>
					</TR>";
				}
			}
			if($bolSurveyDebugMode)
			{
				print "
				</TABLE>";
			}
			if(DBO()->Survey->Error->Value == NULL)
			{

				// Add one record to survey completed table.
				$dbConnection->execute("INSERT INTO survey_completed(date_completed,contact_id,survey_id) VALUES(\"" . date("Y-m-d H:i:s", time()) . "\",\"" . DBO()->Contact->Id->Value . "\",\"$_POST[intSurveyId]\")");
				$intLastId = $dbConnection->fetchone("SELECT LAST_INSERT_ID();");
				foreach($intLastId as $intVal)
				{
					$intLastValue = $intVal;
				}

				// Add multiple records to survey_completed_response, survey_completed_response_option tables.
				foreach($mixQuerie as $intQueryNumber=>$mixQuery)
				{
					if(is_numeric($intLastValue))
					{
						// This replacement is only for querys related to survey_completed_response.
						$mixQuery = str_replace("[replace_survey_completed_id]","$intLastValue",$mixQuery);
						$dbConnection->execute("$mixQuery");
					}
				}

				// all fields have been verified, no errors!.
				DBO()->Survey->Results = TRUE;

			}

		}
		$this->LoadPage('survey');
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

		// Clean input
		$_POST = CleanFormInput($_POST);

		// show the second page - not adding new service.
		if(array_key_exists('intRequestTypeSubmit',$_POST))
		{

			// Temporarily putting this here until its moved to a better placed.
			// Configuration for the support page.
			$arrSupportConfig['SupportType'][1]['Description'] = 'Logging a fault to an existing service';
			$arrSupportConfig['SupportType'][2]['Description'] = 'Make a change to an existing service';
			$arrSupportConfig['SupportType'][3]['Description'] = 'Disconnect a no longer required line number';
			$arrSupportConfig['SupportType'][4]['Description'] = 'Add a new line';
			$arrSupportConfig['SupportType'][5]['Description'] = 'Other';

			$bolFoundSubmit = TRUE;
			$arrFieldsList = NULL;
			$arrFieldsList = array();
			$arrFieldsList['Request Type'] = $arrSupportConfig['SupportType'][$_POST['intRequestType']]['Description'] . "\n";
			
				switch($_POST['intRequestType'])
				{
					case "1":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["FNN $key"] = "$value";
					}
					break;

					case "2":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["FNN $key"] = "$value";
					}
					$arrFieldsList['Diversions Required'] = $_POST['intDiversionsRequired'];
					$arrFieldsList['Diversion From Number'] = $_POST['intDiversionFromNumber'];
					$arrFieldsList['Diversion To Number'] = $_POST['intDiversionToNumber'] . "\n";
					break; 

					case "3":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["FNN $key"] = "$value";
					}
					break; 
					
					case "4":
						
						// Below options are used in multiple service types.
						$arrFieldsList['How many new lines'] = $_POST['mixTotalLines'];
						$arrFieldsList['Requested Install Date'] = $_POST['mixInstallDate'] . "\n";
						$arrFieldsList['Do you allow Telstra to connect your line past the Main Distribution Frame (MDF) and charge fee for service?'] = $_POST['mixContractorOptionOne'];
						$arrFieldsList['Private Contractor Name'] = $_POST['mixContractorName'];
						$arrFieldsList['Private Contractor Number'] = $_POST['mixContractorNumber'];
						$arrFieldsList['Existing line number'] = $_POST['mixPhoneNumber'];
						$arrFieldsList['If you are crossing Telephone Exchange boundaries and are unable to keep your current numbers, do you allow us to connect a new service for you.'] = $_POST['mixRelocationOption'];
						$arrFieldsList['Do you require redirections'] = $_POST['mixRedirectionOption'];
						$arrFieldsList['Redirection Setup'] = $_POST['mixRedirectionPlan'];
						$arrFieldsList['Redirection Announcement'] = $_POST['mixRedirectionAnnouncement'] . "\n";

						// 100 - DSL
						if($_POST['mixServiceType'] == "100")
						{
							$arrFieldsList['DSL Setup'] = $_POST['mixDSLSetup'];
							$arrFieldsList['DSL Existing Connection'] = $_POST['mixDSLExistingConnection'];
							$arrFieldsList['DSL Current Provider'] = $_POST['mixDSLCurrentProvider'];
							$arrFieldsList['DSL Current Account'] = $_POST['mixDSLCurrentAccount'];
							$arrFieldsList['DSL New Plan'] = $_POST['mixDSLNewPlan'];
							$arrFieldsList['DSL Phone Number'] = $_POST['mixDSLPhoneNumber'] . "\n";
						}
						// 101 - Mobile
						if($_POST['mixServiceType'] == "101")
						{
							$arrFieldsList['Mobile Setup'] = $_POST['mixMobileSetup'];
							$arrFieldsList['Mobile Number'] = $_POST['mixMobileNumber'];
							$arrFieldsList['Mobile Current Provider'] = $_POST['mixMobileCurrentProvider'];
							$arrFieldsList['Mobile Current Account'] = $_POST['mixMobileCurrentAccount'];
							$arrFieldsList['Mobile New Plan'] = $_POST['mixMobileNewPlan'] . "\n";
						}
						// 102 - Landline
						if($_POST['mixServiceType'] == "102")
						{
							$arrFieldsList['Landline Setup'] = $_POST['mixLandlineSetup'];
							$arrFieldsList['Landline PSTN Message Bank'] = $_POST['mixLandlinePSTNMessageBank'];
							$arrFieldsList['Landline PSTN Line Hunt'] = $_POST['mixLandlinePSTNLineHunt'];
							$arrFieldsList['Landline PSTN Caller Id'] = $_POST['mixLandlinePSTNCallerId'];
							$arrFieldsList['Landline PSTN Fax Duet'] = $_POST['mixLandlinePSTNFaxDuet'];
							$arrFieldsList['Landline PSTN Fax Stream'] = $_POST['mixLandlinePSTNFaxStream'];
							$arrFieldsList['Landline ISDN Indial Range'] = $_POST['mixLandlineISDNIndialRange'];
							$arrFieldsList['Landline ISDN Caller Id'] = $_POST['mixLandlineISDNCallerId'];
							$arrFieldsList['Landline ISDN On Ramp'] = $_POST['mixLandlineISDNOnRamp'] . "\n";
						}
						// 103 - Inbound
						if($_POST['mixServiceType'] == "103")
						{
							$arrFieldsList['Inbound Setup'] = $_POST['mixInboundSetup'];
							$arrFieldsList['Inbound Current Account'] = $_POST['mixInboundCurrentAccount'];
							$arrFieldsList['Inbound Answering Point'] = $_POST['mixInboundAnsweringPoint'];
							$arrFieldsList['Inbound New Plan'] = $_POST['mixInboundNewPlan'] . "\n";
						}
					break; 
					
					case "5":
					while(@list($key,$value)=each($_POST['intFaultLine'])) {
						$arrFieldsList["FNN $key"] = "$value";
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
			if($bolFoundErrors)
			{
				$this->LoadPage('support_errors');
				return TRUE;
			}
			if(array_key_exists('intRequestTypeSubmitFinal',$_POST))
			{

				$message = "Details below\n\n";

				$message .= "Account: " . DBO()->Account->Id->Value . "\n";
				foreach($arrFieldsList as $key=>$val)
				{
					$message .= "$key: $val\n";
				}
				$message .= "\nKind Regards\n";
				$message .= "Customer Service Group\n";
				
				
				$email = new Email_Notification(EMAIL_NOTIFICATION_SUPPORT_FORM, DBO()->Account->CustomerGroup->Value);
				$email->setFrom("$_POST[mixContact_Email]", "$_POST[mixContact_FirstName] $_POST[mixContact_LastName]");
				$email->setSubject("Account Support Request - " . DBO()->Contact->Account->Value);
				$email->setBodyText("$message");
				$email->send();
				
				// for debug only.
				// mail("DEBUG-ADDRESS","TEST FORM DATA","$message");

				$this->LoadPage('support_confirmed');
				return TRUE;

			}

			$this->LoadPage('support_confirmation');
			return TRUE;
		}

		// show the main dropdown menu, no input detected
		if(!$bolFoundSubmit)
		{
			if(array_key_exists('intRequestType',$_POST) && array_key_exists('mixServiceType',$_POST))
			{
				// check if the user selected "Add a new line"
				if($_POST['intRequestType'] == "4" && is_numeric($_POST['mixServiceType']))
				{
					$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);

					// build a list of the available plans for the service type selected
					$arrCustomerPlansResult = $dbConnection->fetch("SELECT * FROM RatePlan WHERE customer_group = \"" . DBO()->Account->CustomerGroup->Value . "\" AND ServiceType=\"$_POST[mixServiceType]\" ORDER BY Name",$array=true);
					DBO()->CustomerPlans->ListPlans = $arrCustomerPlansResult;
				}
			}
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
			
			try
			{
				TransactionStart();
				DBO()->Account->Address1		= $_POST['mixAccount_Address1'];
				DBO()->Account->Address2		= $_POST['mixAccount_Address2'];
				DBO()->Account->Suburb			= $_POST['mixAccount_Suburb'];
				DBO()->Account->State			= $_POST['mixAccount_State'];
				DBO()->Account->Postcode		= $_POST['mixAccount_Postcode'];
				DBO()->Account->BillingMethod	= $_POST['mixAccount_BillingMethod'];
				DBO()->Account->Country			= $_POST['mixAccount_Country'];
				DBO()->Account->SetColumns("Address1,Address2,Suburb,State,Postcode,BillingMethod,Country");
				if (!DBO()->Account->Save())
				{
					throw new Exception("Could not save changes to account details");
				}
				
				// Record the change of state in the account_history table
				Account_History::recordCurrentState(DBO()->Account->Id->Value, NULL, GetCurrentISODateTime());
				
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
				if (!DBO()->Contact->Save())
				{
					throw new Exception("Could not save changes to contact details");
				}

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
				$headers .= 'From: Customer Service Group<noreply@' . DBO()->CustomerGroup->email_domain->Value . ">\r\n" .
					'X-Mailer: Flex/' . phpversion();
				# supress email errors.
				@mail($strOldEmailAddress, $subject, $message, $headers);

				TransactionCommit();
				$this->LoadPage('edit_successful');
				return TRUE;
			}
			catch (Exception $e)
			{
				TransactionRollback();
				DBO()->Error->Message = $e->getMessage();
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
					$headers .= 'From: Customer Service Group<noreply@' . DBO()->CustomerGroup->email_domain->Value . ">\r\n" .
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
					$headers .= 'From: Customer Service Group<noreply@' . DBO()->CustomerGroup->email_domain->Value . ">\r\n" .
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
			/*
			list($strFoundError,$strErrorResponse) = InputValidation("ABN",$_POST['mixABN'],"numbers",255);
			if($strFoundError)
			{
				$bolFoundError=TRUE;
				DBO()->ErrorMessage .= "$strErrorResponse<br/>";
			}
			*/
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


				$intCustomerEnteredABN = $_POST['mixABN'];
				$intABNInDB = $strCustAccount->ABN;

				/* 
				 * Format the ABN in the database and the users input so it contains numbers only! no spaces or dashs..
				 *
				 * Because not all customers have an abn: 
				 * 1. Its not required they enter an abn
				 * 2. So no level of security is lost, whatever they enter must match whatever is in the database. e.g. blank or a number.
				 *
				 */
				$intCustomerEnteredABN = ereg_replace("[^0-9]", "", $intCustomerEnteredABN);
				$intABNInDB = ereg_replace("[^0-9]", "", $intABNInDB);

				if($strCustContact->LastLogin != NULL)
				{
					// they have logged in before, print error message or redirect, or both!.
					DBO()->ErrorMessage .= "Error you have already setup your account, if you forget your password: <a href=\"" . Href()->ResetPassword() . "\">go here</a>" . "<br/>";

				}
				else if($strCustContact->FirstName == "$_POST[mixFirstName]" && $strCustContact->LastName == "$_POST[mixLastName]" && $strCustContact->DOB == "$_POST[mixBirthYear]-$_POST[mixBirthMonth]-$_POST[mixBirthDay]" && $intABNInDB == "$intCustomerEnteredABN")
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
					$headers .= 'From: Customer Service Group<noreply@' . DBO()->CustomerGroup->email_domain->Value . ">\r\n" .
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
				//sleep(9);
			}
		}

		$this->LoadPage('setup_account');
		return TRUE;
	}
	
}
