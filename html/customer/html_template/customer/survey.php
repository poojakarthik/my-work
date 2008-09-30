<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * HTML Template object for the Account Details
 *
 * HTML Template object for the Account Details
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
 


 class HtmlTemplateCustomerSurvey extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	 
	function Render()
	{

		echo "<div class='customer-standard-display-title'>&nbsp;</div><br/><br/>";

		if(DBO()->Survey->Form->Value !== NULL)
		{

			$arrInputTypes = array(
				"select" => "<select [REPLACE]>", 
				"text" => "<input type='text' [REPLACE]>", 
				"textarea" => "<textarea [REPLACE]>",
				"checkbox" => "<input type='checkbox' [REPLACE]>"
			);

			$arrEndInputTypes = array(
				"select" => "</select>", 
				"text" => "", 
				"textarea" => "</textarea>",
				"checkbox" => ""
			);
			$arrInputDropDown = array(
				"select" => "<option value='[REPLACE]'>[REPLACE]</option>", 
				"text" => "", 
				"textarea" => "",
				"checkbox" => ""
			);

			$mixOutPut .= "";
			$arrNumbers = array();
			$intCount = 0;
			$intSubCount = 0;
			foreach(DBO()->Survey->Form->Value as $results)
			{
				foreach($results as $key=>$val){
					$$key=$val;
				}

				$mixQuestionStart = "
				<div class='customer-standard-table-title-style-password'>$question</div>
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
					if($val == "$question_id")
					{
						$bolFound = TRUE;
					}
				}

				// It doesnt exist, it's a main item, e.g. <select> or <input text>
				if(!$bolFound)
				{
					if(count($arrNumbers)>1)
					{
						$mixOutPut .= "$arrEndInputTypes[$response_type]" . "\n";
						$mixOutPut .= "$mixQuestionEnd"; // End
						$intSubCount=0;
					}
					$mixOutPut .= "$mixQuestionStart"; // Start
					$intSubCount++;
					##################################
					# put main item here
					##################################
					switch($response_type)
					{
						case "select":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$question_id||$id||$response_required]'","$arrInputTypes[$response_type]");
						$mixOutPut .= str_replace("[REPLACE]","$option_name","$arrInputDropDown[$response_type]");
						break;

						case "checkbox":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$question_id||$id||$response_required]' value='$option_name'","$arrInputTypes[$response_type]");
						$mixOutPut .= " $option_name<br>";
						break;

						default:
						break;
					}
					##################################
					//$mixOutPut .= "$intSubCount. $response_type $question_id<br>"; // for debug only..
				}
				$intCount++;
				$arrNumbers[$intCount] = "$question_id";

				// already exists, its a sub item. e.g. <option value=>
				if($bolFound)
				{
					$intSubCount++;
					##################################
					# put sub item here
					##################################
					switch($response_type)
					{
						case "select":
						$mixOutPut .= str_replace("[REPLACE]","$option_name","$arrInputDropDown[$response_type]");
						break;
						
						case "checkbox":
						$mixOutPut .= str_replace("[REPLACE]","name='arrAnswer[$question_id||$id||$response_required]' value='$option_name'","$arrInputTypes[$response_type]") . " $option_name<br>";
						break;
						
						case "textarea":
						break;
						
						case "text":
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
				$mixOutPut .= "$mixQuestionEnd";
			}

			echo "
			<div class='customer-standard-table-title-style-confirm-details'>$title</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td>Please complete all fields below</td>
			</tr>
			</table>
			</div><br/>";
			echo "<form method=\"POST\" action=\"./flex.php/Console/Survey/\">";
			echo "<input type=\"hidden\" name=\"intSurveyId\" value=\"$survey_id\">";
			echo $mixOutPut;
			echo "
			<br/>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
				<TD align=right><INPUT TYPE=\"button\" VALUE=\"Cancel\" onclick=\"javascript:document.location = './'\"> <INPUT TYPE=\"submit\" VALUE=\"Submit Survey\"></TD>
			</TR>
			</TABLE>
			<div id=\"error_box\"></div>";
			echo "</form>";
		}
		else if(DBO()->Survey->Results->Value == TRUE)
		{
			echo "
			<div class='customer-standard-table-title-style-confirmation'>Confirmation</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td>Thank you for your valuable feedback, the survey has now been completed.</td>
			</tr>
			</table>
			</div><br/>";
		}
		else if(DBO()->Survery->Error->Value !== NULL)
		{
			echo "
			<div class='customer-standard-table-title-style-notice'>Failure notice</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td>" . DBO()->Survery->Error->Value . "Please return and correct the errors, <A HREF=\"javascript:history.go(-1)\">click here</A>.</td>
			</tr>
			</table>
			</div><br/>";
		}
		else
		{
			echo "
			<div class='customer-standard-table-title-style-notice'>Failure notice</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td>Unfortunately, no survey data can be viewed.</td>
			</tr>
			</table>
			</div><br/>";
		}
	}
}

?>
