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

		$this->LoadJavascript('javascript_functions');
		$this->LoadJavascript('javascript_validation');
		$this->LoadJavascript('javascript_error_box');
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


			if(!array_key_exists('intRequestType', $_POST))
			{
				echo "<form method=\"POST\" action=\"./flex.php/Console/Support/\">";
				echo "<div class='customer-standard-table-title-style-password'>Select your request category</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
				<TR>
				<TD>Request Type: </TD>
				<TD>
					<SELECT NAME=\"intRequestType\">";
						for($i=1; $i<count($GLOBALS['*arrConstant']['SupportType'])+1; $i++)
						{
							$mixDescription = $GLOBALS['*arrConstant']['SupportType'][$i]['Description'];
							print "<OPTION VALUE=\"$i\">$mixDescription</option>";
						}
					print "</SELECT>
					</TD>
				</TR>
				</TABLE>
				</div>";
			}
			else if(is_numeric($_POST['intRequestType']))
			{
				echo "<form method=\"POST\" action=\"./flex.php/Console/Support/\" onsubmit=\"return validate_support_request(this)\">";
				echo "
				<input type=\"hidden\" name=\"intRequestType\" value=\"$_POST[intRequestType]\">
				<input type=\"hidden\" name=\"intRequestTypeSubmit\" value=\"1\">
				<div class='customer-standard-table-title-style-address'>Address Details</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR>
				<TD width=\"160\">Street Address: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address1\" VALUE=\"" . htmlspecialchars(DBO()->Account->Address1->Value) . "\" size=\"30\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD></TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address2\" VALUE=\"" . htmlspecialchars(DBO()->Account->Address2->Value) . "\" size=\"30\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD>Suburb: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Suburb\" VALUE=\"" . htmlspecialchars(DBO()->Account->Suburb->Value) . "\"></TD>
				</TR>

				<TR>
				<TD>State: </TD>
				<TD>
					<SELECT NAME=\"mixAccount_State\">";
					
					foreach($GLOBALS['*arrConstant']['ServiceStateType'] as $strStateName=>$resStateName){
						$strStateDescription = $GLOBALS['*arrConstant']['ServiceStateType']["$strStateName"]['Description'];
						if($strStateName == DBO()->Account->State->Value)
						{
							$mixStates .= "<OPTION VALUE=\"$strStateName\" SELECTED>$strStateDescription</OPTION>\n";
						}
						else
						{
							$mixStates .= "<OPTION VALUE=\"$strStateName\">$strStateDescription</OPTION>\n";
						}
					}

					print "
					$mixStates
					</SELECT>
					</TD>
				</TR>
				<TR>
				<TD>Postcode: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Postcode\" VALUE=\"" . htmlspecialchars(DBO()->Account->Postcode->Value) . "\" size=\"4\" maxlength=\"4\"></TD>
				</TR>
				<TR>
				<TD>Country: </TD>
				<TD>
					<SELECT NAME=\"mixAccount_Country\">
					<OPTION VALUE=\"" . htmlspecialchars(DBO()->Account->Country->Value) . "\" SELECTED>" . htmlspecialchars(DBO()->Account->Country->Value) . "</OPTION>
					</SELECT>
					</TD>
				</TR>
				</TABLE>
				</div>
				<br/>";

				switch($_POST['intRequestType'])
				{

					case "1":
					echo "<div class='customer-standard-table-title-style-password'>Logging a fault to an existing service</div>
					<div class='GroupedContent'>
					<TABLE class=\"customer-standard-table-style\">
					<TR VALIGN=\"TOP\">
						<TD width=\"160\">Faulty service line number:</TD>
						<TD><input type=\"text\" name=\"intFaultLine1\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Faulty service line number:</TD>
						<TD><input type=\"text\" name=\"intFaultLine2\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Faulty service line number:</TD>
						<TD><input type=\"text\" name=\"intFaultLine3\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Faulty service line number:</TD>
						<TD><input type=\"text\" name=\"intFaultLine4\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Faulty service line number:</TD>
						<TD><input type=\"text\" name=\"intFaultLine5\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Faulty service line number:</TD>
						<TD><input type=\"text\" name=\"intFaultLine6\"></TD>
					</TR>
					</TABLE>
					</div>
					<br/>";
					break; 	

					case "2":
					echo "<div class='customer-standard-table-title-style-password'>Make a change to an existing service</div>
					<div class='GroupedContent'>
					<TABLE class=\"customer-standard-table-style\">
					<TR VALIGN=\"TOP\">
					<TD width=\"160\">Are any diversions required:</TD>
					<TD>
						<input type=\"radio\" name=\"intDiversionsRequired\" value=\"1\"> Yes<br>
						<input type=\"radio\" name=\"intDiversionsRequired\" value=\"0\"> No
					</TD>
					</TR>					
					<TD width=\"160\">If so:</TD>
					<TD>
						<TABLE style=\"font-size: 12px;\">
						<TR>
							<TD>From number</TD>
							<TD></TD>
							<TD>Diverted to number</TD>
						</TR>
						<TR>
							<TD><INPUT TYPE=\"text\" NAME=\"intDiversionFromNumber\"></TD>
							<TD><IMG SRC=\"./img/template/arrow_right.jpg\" WIDTH=\"34\" HEIGHT=\"11\" BORDER=\"0\" ALT=\"\"></TD>
							<TD><INPUT TYPE=\"text\" NAME=\"intDiversionToNumber\"></TD>
						</TR>
						</TABLE>
					</TD>
					</TR>
					</TABLE>
					</div>
					<br/>";
					break;

					case "3":
					echo "
					<div class='customer-standard-table-title-style-password'>Disconnect a no longer required line number</div>
					<div class='GroupedContent'>
					<TABLE class=\"customer-standard-table-style\">
					<TR VALIGN=\"TOP\">
						<TD width=\"160\">Service line number:</TD>
						<TD><input type=\"text\" name=\"intDisconnectNumber1\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Service line number:</TD>
						<TD><input type=\"text\" name=\"intDisconnectNumber2\"></TD>
					</TR>
					<TR VALIGN=\"TOP\">
						<TD>Service line number:</TD>
						<TD><input type=\"text\" name=\"intDisconnectNumber3\"></TD>
					</TR>
					</TABLE>
					</div>
					<br/>";
					break;

					case "4":
					// 4 = Add new service..
					/*
					echo "<div class='customer-standard-table-title-style-password'>Add a new line</div>
					<div class='GroupedContent'>
					<TABLE class=\"customer-standard-table-style\">
					<TR VALIGN=\"TOP\">
					<TD width=\"160\">Something here?:</TD>
					<TD></TD>
					</TR>
					</TABLE>
					</div>
					<br/>";
					*/
					break;

					case "5":
					// 5 = Other
					/*
					echo "<div class='customer-standard-table-title-style-password'>Details of request</div>
					<div class='GroupedContent'>
					<TABLE class=\"customer-standard-table-style\">
					<TR VALIGN=\"TOP\">
					<TD width=\"160\">Something here?:</TD>
					<TD></TD>
					</TR>
					</TABLE>
					</div>
					<br/>";
					*/
					break;

					default:
					// Unable to determine request type..?
					break;
				}

				echo "<div class='customer-standard-table-title-style-password'>Type of service</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
				<TD width=\"160\">Service Type:</TD>
				<TD>
					<select name=\"mixServiceType\">";

						for($i=1; $i<count($GLOBALS['*arrConstant']['ServiceType'])+1; $i++)
						{
							$mixServiceDescription = $GLOBALS['*arrConstant']['ServiceType'][$i]['Description'];
							print "<option value=\"$i\">$mixServiceDescription</option>";
						}

						print "
					</select>		
				</TD>
				</TR>
				</TABLE>
				</div>
				<br/>";

				echo "<div class='customer-standard-table-title-style-password'>Brief description</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
				<TD width=\"160\">Details of request:</TD>
				<TD><TEXTAREA NAME=\"mixAdditionalComments\" ROWS=\"5\" COLS=\"35\"></TEXTAREA></TD>
				</TR>
				</TABLE>
				</div>
				<br/>";

				print "
				<div class='customer-standard-table-title-style-contact'>Contact Details</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR>
				<TD width=\"160\">Title: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Title\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Title->Value) . "\" size=\"10\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD>Job Title: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_JobTitle\" VALUE=\"" . htmlspecialchars(DBO()->Contact->JobTitle->Value) . "\"></TD>
				</TR>
				<TR>
				<TD>First Name: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_FirstName\" VALUE=\"" . htmlspecialchars(DBO()->Contact->FirstName->Value) . "\"></TD>
				</TR>
				<TR>
				<TD>Last Name: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_LastName\" VALUE=\"" . htmlspecialchars(DBO()->Contact->LastName->Value) . "\"></TD>
				</TR>
				<TR>
				<TD>E-mail: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Email\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Email->Value) . "\" size=\"30\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD>Phone: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Phone\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Phone->Value) . "\"></TD>
				</TR>
				<TR>
				<TD>Mobile: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Mobile\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Mobile->Value) . "\"></TD>
				</TR>
				<TR>
				<TD>Fax: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Fax\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Fax->Value) . "\"></TD>
				</TR>
				</TABLE>
				</div>
				<br>";


				echo "<div class='customer-standard-table-title-style-password'>Terms and conditions</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
				<TD width=\"160\">Please read:</TD>
				<TD><TEXTAREA NAME=\"\" ROWS=\"5\" COLS=\"35\">I understand that Telstra determines on reasonable grounds that if the fault is not in the Telstra network (for example a fault in private equipment) an incorrect callout charge will apply.($105 inc GST)

Telstra are responsible for the service to the Network Boundary Point of Customer Premises. This is the first socket in a residence and or business and the Main Distribution Frame (MDF) in a multi unit dwelling.
If the fault is with private equipment and not with the Telstra network then Telco Blue will be charged a $105 call out fee from Telstra which will then be passed on to you the customer for payment on your next bill. Please note sometimes this charge can come through upto 90 days in arrears.</TEXTAREA></TD>
				</TR>
				</TABLE>
				</div>";
			}
			print "
			<br/>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
				<TD align=right><INPUT TYPE=\"button\" VALUE=\"Cancel\" onclick=\"javascript:document.location = './'\"> <INPUT TYPE=\"submit\" VALUE=\"Continue\"></TD>
			</TR>
			</TABLE>
			<div id=\"error_box\"></div>";

			echo "</form>";
	}
}

?>
