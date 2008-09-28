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
 


 class HtmlTemplateCustomerSupportConfirmation extends HtmlTemplate
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
		echo "<div class='customer-standard-table-title-style-notice'><FONT COLOR='red'>Please confirm the new request below</FONT></div><br/><br/>";

		echo "<form method=\"POST\" action=\"./flex.php/Console/Support/\"\">";


		$bolFaultLine = FALSE;
		$bolServiceType = FALSE;
		$bolInContract = FALSE;

		$mixTypeOfServiceList = "<table class=\"customer-standard-table-style\">";
		$mixServiceList = "<table class=\"customer-standard-table-style\">						
		<tr valign=\"top\">
			<td>Service</td>
			<td>Plan</td>
			<td>Contract Ends</td>
		</tr>";
		$mixHiddenFields = "";
		foreach($_POST as $key=>$val)
		{
			if(is_array($val))
			{
				foreach($val as $key2=>$val2)
				{
					$key2 = htmlspecialchars("$key2", ENT_QUOTES);
					$val2 = htmlspecialchars("$val2", ENT_QUOTES);
					$bit = "[";
					if($key == "mixServiceType")
					{
						$bolServiceType = TRUE;
						$mixTypeOfServiceList .= "
						<tr>
							<td>$val2</td>
						</tr>";
					}
					if($key == "intFaultLine")
					{
						$bolFaultLine = TRUE;
						$mixContent = explode("||",$val2);
						$mixShowExpire = $mixContent[2];
						if(!$mixShowExpire)
						{
							$mixShowExpire = "N/a";
						}
						$mixShowNotice = "";
						$intTimeExpires = strtotime($mixShowExpire);
						if($intTimeExpires > time() && $intRequestType == "3")
						{
							$mixShowNotice = " class=\"text_notice\"";
							$bolInContract = TRUE;
						}
						$mixServiceList .= "
						<tr valign=\"top\">
							<td>$mixContent[0]</td>
							<td>$mixContent[1]</td>
							<td$mixShowNotice>";
							
						if($mixShowExpire !== "" && $mixShowExpire !== "N/a")
						{
							$mixServiceList .= date("d-m-Y",strtotime($mixShowExpire));
						}
						if($mixShowExpire == "N/a")
						{
							$mixServiceList .= "$mixShowExpire";
						}

						$mixServiceList .= "</td>
						</tr>";
						$val2 = "$mixContent[0]";
					}
					$mixHiddenFields .= "<input type=\"hidden\" name=\"$key$bit$key2]\" value=\"$val2\">\n";
				}
			}
			else
			{
				$key = htmlspecialchars("$key", ENT_QUOTES);
				$val = htmlspecialchars("$val", ENT_QUOTES);
				$mixHiddenFields .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
				$$key=$val;
			}
		}
		$mixTypeOfServiceList .= "</table>";
		$mixServiceList .= "</table>";

		print "$mixHiddenFields";
		echo "
		<input type=\"hidden\" name=\"intRequestType\" value=\"$_POST[intRequestType]\">
		<input type=\"hidden\" name=\"intRequestTypeSubmit\" value=\"1\">
		<input type=\"hidden\" name=\"intRequestTypeSubmitFinal\" value=\"1\">
		<div class='customer-standard-table-title-style-address'>Address Details</div>
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD width=\"160\">Street Address: </TD>
			<TD>$mixAccount_Address1</TD>
		</TR>
		<TR>
			<TD></TD>
			<TD>$mixAccount_Address2</TD>
		</TR>
		<TR>
			<TD>Suburb: </TD>
			<TD>$mixAccount_Suburb</TD>
		</TR>
		<TR>
			<TD>State: </TD>
			<TD>$mixAccount_State</TD>
		</TR>
		<TR>
			<TD>Postcode: </TD>
			<TD>$mixAccount_Postcode</TD>
		</TR>
		<TR>
			<TD>Country: </TD>
			<TD>$mixAccount_Country</TD>
		</TR>
		</TABLE>
		</div>
		<br/>";

		switch($_POST['intRequestType'])
		{

			// Logging a fault to an existing service
			case "1":
			if($bolFaultLine)
			{
				echo "<div class='customer-standard-table-title-style-password'>Logging a fault to an existing service</div>
				<div class='GroupedContent'>
				$mixServiceList
				</div>
				<br/>";
			}
			break;

			// Making a change to an existing service
			case "2":
			echo "<div class='customer-standard-table-title-style-password'>Making a change to an existing service</div>
			<div class='GroupedContent'>
			$mixServiceList
			</div>
			<br/>";
			echo "<div class='customer-standard-table-title-style-password'>Create Diversions</div>
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR VALIGN=\"TOP\">
			<TD width=\"160\">Are any diversions required:</TD>
			<TD>$intDiversionsRequired</TD>
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
					<TD>$intDiversionFromNumber</TD>
					<TD><IMG SRC=\"./img/template/arrow_right.jpg\" WIDTH=\"34\" HEIGHT=\"11\" BORDER=\"0\" ALT=\"\"></TD>
					<TD>$intDiversionToNumber</TD>
				</TR>
				</TABLE>
			</TD>
			</TR>
			</TABLE>
			</div>
			<br/>";
			break;

			// Disconnect a no longer required line number
			case "3":
			if($bolFaultLine)
			{
				echo "<div class='customer-standard-table-title-style-password'>Disconnect a no longer required line number</div>
				<div class='GroupedContent'>
				$mixServiceList
				</div>
				<br/>";
			}
			break;

			// Add a new line
			case "4":

						// 100 - DSL
						if($_POST['mixServiceType'] == "100")
						{
							echo "<div class='customer-standard-table-title-style-password'>DSL Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">DSL Setup</TD>
								<TD>$_POST[mixDSLSetup]</TD>
							</TR>
							<TR>
								<TD>Existing Connection</TD>
								<TD>$_POST[mixDSLExistingConnection]</TD>
							</TR>
							<TR>
								<TD>Current Provider</TD>
								<TD>$_POST[mixDSLCurrentProvider]</TD>
							</TR>
							<TR>
								<TD>Current Account</TD>
								<TD>$_POST[mixDSLCurrentAccount]</TD>
							</TR>
							<TR>
								<TD>New Plan</TD>
								<TD>$_POST[mixDSLNewPlan]</TD>
							</TR>
							<TR>
								<TD>Phone Number</TD>
								<TD>$_POST[mixDSLPhoneNumber]</TD>
							</TR>
							</TABLE>
							</div><br/>";
						}
						// 101 - Mobile
						if($_POST['mixServiceType'] == "101")
						{
							echo "<div class='customer-standard-table-title-style-password'>Mobile Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Setup</TD>
								<TD>$_POST[mixMobileSetup]</TD>
							</TR>
							<TR>
								<TD>Number</TD>
								<TD>$_POST[mixMobileNumber]</TD>
							</TR>
							<TR>
								<TD>Current Provider</TD>
								<TD>$_POST[mixMobileCurrentProvider]</TD>
							</TR>
							<TR>
								<TD>Current Account</TD>
								<TD>$_POST[mixMobileCurrentAccount]</TD>
							</TR>
							<TR>
								<TD>New Plan</TD>
								<TD>$_POST[mixMobileNewPlan]</TD>
							</TR>
							</TABLE>
							</div><br/>";
						}
						// 102 - Landline
						if($_POST['mixServiceType'] == "102")
						{

							echo "<div class='customer-standard-table-title-style-password'>Mobile Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">Setup</TD>
								<TD>$_POST[mixLandlineSetup]</TD>
							</TR>
							<TR>
								<TD>PSTN Message Bank</TD>
								<TD>$_POST[mixLandlinePSTNMessageBank]</TD>
							</TR>
							<TR>
								<TD>PSTN Line Hunt</TD>
								<TD>$_POST[mixLandlinePSTNLineHunt]</TD>
							</TR>
							<TR>
								<TD>PSTN Caller Id</TD>
								<TD>$_POST[mixLandlinePSTNCallerId]</TD>
							</TR>
							<TR>
								<TD>PSTN Fax Duet</TD>
								<TD>$_POST[mixLandlinePSTNFaxDuet]</TD>
							</TR>
							<TR>
								<TD>PSTN Fax Stream</TD>
								<TD>$_POST[mixLandlinePSTNFaxStream]</TD>
							</TR>
							<TR>
								<TD>ISDN Indial Range</TD>
								<TD>$_POST[mixLandlineISDNIndialRange]</TD>
							</TR>
							<TR>
								<TD>ISDN Caller Id</TD>
								<TD>$_POST[mixLandlineISDNCallerId]</TD>
							</TR>
							<TR>
								<TD>ISDN On Ramp</TD>
								<TD>$_POST[mixLandlineISDNOnRamp]</TD>
							</TR>
							</TABLE>
							</div><br/>";
						}						
						// 103 - Inbound
						if($_POST['mixServiceType'] == "103")
						{
							echo "
							<div class='customer-standard-table-title-style-password'>Inbound Setup</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR VALIGN=\"TOP\">
							<TD width=\"160\"></TD>
							<TD><B>$_POST[mixInboundSetup]</B></TD>
							</TR>
							<TR>
								<TD width=\"160\">Current Account Number</TD>
								<TD>$_POST[mixInboundCurrentAccount]</TD>
							</TR>
							<TR>
								<TD width=\"160\">Answering Point</TD>
								<TD>$_POST[mixInboundAnsweringPoint]</TD>
							</TR>
							</TABLE>
							</div>
							<br/>
							<div class='customer-standard-table-title-style-password'>Plan Choice.</div>
							<div class='GroupedContent'>
							<TABLE class=\"customer-standard-table-style\">
							<TR>
								<TD width=\"160\">New Plan: </TD>
								<TD>$_POST[mixInboundNewPlan]</TD>
							</TR>
							</TABLE>
							</div>
							<br/>";
						}
			break;

			// Other
			case "5":
			break;

			// Unable to determine request type..?
			default:
			break;
		}
		if($bolServiceType)
		{
			echo "<div class='customer-standard-table-title-style-password'>Type of service</div>
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR VALIGN=\"TOP\">
				<TD>$mixTypeOfServiceList</TD>
			</TR>
			</TABLE>
			</div>
			<br/>";
		}

		echo "<div class='customer-standard-table-title-style-password'>Brief instructions</div>
		<div class='GroupedContent'>
		<TABLE class=\"customer-standard-table-style\">
		<TR VALIGN=\"TOP\">
		<TD width=\"160\">Details of request:</TD>
		<TD>$mixCustomerComments</TD>
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
		<TD>$mixContact_Title</TD>
		</TR>
		<TR>
		<TD>Job Title: </TD>
		<TD>$mixContact_JobTitle</TD>
		</TR>
		<TR>
		<TD>First Name: </TD>
		<TD>$mixContact_FirstName</TD>
		</TR>
		<TR>
		<TD>Last Name: </TD>
		<TD>$mixContact_LastName</TD>
		</TR>
		<TR>
		<TD>E-mail: </TD>
		<TD>$mixContact_Email</TD>
		</TR>
		<TR>
		<TD>Phone: </TD>
		<TD>$mixContact_Phone</TD>
		</TR>
		<TR>
		<TD>Mobile: </TD>
		<TD>$mixContact_Mobile</TD>
		</TR>
		<TR>
		<TD>Fax: </TD>
		<TD>$mixContact_Fax</TD>
		</TR>
		</TABLE>
		</div>
		<br>";

		if($bolInContract && $intRequestType == "3")
		{
			print "
			<div class='customer-standard-table-title-style-confirm-details'>Warning: In Contract</div>
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
				<TD>One of more of your services are still in contract (see above for details), an early termination fee will apply.</TD>
			</TR>
			</TABLE>
			</div>
			<br/>";
		}

		print "
		<TABLE class=\"customer-standard-table-style\">
		<TR>
			<TD align=right><INPUT TYPE=\"button\" VALUE=\"Back\" onclick=\"javascript:history.go(-1)\"> <input type=\"submit\" value=\"Confirm Changes\"></TD>
		</TR>
		</TABLE>
		</form>
		";
		
		echo "</div>\n";

	}
}

?>
