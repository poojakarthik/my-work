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
			<form method=\"post\" action=\"./flex.php/Console/EditConfirm/\" onsubmit=\"return validate_edit_details(this)\">
			<input type=\"hidden\" name=\"intUpdateAccountId\" value=\"$intAccountId\" />

			<div class='customer-standard-table-title-style-billing'>Billing Details</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr valign=\"top\">
				<td width=\"160\">Billing Method: </td>
				<td>";
				
				$intBillingMethod = DBO()->Account->BillingMethod->Value;
				
				if($intBillingMethod == DELIVERY_METHOD_EMAIL)
				{
					echo "<input type=\"radio\" class=\"edit-details-radio-buttons\" name=\"mixAccount_BillingMethod\" value=\"" . DELIVERY_METHOD_EMAIL . "\" checked=\"checked\" /> Email<br/>";
				}
				else if($intBillingMethod == DELIVERY_METHOD_POST)
				{
					echo "<input type=\"radio\" class=\"edit-details-radio-buttons\" name=\"mixAccount_BillingMethod\" value=\"" . DELIVERY_METHOD_EMAIL . "\"$strShowChecked /> Email<br/>";
					echo "<input type=\"radio\" class=\"edit-details-radio-buttons\" name=\"mixAccount_BillingMethod\" value=\"" . DELIVERY_METHOD_POST . "\" checked=\"checked\" /> Post<br/>";
				}
				else
				{
					echo "<input type=\"radio\" class=\"edit-details-radio-buttons\" name=\"mixAccount_BillingMethod\" value=\"" . DELIVERY_METHOD_EMAIL . "\" /> Email<br/>";
				}

				print "</td>
			</tr>
			</table>
			</div>
			<br/>



			<div class='customer-standard-table-title-style-password'>Account Password</div>
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr valign=\"top\">
			<tr>
				<td width=\"160\">Old Password: </td>
				<td><input type=\"password\" name=\"mixAccount_OldPassword\" value=\"\" /></td>
			</tr>
			<tr>
				<td>New Password: </td>
				<td><input type=\"password\" name=\"mixAccount_NewPassword1\" value=\"\" maxlength=\"40\" /></td>
			</tr>
			<tr>
				<td>Repeat New Password: </td>
				<td><input type=\"password\" name=\"mixAccount_NewPassword2\" value=\"\" maxlength=\"40\" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan=\"2\"><font size=\"1\">If you do not wish to change your password please leave these fields blank.</font></td>
			</tr>
			</table>
			</div>
			<br/>

			<div class='customer-standard-table-title-style-address'>Billing Address Details</div>
			<!-- <h2 class='Account'>Address Details</h2> -->
			<div class='GroupedContent'>
			<table class=\"customer-standard-table-style\">
			<tr>
			<td width=\"160\">Street Address: </td>
			<td><input type=\"text\" name=\"mixAccount_Address1\" value=\"" . htmlspecialchars(DBO()->Account->Address1->Value) . "\" size=\"30\" maxlength=\"255\" /></td>
			</tr>
			<tr>
			<td></td>
			<td><input type=\"text\" name=\"mixAccount_Address2\" value=\"" . htmlspecialchars(DBO()->Account->Address2->Value) . "\" size=\"30\" maxlength=\"255\" /></td>
			</tr>
			<tr>
			<td>Suburb: </td>
			<td><input type=\"text\" name=\"mixAccount_Suburb\" value=\"" . htmlspecialchars(DBO()->Account->Suburb->Value) . "\" /></td>
			</tr>

			<tr>
			<td>State: </td>
			<td>
				<select name=\"mixAccount_State\">";
				
				foreach($GLOBALS['*arrConstant']['ServiceStateType'] as $strStateName=>$resStateName){
					$strStateDescription = $GLOBALS['*arrConstant']['ServiceStateType']["$strStateName"]['Description'];
					if($strStateName == DBO()->Account->State->Value)
					{
						$mixStates .= "<option value=\"$strStateName\" SELECTED>$strStateDescription</option>\n";
					}
					else
					{
						$mixStates .= "<option value=\"$strStateName\">$strStateDescription</option>\n";
					}
				}

				print "
				$mixStates
				</select>
				</td>
			</tr>
			<tr>
			<td>Postcode: </td>
			<td><input type=\"text\" name=\"mixAccount_Postcode\" value=\"" . htmlspecialchars(DBO()->Account->Postcode->Value) . "\" size=\"4\" maxlength=\"4\"></td>
			</tr>
			<tr>
			<td>Country: </td>
			<td>
				<select name=\"mixAccount_Country\">
				<option value=\"" . htmlspecialchars(DBO()->Account->Country->Value) . "\" SELECTED>" . htmlspecialchars(DBO()->Account->Country->Value) . "</option>
				</select>
				</td>
			</tr>
			</table>
			</div>
			<br/>";

			// NOTE: Deprecated
			/*echo "<div class='customer-standard-table-title-style-contact'>Contact Details</div>
			<div class='groupedcontent'>
			<table class=\"customer-standard-table-style\">
			<tr>
			<td width=\"160\">Title: </td>
			<td><input type=\"text\" name=\"mixContact_Title\" value=\"" . htmlspecialchars(DBO()->Contact->Title->Value) . "\" size=\"10\" maxlength=\"255\" /></td>
			</tr>
			<tr>
			<td>Job Title: </td>
			<td><input type=\"text\" name=\"mixContact_JobTitle\" value=\"" . htmlspecialchars(DBO()->Contact->JobTitle->Value) . "\" /></td>
			</tr>
			<tr>
			<td>First Name: </td>
			<td><input type=\"text\" name=\"mixContact_FirstName\" value=\"" . htmlspecialchars(DBO()->Contact->FirstName->Value) . "\" /></td>
			</tr>
			<tr>
			<td>Last Name: </td>
			<td><input type=\"text\" name=\"mixContact_LastName\" value=\"" . htmlspecialchars(DBO()->Contact->LastName->Value) . "\" /></td>
			</tr>
			<tr>
			<td>E-mail: </td>
			<td><input type=\"text\" name=\"mixContact_Email\" value=\"" . htmlspecialchars(DBO()->Contact->Email->Value) . "\" size=\"30\" maxlength=\"255\" /></td>
			</tr>
			<tr>
			<td>Phone: </td>
			<td><input type=\"text\" name=\"mixContact_Phone\" value=\"" . htmlspecialchars(DBO()->Contact->Phone->Value) . "\" /></td>
			</tr>
			<tr>
			<td>Mobile: </td>
			<td><input type=\"text\" name=\"mixContact_Mobile\" value=\"" . htmlspecialchars(DBO()->Contact->Mobile->Value) . "\" /></td>
			</tr>
			<tr>
			<td>Fax: </td>
			<td><input type=\"text\" name=\"mixContact_Fax\" value=\"" . htmlspecialchars(DBO()->Contact->Fax->Value) . "\" /></td>
			</tr>
			</TABLE>
			</div>";*/
			
			self::_contactDetailsFields();

			echo "<br/>
			<table class=\"customer-standard-table-style\">
			<tr>
				<td align=right><input type=\"button\" value=\"Cancel\" onclick=\"javascript:document.location = './'\" /> <input type=\"submit\" value=\"Update Details\" /></td>
			</tr>
			</table>
			<div id=\"error_box\"></div>
			";
		}

		echo "</div>\n";
	}
	
	private static function _contactDetailsFields() {
		$oAccountUser 	= Account_User::getForId(AuthenticatedUser()->_arrUser['id']);
		$D 				= new DOM_Factory();
		$D->getDOMDocument()->appendChild(
			$D->div(array('class' => 'customer-standard-table-title-style-contact'),
				"Contact Details"
			)
		);
		$D->getDOMDocument()->appendChild(
			$D->div(array('class' => 'grouped-content'),
				$D->table(array('class' => 'customer-standard-table-style'),
					$D->tr(
						$D->td(array('width' => 160),
							"Given Name: "
						),
						$D->td(
							$D->input(array('type' => 'text', 'name' => 'mixContact_GivenName', 'value' => $oAccountUser->given_name)) 
						)
					),
					$D->tr(
						$D->td("Family Name: "),
						$D->td(
							$D->input(array('type' => 'text', 'name' => 'mixContact_FamilyName', 'value' => $oAccountUser->family_name)) 
						)
					),
					$D->tr(
						$D->td("Email Address: "),
						$D->td(
							$D->input(array(
								'type' 		=> 'text', 
								'name' 		=> 'mixContact_Email', 
								'value' 	=> $oAccountUser->email, 
								'size' 		=> 30, 
								'maxlength' => 255
							)) 
						)
					)
				)
			)
		);
	
		echo $D->getDOMDocument()->saveHTML();
	}
}

?>
