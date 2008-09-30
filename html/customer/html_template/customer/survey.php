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

			echo "
			<div class='customer-standard-table-title-style-confirm-details'>" . DBO()->Survey->Title->Value . "</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td>Please complete all fields below</td>
			</tr>
			</table>
			</div><br/>";
			echo "<form method=\"POST\" action=\"./flex.php/Console/Survey/\">";
			echo DBO()->Survey->Form->Value;
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
		else if(DBO()->Survey->Error->Value !== NULL)
		{
			$return_link = "";
			if(!eregi("already completed this survey",DBO()->Survey->Error->Value))
			{
				$return_link = "Please return and correct the errors, <A HREF=\"javascript:history.go(-1)\">click here</A>.";
			}
			echo "
			<div class='customer-standard-table-title-style-notice'>Failure notice</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td>" . DBO()->Survey->Error->Value . "$return_link</td>
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
