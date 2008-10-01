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

			// Temporarily putting this here until its moved to a better placed.
			// Configuration for the support page.
			$arrSupportConfig['SupportType'][1]['Description'] = 'Logging a fault to an existing service';
			$arrSupportConfig['SupportType'][2]['Description'] = 'Make a change to an existing service';
			$arrSupportConfig['SupportType'][3]['Description'] = 'Disconnect a no longer required line number';
			$arrSupportConfig['SupportType'][4]['Description'] = 'Add a new line';
			$arrSupportConfig['SupportType'][5]['Description'] = 'Other';

			// Create  list of available services 
			$mixTypeOfServiceList = "<table>";
			$mixTypeOfServiceList_dropdown = "<table>
			<select name=\"mixServiceType\">";
			for($i=100; $i<100+count($GLOBALS['*arrConstant']['service_type']); $i++)
			{
				$mixServiceDescription = $GLOBALS['*arrConstant']['service_type'][$i]['Description'];
				if(!eregi("dialup",$mixServiceDescription))
				{
					$mixTypeOfServiceList .= "
					<tr>
						<td><input type=\"checkbox\" name=\"mixServiceType[$i]\" VALUE=\"$mixServiceDescription\"></td>
						<td>$mixServiceDescription</td>
					</tr>";				
					$mixTypeOfServiceList_dropdown .= "	<option value=\"$i\">$mixServiceDescription</option>";
				}
			}
			$mixTypeOfServiceList .= "</table>";
			$mixTypeOfServiceList_dropdown .= "
			</select>
			</table>";



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
				<td class=\"text_row_standout\">Select:</td>
				<td class=\"text_row_standout\">Service</td>
				<td class=\"text_row_standout\">Plan</td>
				<td class=\"text_row_standout\">Contract Ends</td>
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
					<td><label for=\"intFaultLine[$count]\">";
						if($mixShowExpire !== "" && $mixShowExpire !== "N/a")
						{
							$mixServiceList .= date("d-m-Y",strtotime($mixShowExpire));
						}
						if($mixShowExpire == "N/a")
						{
							$mixServiceList .= "$mixShowExpire";
						}
				$mixServiceList .= "</label></td>
				</tr>";
			}
			$mixServiceList .= "</table>";




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
						for($i=1; $i<count($arrSupportConfig['SupportType'])+1; $i++)
						{
							$mixDescription = $arrSupportConfig['SupportType'][$i]['Description'];
							print "<OPTION VALUE=\"$i\">$mixDescription</option>";
						}
					print "</SELECT>
					</TD>
				</TR>
				</TABLE>
				</div>";
			}
			// detect if we are adding new service
			// check if this page has been loaded before with array_key_exists..
			if(is_numeric($_POST['intRequestType']) && $arrSupportConfig['SupportType'][$_POST['intRequestType']]['Description'] == "Add a new line" && !array_key_exists('intAddNewServiceCheck' ,$_POST))
			{
				echo "<form method=\"POST\" action=\"./flex.php/Console/Support/\">";
				echo "<input type=\"hidden\" name=\"intRequestType\" value=\"$_POST[intRequestType]\">"; // tell php we have already selected a first option
				echo "<input type=\"hidden\" name=\"intAddNewServiceCheck\" value=\"1\">"; // tell php to bypass add service selection (this page)
				echo "<div class='customer-standard-table-title-style-password'>Please select the service you wish to add</div>
				<div class='GroupedContent'>$mixTypeOfServiceList_dropdown</div>
				<br/>";
			}

			// If first option selected then show page two.
			if(is_numeric($_POST['intRequestType']) && $arrSupportConfig['SupportType'][$_POST['intRequestType']]['Description'] !== "Add a new line")
			{
				// skip the add service page.
				$_POST['intAddNewServiceCheck'] = "1";
			}
			// If first option selected then show page two. only if add service page has been shown first...
			if(is_numeric($_POST['intRequestType']) && array_key_exists('intAddNewServiceCheck' ,$_POST))
			{
				echo "<form method=\"POST\" action=\"./flex.php/Console/Support/\" onsubmit=\"return validate_support_request(this)\">";
				echo "
				<input type=\"hidden\" name=\"intRequestType\" value=\"$_POST[intRequestType]\">
				<input type=\"hidden\" name=\"intRequestTypeSubmit\" value=\"1\">
				<input type=\"hidden\" name=\"mixServiceType\" value=\"$_POST[mixServiceType]\">
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
						/*
							1 = ADSL
							2 = Mobile
							3 = Land Line
							4 = Other
						*/

						$arrPlans = DBO()->CustomerPlans->ListPlans->Value;
						$mixPlanList = "";
						foreach($arrPlans as $arrPlan)
						{
							foreach($arrPlan as $key=>$val)
							{
								$$key=$val;
							}
							$mixPlanList .= "<OPTION VALUE=\"$Name\">$Name</OPTION>\n";
						}
						$mixServiceType = $_POST['mixServiceType'];
						switch($mixServiceType)
						{
							case "100":
							echo "
							<div class='customer-standard-table-title-style-password'>DSL Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR VALIGN=\"TOP\">
							<TD width=\"160\">Select Option:</TD>
							<TD>
								<SELECT NAME=\"mixDSLSetup\">
									<OPTION VALUE=\"New Connection\">New Connection</OPTION>
									<OPTION VALUE=\"Port Old Connection\">Port Old Connection</OPTION>
								</SELECT></TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>For existing connections (Porting), please complete below.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD>DSL Type</TD>
								<TD>
									<SELECT NAME=\"mixDSLExistingConnection\">
										<OPTION VALUE=\"ADSL\">ADSL</OPTION>
										<OPTION VALUE=\"ADSL2\">ADSL2</OPTION>
										<OPTION VALUE=\"ADSL2+\">ADSL2+</OPTION>
										<OPTION VALUE=\"ADSL2++\">ADSL2++</OPTION>
										<OPTION VALUE=\"ISDN\">ISDN</OPTION>
										<OPTION VALUE=\"HDSL\">HDSL</OPTION>
										<OPTION VALUE=\"HDSL2\">HDSL2</OPTION>
										<OPTION VALUE=\"SDSL\">SDSL</OPTION>
										<OPTION VALUE=\"SHDSL\">SHDSL</OPTION>
										<OPTION VALUE=\"G.SHDSL\">G.SHDSL</OPTION>
										<OPTION VALUE=\"RADSL\">RADSL</OPTION>
										<OPTION VALUE=\"VDSL\">VDSL</OPTION>
										<OPTION VALUE=\"VDSL2\">VDSL2</OPTION>
										<OPTION VALUE=\"UDSL\">UDSL</OPTION>
										<OPTION VALUE=\"GDSL\">GDSL</OPTION>
									</SELECT>								
								</TD>
							</TR>
							<TR>
								<TD width=\"160\">Current Provider</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixDSLCurrentProvider\"></TD>
							</TR>
							<TR>
								<TD width=\"160\">Account Number</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixDSLCurrentAccount\"> (with current provider)</TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>Plan Choice.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Select New Plan: </TD>
								<TD>
									<SELECT NAME=\"mixDSLNewPlan\">
										$mixPlanList
									</SELECT>								
								</TD>
							</TR>
							<TR>
								<TD width=\"160\">Line number</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixDSLPhoneNumber\"></TD>
							</TR>
							</TABLE>
							</div>
							<br/>";
							break;

							case "101":
							echo "
							<div class='customer-standard-table-title-style-password'>Mobile Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR VALIGN=\"TOP\">
							<TD width=\"160\">Select Option:</TD>
							<TD>
								<SELECT NAME=\"mixMobileSetup\">
									<OPTION VALUE=\"New Connection\">New Activation</OPTION>
									<OPTION VALUE=\"Port Old Connection\">Port Old Number</OPTION>
								</SELECT></TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>For existing connections (Porting), please complete below.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Current Mobile #</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixMobileNumber\"></TD>
							</TR>
							<TR>
								<TD width=\"160\">Current Carrier</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixMobileCurrentProvider\"></TD>
							</TR>
							<TR>
								<TD width=\"160\">Account Number</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixMobileCurrentAccount\"> (with current provider)</TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>Plan Choice.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Select New Plan: </TD>
								<TD>
									<SELECT NAME=\"mixMobileNewPlan\">
										$mixPlanList
									</SELECT>								
								</TD>
							</TR>
							</TABLE>
							</div>
							<br/>";
							break;
							
							case "102":
							echo "
							<div class='customer-standard-table-title-style-password'>Landline Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR VALIGN=\"TOP\">
							<TD width=\"160\">Select Type:</TD>
							<TD>
								<SELECT NAME=\"mixLandlineSetup\">
									<OPTION VALUE=\"PSTN\">PSTN</OPTION>
									<OPTION VALUE=\"ISDN\">ISDN</OPTION>
									<OPTION VALUE=\"Not Sure\">Not Sure</OPTION>
								</SELECT>
							</TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>Options, choose either PSTN or ISDN, <B>NOT</B> both.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\"></TD>
								<TD><B>PSTN Options</B></TD>
							</TR>
							<TR>
								<TD colspan=\"2\">
									<TABLE class=\"customer-standard-table-style\">
									<TR>
										<TD width=\"160\">Message Bank: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlinePSTNMessageBank\"></TD>
									</TR>
									<TR>
										<TD>Line Hunt: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlinePSTNLineHunt\"></TD>
									</TR>
									<TR>
										<TD>Caller ID: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlinePSTNCallerId\"></TD>
									</TR>
									<TR>
										<TD>Fax Duet: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlinePSTNFaxDuet\"></TD>
									</TR>
									<TR>
										<TD>Fax Stream: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlinePSTNFaxStream\"></TD>
									</TR>
									</TABLE>
									<BR>
								</TD>
							</TR>
							<TR>
								<TD width=\"160\"></TD>
								<TD VALIGN=\"top\"><B>ISDN Options</B></TD>
							</TR>
							<TR>
								<TD colspan=\"2\">
									<TABLE class=\"customer-standard-table-style\">
									<TR>
										<TD width=\"160\">100 Indial Range: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlineISDNIndialRange\"></TD>
									</TR>
									<TR>
										<TD>Caller ID: </TD>
										<TD><INPUT TYPE=\"checkbox\" NAME=\"mixLandlineISDNCallerId\"></TD>
									</TR>
									<TR>
										<TD>On Ramp: </TD>
										<TD>
											<SELECT NAME=\"mixLandlineISDNOnRamp\">
												<OPTION VALUE=\"On Ramp 2\">On Ramp 2</OPTION>
												<OPTION VALUE=\"On Ramp 10\">On Ramp 10</OPTION>
												<OPTION VALUE=\"On Ramp 20\">On Ramp 20</OPTION>
												<OPTION VALUE=\"On Ramp 30\">On Ramp 30</OPTION>
											</SELECT>	
										</TD>
									</TR>
									</TABLE>
								</TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>Plan Choice.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Select New Plan: </TD>
								<TD>
									<SELECT NAME=\"mixLandlineNewPlan\">
										$mixPlanList
									</SELECT>								
								</TD>
							</TR>
							</TABLE>
							</div>
							<br/>";
							break;
							

							case "103":
							echo "
							<div class='customer-standard-table-title-style-password'>Inbound Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR VALIGN=\"TOP\">
							<TD width=\"160\">Select Option:</TD>
							<TD>
								<SELECT NAME=\"mixInboundSetup\">
									<OPTION VALUE=\"New Connection\">New Activation</OPTION>
									<OPTION VALUE=\"Port Old Connection\">Port Old Number</OPTION>
								</SELECT></TD>
							</TR>
							<TR>
								<TD width=\"160\">Current Account Number</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixInboundCurrentAccount\"> (with current provider)</TD>
							</TR>
							<TR>
								<TD width=\"160\">Answering Point</TD>
								<TD><INPUT TYPE=\"text\" NAME=\"mixInboundAnsweringPoint\"></TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>Plan Choice.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Select New Plan: </TD>
								<TD>
									<SELECT NAME=\"mixInboundNewPlan\">
										$mixPlanList
									</SELECT>								
								</TD>
							</TR>
							</TABLE>
							</div>
							<br/>";
							break;
							
							default:
							// Unable to determine request type..?
							break;
						}
					break;

					case "5":
					echo "<div class='customer-standard-table-title-style-password'>Select the service this query is related to</div>
					<div class='GroupedContent'>
					$mixServiceList
					</div>
					<br/>";
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

					break;

					default:
					// Unable to determine request type..?
					break;
				}

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
If the fault is with private equipment and not with the Telstra network then there will be a $105 call out fee from Telstra which will then be passed on to you the customer for payment on your next bill. Please note sometimes this charge can come through upto 90 days in arrears.</TEXTAREA></TD>
				</TR>
				</TABLE>
				</div>";
			}
			print "
			<br/>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
				<TD align=right><INPUT TYPE=\"button\" VALUE=\"Back\" onclick=\"javascript:history.go(-1)\"> <INPUT TYPE=\"submit\" VALUE=\"Continue\"></TD>
			</TR>
			</TABLE>
			<div id=\"error_box\"></div>";

			echo "</form>";
	}
}

?>
