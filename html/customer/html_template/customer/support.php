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
 


 class HtmlTemplateCustomerSupport extends HtmlTemplate
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

			echo "<form method=\"POST\" action=\"./flex.php/Console/Support/\">";

			if(!array_key_exists('intRequestType', $_POST))
			{
				echo "<div class='customer-standard-table-title-style-password'>Select your request category</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
				<TR>
				<TD>Request Type: </TD>
				<TD>
					<SELECT NAME=\"intRequestType\">
						<OPTION VALUE=\"1\">Logging a fault to an existing service</OPTION>
						<OPTION VALUE=\"2\">Make a change to an existing service</OPTION>
						<OPTION VALUE=\"3\">Disconnect a no longer required line number</OPTION>
						<OPTION VALUE=\"4\">Add a new line</OPTION>
						<OPTION VALUE=\"5\">Other</OPTION>
					</SELECT>
					</TD>
				</TR>
				</TABLE>
				</div>";
			}
			else if(is_numeric($_POST['intRequestType']))
			{
				switch($_POST['intRequestType'])
				{

					case "1":
					echo "Form 1";
					break; 	

					case "2":
					echo "Form 2";
					break;

					case "3":
					echo "Form 3";
					break;

					case "4":
					echo "Form 4";
					break;

					case "5":
					echo "Form 5";
					break;

					default:
					echo "Unable to dertmine correct form to display";
					break;
				}
			}
			print "
			<br/>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
				<TD align=right><INPUT TYPE=\"button\" VALUE=\"Cancel\" onclick=\"javascript:document.location = './'\"> <INPUT TYPE=\"submit\" VALUE=\"Continue\"></TD>
			</TR>
			</TABLE>";

			echo "</form>";
	}
}

?>
