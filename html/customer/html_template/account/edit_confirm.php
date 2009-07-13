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
 


 class HtmlTemplateAccountEditConfirm extends HtmlTemplate
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
		echo "<div class='customer-standard-display-title'>&nbsp;</div><br /><br />";
		echo "<div class='customer-standard-table-title-style-notice'><font color='red'>Please confirm the new changes below</font></div><br /><br />";

		foreach($_POST as $key=>$val)
		{
			$$key=htmlspecialchars("$val", ENT_QUOTES);
		}
		echo "
		<form method=\"post\" action=\"./flex.php/Console/Edit/\"\">
		<input type=\"hidden\" name=\"intUpdateAccountId\" value=\"$intUpdateAccountId\" />
		<input type=\"hidden\" name=\"mixAccount_Address1\" value=\"$mixAccount_Address1\" />
		<input type=\"hidden\" name=\"mixAccount_Address2\" value=\"$mixAccount_Address2\" />
		<input type=\"hidden\" name=\"mixAccount_Suburb\" value=\"$mixAccount_Suburb\" />
		<input type=\"hidden\" name=\"mixAccount_State\" value=\"$mixAccount_State\" />
		<input type=\"hidden\" name=\"mixAccount_Postcode\" value=\"$mixAccount_Postcode\" />
		<input type=\"hidden\" name=\"mixAccount_BillingMethod\" value=\"$mixAccount_BillingMethod\" />
		<input type=\"hidden\" name=\"mixAccount_Country\" value=\"$mixAccount_Country\" />
		<input type=\"hidden\" name=\"mixContact_FirstName\" value=\"$mixContact_FirstName\" />
		<input type=\"hidden\" name=\"mixContact_LastName\" value=\"$mixContact_LastName\" />
		<input type=\"hidden\" name=\"mixContact_Title\" value=\"$mixContact_Title\" />
		<input type=\"hidden\" name=\"mixContact_JobTitle\" value=\"$mixContact_JobTitle\" />
		<input type=\"hidden\" name=\"mixContact_Email\" value=\"$mixContact_Email\" />
		<input type=\"hidden\" name=\"mixContact_Phone\" value=\"$mixContact_Phone\" />
		<input type=\"hidden\" name=\"mixContact_Mobile\" value=\"$mixContact_Mobile\" />
		<input type=\"hidden\" name=\"mixAccount_OldPassword\" value=\"$mixAccount_OldPassword\" />
		<input type=\"hidden\" name=\"mixAccount_NewPassword1\" value=\"$mixAccount_NewPassword1\" />
		<input type=\"hidden\" name=\"mixAccount_NewPassword2\" value=\"$mixAccount_NewPassword2\" />
		<input type=\"hidden\" name=\"mixContact_Fax\" value=\"$mixContact_Fax\" />";

		$intBillMethod = $_POST['mixAccount_BillingMethod'];
		$strNewBillingMethod = $GLOBALS['*arrConstant']['delivery_method'][$intBillMethod]['Description'];
		
		// If the user is changing there password: display a notice on this page, 
		// they may not change any other details so we need to let them know what this update is for.
		if(array_key_exists('mixAccount_NewPassword1', $_POST))
		{
			if($_POST['mixAccount_NewPassword1'] != "")
			{
				print "
				<div class='customer-standard-table-title-style-confirm-details'>Password Change</div>
				<div class='GroupedContent'>
				<table class=\"customer-standard-table-style\">
				<tr>
				<td style=\"width: 160px;\">Password change: </td>
				<td>Yes</td>
				</tr>
				</table>
				</div>
				<br/>";		
			}
		}
		
		print "
		<div class='customer-standard-table-title-style-confirm-details'>Billing Details</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td style=\"width: 160px;\">Billing Method: </td>
			<td>$strNewBillingMethod</td>
		</tr>
		</table>
		</div>
		<br />";
		
		print "
		<div class='customer-standard-table-title-style-confirm-details'>Billing Address Details</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td style=\"width: 160px;\">Street Address: </td>
			<td>$mixAccount_Address1</td>
		</tr>
		<tr>
			<td></td>
			<td>$mixAccount_Address2</td>
		</tr>
		<tr>
			<td>Suburb: </td>
			<td>$mixAccount_Suburb</td>
		</tr>
		<tr>
			<td>State: </td>
			<td>$mixAccount_State</td>
		</tr>
		<tr>
			<td>Postcode: </td>
			<td>$mixAccount_Postcode</td>
		</tr>
		<tr>
			<td>Country: </td>
			<td>$mixAccount_Country</td>
		</tr>
		</table>
		</div>
		<br />";

		print "
		<div class='customer-standard-table-title-style-confirm-details'>Contact Details</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td style=\"width: 160px;\">First Name: </td>
			<td>$mixContact_FirstName</td>
		</tr>
		<tr>
			<td>Last Name: </td>
			<td>$mixContact_LastName</td>
		</tr>
		<tr>
			<td>Title: </td>
			<td>$mixContact_Title</td>
		</tr>
		<tr>
			<td>Job Title: </td>
			<td>$mixContact_JobTitle</td>
		</tr>
		<tr>
			<td>Email: </td>
			<td>$mixContact_Email</td>
		</tr>
		<tr>
			<td>Phone: </td>
			<td>$mixContact_Phone</td>
		</tr>
		<tr>
			<td>Mobile: </td>
			<td>$mixContact_Mobile</td>
		</tr>
		<tr>
			<td>Fax: </td>
			<td>$mixContact_Fax</td>
		</tr>
		</table>
		</div>
		<br />

		<div class='customer-standard-table-title-style-confirm-details'>Disclaimer</div>
		<div class='GroupedContent'>
		<table class=\"customer-standard-table-style\">
		<tr>
			<td>
				Making changes to your account details may affect the way details  are represented when you receive your bill.
				The contact person allocated will be required to verify their personal contact details when calling into the service centre for account information.
			</td>
		</tr>
		</table>
		</div>
		<br/>

		<table class=\"customer-standard-table-style\">
		<tr>
			<td align=right><input type=\"button\" value=\"Cancel\" onclick=\"javascript:document.location = './'\" /> <input type=\"submit\" value=\"Confirm Changes\" /></td>
		</tr>
		</table>
		</form>
		";
		
		echo "</div>\n";
	}
}

?>
