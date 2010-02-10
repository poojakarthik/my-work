<?php

class HtmlTemplate_Account_Create extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		
		$strAccountCreateLink = MenuItems::ManageSales();
		// $_GET['Associated']=1000167166
		
		echo "<form method='POST' action='{$strAccountCreateLink}'>\n";
		
		if(array_key_exists("Associated", $_GET))
		{
			echo "<h1>Add Associated Account</h1>";
			echo "<input type='hidden' name='Associated' value='{$_GET['Associated']}'>\n";

		}
		if(!array_key_exists("Associated", $_GET))
		{
			echo "<h1>Add Customer</h1>";
			echo "<strong><span class='Attention'>Attention</span> :</strong>
			This form will add a new Customer. If you wish to add an Account to an existing Customer, 
			you will need to use the &quot;Add Associated Account&quot; link from the existing Account.\n";

		}
		
		// Account Details
		echo "
		<h2 class='Account'>Account Details</h2>
		<div class='wide-form'>
		<table>
		<tr>
			<td>Business Name</td>
			<td><input type='text' name='Account[BusinessName]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Trading Name</td>
			<td><input type='text' name='Account[TradingName]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>ABN</td>
			<td><input type='text' name='Account[ABN]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>ACN</td>
			<td><input type='text' name='Account[ACN]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Address (Line 1)</td>
			<td><input type='text' name='Account[Address1]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Address (Line 2)</td>
			<td><input type='text' name='Account[Address2]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Suburb</td>
			<td><input type='text' name='Account[Suburb]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Postcode</td>
			<td><input type='text' name='Account[Postcode]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>State</td>
			<td><select name='Account[State]'><option value=''></option></select></td>
		</tr>
		<tr>
			<td>Customer Group</td>
			<td><select name='Account[CustomerGroup]'><option value=''></option></select></td>
		</tr>
		</table>
		</div>\n";
		
	
		// Billing Details
		echo "
		<h2 class='Account'>Billing Details</h2>
		<div class='wide-form'>
		<table>
		<tr>
			<td>NDD Fee</td>
			<td>
				<input type='checkbox' name='Account[DisableDDR]' value='1'>
				Do NOT charge an admin fee (non direct debit fee)
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top;'>Late Payments</td>
			<td>
				<ul style='list-style-type: none; margin: 0; padding: 0;'>
					<li><input type='radio' name='Account[DisableLatePayment]' value='0'> Charge a late payment fee</li>
					<li><input type='radio' name='Account[DisableLatePayment]' value='-1'> Don't charge a late payment fee on the next invoice</li>
					<li><input type='radio' name='Account[DisableLatePayment]' value='1'> Never charge a late payment fee</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>Billing Method</td>
			<td>
				<select name='Account[BillingMethod]'>
					<option value=''></option>
				</select>
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top;'>Payment Method</td>
			<td>
				<ul style='list-style-type: none; margin: 0; padding: 0;'>
					<li><input type='radio' name='Account[BillingType]' value='3'> Invoice</li>
					<li><input type='radio' name='Account[BillingType]' value='1'> Direct Debit - from Bank Account</li>
					<li><input type='radio' name='Account[BillingType]' value='2'> Direct Debit - from Credit Card</li>
				</ul>
			</td>
		</tr>
		</table>
		</div>\n";
		

		// Primary Contact Details
		echo "
		<h2 class='Account'>Primary Contact Details</h2>
		<div class='wide-form'>
		<table>
		<tr>
			<td style='vertical-align: top;'></td>
			<td>
				<ul style='list-style-type: none; margin: 0; padding: 0;'>
					<li><input type='radio' name='Contact[USE]' value='1'> Select an existing contact from the list below:</li>
					<li><input type='radio' name='Contact[USE]' value='0'> Create a new Contact using the following details:</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>Title</td>
			<td><select name='Contact[Title]'><option value=''></option></select></td>
		</tr>
		<tr>
			<td>First Name</td>
			<td><input type='text' name='Contact[FirstName]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Last Name</td>
			<td><input type='text' name='Contact[LastName]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Date of Birth</td>
			<td>
				<select name='Contact[DOB][day]'></select>
				<select name='Contact[DOB][month]'></select>
				<select name='Contact[DOB][year]'></select>
			</td>
		</tr>
		<tr>
			<td>Job Title</td>
			<td><input type='text' name='Contact[JobTitle]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Email Address</td>
			<td><input type='text' name='Contact[Email]' class='input-string' maxlength='255'></td>
		</tr>
		<tr>
			<td>Phone Number</td>
			<td><input type='text' name='Contact[Phone]' class='input-string' maxlength='25'></td>
		</tr>
		<tr>
			<td>Mobile Number</td>
			<td><input type='text' name='Contact[Mobile]' class='input-string' maxlength='25'></td>
		</tr>
		<tr>
			<td>Fax Number</td>
			<td><input type='text' name='Contact[Fax]' class='input-string' maxlength='25'></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input type='text' name='Contact[Password]' class='input-string' maxlength='255'></td>
		</tr>
		</table>
		</div>\n";
		
		
		// Legend
		echo "
		<h2 class='Account'>Primary Contact Details</h2>
		<div class='wide-form'>
		<table>
		<tr>
			<td style='vertical-align: top;'></td>
			<td>
				<ul style='list-style-type: none; margin: 0; padding: 0;'>
					<li><strong><span class='Red'>* </span></strong>: Required field</li>
					<li><strong><span class='Red'><sup>1</sup> </span></strong>: One or both fields required</li>
					<li><strong><span class='Red'><sup>2</sup> </span></strong>: One or both fields required only when the associated option is selected</li>
					<li><strong><span class='Red'><sup>#</sup> </span></strong>: Required only when the associated option is selected</li>
				</ul>
			</td>
		</tr>
		</table>
		</div>\n";
	}
	
}