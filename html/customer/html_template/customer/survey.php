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

		$arrInputTypes = array(
			"select" => "<select name='[question]'>", 
			"text" => "<input type='text' name='[question]'>", 
			"textarea" => "<textarea name='[question]'>",
			"checkbox" => "<input type='checkbox' name='[question]'>"
		);

		$arrEndInputTypes = array(
			"select" => "</select>", 
			"text" => "", 
			"textarea" => "</textarea>",
			"checkbox" => ""
		);
		$arrInputDropDown = array(
			"select" => "<option value='[option]'>[option]</option>", 
			"text" => "", 
			"textarea" => "",
			"checkbox" => ""
		);

		$mixOutPut .= "";
		$arrNumbers = array();
		$intCount = 0;
		$intSubCount = 0;
		foreach(DBO()->Survey->Results->Value as $results)
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
					$mixOutPut .= "$mixQuestionEnd"; // End
					$intSubCount=0;
				}
				$mixOutPut .= "$mixQuestionStart"; // Start
				$intSubCount++;
				##################################
				# put main item here
				##################################
				$mixOutPut .= "$intSubCount. $response_type $question_id<br>";
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
				$mixOutPut .= "$intSubCount. $response_type $question_id<br>";
			}
			switch($response_type)
			{
				case "select":
				break;
				case "checkbox":
				break;
				case "textarea":
				break;
				case "text":
				break;
			}

		}
		// it will never end itself..
		if(count($arrNumbers)>1)
		{
			$mixOutPut .= "$mixQuestionEnd";
		}

		echo "<div class='customer-standard-display-title'>&nbsp;</div><br/><br/>";

		echo "
		<div class='customer-standard-table-title-style-confirm-details'>$title</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td>Please complete all fields below</td>
		</tr>
		</table>
		</div><br/>";

		echo $mixOutPut;

	}
}

?>
