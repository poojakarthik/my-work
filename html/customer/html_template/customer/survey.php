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
		$intLoopStarted = FALSE;
		$IntQuestionId = NULL; // by default, no question is selected
		$arrSurveyCount = array();
		$intQuestionNum=NULL;
		$numbers = array();
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
			
			$mixQuestionEnd .= "
				</TD>
			</TR>
			</TABLE>
			</div>
			<br/>";

			$foo = $numbers[$question_id];
			if($foo=="")
			{
				echo "S$question_id";
				$numbers[$question_id]="$question_id";
				//$mixOutPut .= "$mixQuestionStart";
			}

			$count++;
			$mixCheckBoxStart = "";
			$mixCheckBoxEnd = "";
			

			if($intQuestionNum == NULL)
			{
				$mixOutPut .= "<br>START $question_id<br>";
			}
			if($intQuestionNum !== NULL && $intQuestionNum !== $question_id)
			{
				$mixOutPut .= "<br>END $question_id<br><br>START $question_id<br>";
			}

			if($response_type == "checkbox")
			{
				$mixCheckBoxStart = "";
				$mixCheckBoxEnd = "$option_name<br>";	
			}
			// First Item
			if($IntQuestionId == NULL)
			{
				$mixOutPut .= "" . "debug: 1" . str_replace("[question]","strAnswer[$question_id]","$arrInputTypes[$response_type]") . "$mixCheckBoxEnd\n";				
				$intLoopStarted = TRUE;
			}
			if($IntQuestionId == "$question_id" || $IntQuestionId == NULL)
			{
				if($response_type == "checkbox")
				{
					$mixOutPut .= "
					debug: 2" . "$mixCheckBoxStart" . str_replace("[question]","arrAnswer[$id]","$arrInputTypes[$response_type]") . "$mixCheckBoxEnd\n";
				}
				$mixOutPut .= "debug: 3" . str_replace("[option]","$option_name","$arrInputDropDown[$response_type]") . "\n";
			}

			// Last Item...
			if($IntQuestionId !== "$question_id" && $IntQuestionId !== NULL)
			{
				$IntPrevious = $question_id-1;
				$mixOutPut .= "$arrEndInputTypes[$response_type]" . "\n";
				//echo "Ending...$IntPrevious<br>\n";
				$IntQuestionId = NULL;
				// start new question..
				$mixOutPut .= "" . "debug: 4" . "$mixCheckBoxStart" . str_replace("[question]","strAnswer[$question_id]","$arrInputTypes[$response_type]") . "$mixCheckBoxEnd \n";
				$mixOutPut .= "" . "debug: 5" . str_replace("[option]","$option_name","$arrInputDropDown[$response_type]") . "\n";
				$intLoopStarted = TRUE;
			}
			$IntQuestionId = "$question_id";
			$intQuestionNum = $question_id;

			if($foo!==$question_id)
			{
				echo "E, ";
				//$mixOutPut .= "$mixQuestionEnd";
			}

		}
		if($intLoopStarted == TRUE)
		{
			$mixOutPut .= "debug: 6" . "$arrEndInputTypes[$response_type]" . "\n";
			//echo "Ending...$question_id<br>\n";
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
