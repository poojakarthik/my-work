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

			// Create list of customer services
			DBO()->Account->Services = AppTemplateConsole::GetServices(DBO()->Account->Id->Value, SERVICE_ACTIVE);
			$arrServices			= DBO()->Account->Services->Value;
			$arrLines = array();
			foreach($arrServices as $arrService)
			{
				$count++;
				$arrLines[$count] = $arrService['FNN'] . "||" . $arrService['CurrentPlan']['Name'] . "||" . $arrService['CurrentPlan']['ContractExpiresOn'];
			}
			$mixServiceList = "
			<table class=\"customer-standard-table-style\">
			<tr valign=\"top\">
				<td>Select:</td>
				<td>Service</td>
				<td>Plan</td>
				<td>Contract Ends</td>
			</tr>";
			foreach($arrLines as $count=>$mixLine)
			{
				$mixContent = explode("||",$mixLine);
				$mixShowExpire = $mixContent[2];
				if(!$mixShowExpire)
				{
					$mixShowExpire = "N/a";
				}
				$mixServiceList .= "
				<tr valign=\"top\">
					<td><input type=\"checkbox\" name=\"intFaultLine[$count]\" value=\"$mixContent[0]||$mixContent[1]||$mixShowExpire\" id=\"intFaultLine[$count]\"></td>
					<td><label for=\"intFaultLine[$count]\">$mixContent[0]</label></td>
					<td><label for=\"intFaultLine[$count]\">$mixContent[1]</label></td>
					<td><label for=\"intFaultLine[$count]\">$mixShowExpire</label></td>
				</tr>";
			}
			$mixServiceList .= "</table>";


			// Create  list of available services 
			$mixTypeOfServiceList = "<table>";
			for($i=1; $i<count($GLOBALS['*arrConstant']['ServiceType'])+1; $i++)
			{
				$mixServiceDescription = $GLOBALS['*arrConstant']['ServiceType'][$i]['Description'];
				$mixTypeOfServiceList .= "
				<tr>
					<td><input type=\"checkbox\" name=\"mixServiceType[$i]\" VALUE=\"$mixServiceDescription\"></td>
					<td>$mixServiceDescription</td>
				</tr>";
			}
			$mixTypeOfServiceList .= "</table>";




			// If no submission then print default options.
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

			// If first option selected then show page two.
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
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address1\" VALUE=\"" . DBO()->Account->Address1->Value . "\" size=\"30\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD></TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address2\" VALUE=\"" . DBO()->Account->Address2->Value . "\" size=\"30\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD>Suburb: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Suburb\" VALUE=\"" . DBO()->Account->Suburb->Value . "\"></TD>
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
				<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Postcode\" VALUE=\"" . DBO()->Account->Postcode->Value . "\" size=\"4\" maxlength=\"4\"></TD>
				</TR>
				<TR>
				<TD>Country: </TD>
				<TD>
					<SELECT NAME=\"mixAccount_Country\">
					<OPTION VALUE=\"" . DBO()->Account->Country->Value . "\" SELECTED>" . DBO()->Account->Country->Value . "</OPTION>
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
					$mixServiceList
					</div>
					<br/>";
					break; 	

					case "2":
					echo "<div class='customer-standard-table-title-style-password'>Select the service you wish to change</div>
					<div class='GroupedContent'>
					$mixServiceList
					</div>
					<br/>";
					echo "<div class='customer-standard-table-title-style-password'>Create Diversions</div>
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
					$mixServiceList
					</div>
					<br/>";
					break;

					case "4":
					/* nothing */
					echo "";
					break;

					case "5":
					echo "<div class='customer-standard-table-title-style-password'>Select the service this query is related to</div>
					<div class='GroupedContent'>
					$mixServiceList
					</div>
					<br/>";
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
					<TD>$mixTypeOfServiceList</TD>
				</TR>
				</TABLE>
				</div>
				<br/>";

				echo "<div class='customer-standard-table-title-style-password'>Brief instructions</div>
				<div class='GroupedContent'>
				<TABLE class=\"customer-standard-table-style\">
				<TR VALIGN=\"TOP\">
				<TD width=\"160\">Details of request:</TD>
				<TD><textarea name=\"mixCustomerComments\" rows=\"5\" cols=\"35\"></textarea></TD>
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
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Title\" VALUE=\"" . DBO()->Contact->Title->Value . "\" size=\"10\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD>Job Title: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_JobTitle\" VALUE=\"" . DBO()->Contact->JobTitle->Value . "\"></TD>
				</TR>
				<TR>
				<TD>First Name: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_FirstName\" VALUE=\"" . DBO()->Contact->FirstName->Value . "\"></TD>
				</TR>
				<TR>
				<TD>Last Name: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_LastName\" VALUE=\"" . DBO()->Contact->LastName->Value . "\"></TD>
				</TR>
				<TR>
				<TD>E-mail: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Email\" VALUE=\"" . DBO()->Contact->Email->Value . "\" size=\"30\" maxlength=\"255\"></TD>
				</TR>
				<TR>
				<TD>Phone: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Phone\" VALUE=\"" . DBO()->Contact->Phone->Value . "\"></TD>
				</TR>
				<TR>
				<TD>Mobile: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Mobile\" VALUE=\"" . DBO()->Contact->Mobile->Value . "\"></TD>
				</TR>
				<TR>
				<TD>Fax: </TD>
				<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Fax\" VALUE=\"" . DBO()->Contact->Fax->Value . "\"></TD>
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
