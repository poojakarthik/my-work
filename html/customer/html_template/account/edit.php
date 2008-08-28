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
 


 class HtmlTemplateAccountEdit extends HtmlTemplate
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

		$this->LoadJavascript('edit_account_details');
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
		echo "<br/><br/><div class='NarrowContent'>\n";

		// Display the details of their primary address
		$intAccountUpdated = DBO()->Account->Updated->Value;
		echo "$intAccountUpdated";
		if($intAccountUpdated=="1")
		{
			print "Thank you for taking the time to update your account,<br/><font color=\"green\">your changes have been completed. <img src=\"/" . CUSTOMER_URL_NAME . "/trunk/html/images/generic/check.gif\"></font><br/><br/>";
		}
		if($intAccountUpdated!="1")
		{
			print "
			<!-- We dont want any caching of this page.. -->
			<form method=\"POST\" action=\"./flex.php/Console/EditConfirm/\" onsubmit=\"return validate_form(this)\">
			<input type=\"hidden\" name=\"intUpdateAccountId\" value=\"$intAccountId\">

			<div class='customer-standard-table-title-style-billing'>Billing Details</div>
			<!-- <h2 class='Account'>Billing Details</h2> -->
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR VALIGN=\"TOP\">
				<TD width=\"200\">Billing Method: </TD>
				<TD>";
				
				$BillingMethod = DBO()->Account->BillingMethod->Value;
				for($i=0; $i<2; $i++)
				{
					$strShowChecked = "";
					if($BillingMethod == $i)
					{
						$strShowChecked = " CHECKED";
					}
					if($i == "2")
					{
						$strShowChecked .= " DISABLED";
					}
					$strDescriptionOfMethod = $GLOBALS['*arrConstant']['BillingMethod'][$i]['Description'];
					// Only show Post as a billing method for customers already on post.
					if($i != "0")
					{
						echo "<INPUT TYPE=\"radio\" NAME=\"mixAccount_BillingMethod\" VALUE=\"$i\"$strShowChecked> $strDescriptionOfMethod<br/>";
					}
					if($i == "0" && $BillingMethod == "0")
					{
						echo "<INPUT TYPE=\"radio\" NAME=\"mixAccount_BillingMethod\" VALUE=\"$i\"$strShowChecked> $strDescriptionOfMethod<br/>";
					}
				}

				print "</TD>
			</TR>
			</TABLE>
			</div>
			<br/>



			<div class='customer-standard-table-title-style-password'>Account Password</div>
			<!-- <h2 class='Account'>Account Password</h2> -->
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR VALIGN=\"TOP\">
			<TR>
				<TD width=\"200\">Old Password: </TD>
				<TD><INPUT TYPE=\"password\" NAME=\"mixAccount_OldPassword\" VALUE=\"\"></TD>
			</TR>
			<TR>
				<TD>New Password: </TD>
				<TD><INPUT TYPE=\"password\" NAME=\"mixAccount_NewPassword1\" VALUE=\"\" maxlength=\"40\"></TD>
			</TR>
			<TR>
				<TD>Repeat New Password: </TD>
				<TD><INPUT TYPE=\"password\" NAME=\"mixAccount_NewPassword2\" VALUE=\"\" maxlength=\"40\"></TD>
			</TR>
			<TR>
				<TD>&nbsp;</TD>
				<TD colspan=\"2\"><FONT SIZE=\"1\">If you do not wish to change your password please leave these fields blank.</FONT></TD>
			</TR>
			</TABLE>
			</div>
			<br/>

			<div class='customer-standard-table-title-style-address'>Address Details</div>
			<!-- <h2 class='Account'>Address Details</h2> -->
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
			<TD width=\"200\">Street Address: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address1\" VALUE=\"" . htmlspecialchars(DBO()->Account->Address1->Value) . "\"></TD>
			</TR>
			<TR>
			<TD></TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixAccount_Address2\" VALUE=\"" . htmlspecialchars(DBO()->Account->Address2->Value) . "\"></TD>
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
			<br/>

			<div class='customer-standard-table-title-style-contact'>Contact Details</div>
			<!-- <h2 class='Account'>Contact Details</h2> -->
			<div class='GroupedContent'>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
			<TD width=\"200\">Title: </TD>
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Title\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Title->Value) . "\"></TD>
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
			<TD><INPUT TYPE=\"text\" NAME=\"mixContact_Email\" VALUE=\"" . htmlspecialchars(DBO()->Contact->Email->Value) . "\"></TD>
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

			<br/>
			<TABLE class=\"customer-standard-table-style\">
			<TR>
				<TD align=right><INPUT TYPE=\"submit\" VALUE=\"Update Details\"></TD>
			</TR>
			</TABLE>
			";
		}

		echo "</div>\n";
	}
}

?>
