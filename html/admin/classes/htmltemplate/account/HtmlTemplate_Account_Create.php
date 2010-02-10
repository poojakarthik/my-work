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
		
		echo "
		<div class='page-reset'>
		<form method='post' action='{$strAccountCreateLink}'>\n";
		
		// Message Notice
		if(array_key_exists("Associated", $_GET))
		{
			echo "
			<h1>Add Associated Account</h1>
			<input type='hidden' name='Associated' value='{$_GET['Associated']}' />";
		}
		
		if(!array_key_exists("Associated", $_GET))
		{
			echo "
			<h1>Add Customer</h1>
			<div class='MsgNoticeWide'>
				<strong><span class='Attention'>Attention</span> :</strong>
				This form will add a new Customer. If you wish to add an Account to an existing Customer, 
				you will need to use the &quot;Add Associated Account&quot; link from the existing Account.
			</div>\n";
		}

		// Account Details
		echo "
		<div class='Seperator'></div>
		<h2 class='Account'>Account Details</h2>
		<table class='form-layout'>
		<tr>
			<th>Business Name:</th>
			<td><input type='text' name='Account[BusinessName]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Trading Name:</th>
			<td><input type='text' name='Account[TradingName]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>ABN:</th>
			<td><input type='text' name='Account[ABN]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>ACN:</th>
			<td><input type='text' name='Account[ACN]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Address (Line 1):</th>
			<td><input type='text' name='Account[Address1]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Address (Line 2):</th>
			<td><input type='text' name='Account[Address2]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Suburb:</th>
			<td><input type='text' name='Account[Suburb]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Postcode:</th>
			<td><input type='text' name='Account[Postcode]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th>State:</th>
			<td>
				<select name='Account[State]'>
					<option value=''></option>
				</select>
			</td>
		</tr>
		<tr>
			<th>Customer Group:</th>
			<td>
				<select name='Account[CustomerGroup]'>
					<option value=''></option>
				</select>
			</td>
		</tr>
		</table>\n";
	
		// Billing Details
		echo "
		<div class='Seperator'></div>
		<h2 class='Account'>Billing Details</h2>
		<table class='form-layout'>
		<tr>
			<th>NDD Fee:</th>
			<td>
				<input type='checkbox' name='Account[DisableDDR]' value='1' />
				Do NOT charge an admin fee (non direct debit fee)
			</td>
		</tr>
		<tr>
			<th>Late Payments:</th>
			<td>
				<ul>
					<li><input type='radio' name='Account[DisableLatePayment]' value='0' /> Charge a late payment fee</li>
					<li><input type='radio' name='Account[DisableLatePayment]' value='-1' /> Don't charge a late payment fee on the next invoice</li>
					<li><input type='radio' name='Account[DisableLatePayment]' value='1' /> Never charge a late payment fee</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th>Billing Method:</th>
			<td>
				<select name='Account[BillingMethod]'>
					<option value=''></option>
				</select>
			</td>
		</tr>
		<tr>
			<th>Payment Method:</th>
			<td>
				<ul>
					<li><input type='radio' name='Account[BillingType]' value='3' /> Invoice</li>
					<li>
						<input type='radio' name='Account[BillingType]' value='1' /> Direct Debit - from Bank Account
						<table>
						<tr>
							<th>Bank Name:</th>
							<td><input name='DDR[BankName]' maxlength='255' value='' type='text' /></td>
						</tr>
						<tr>
							<th>BSB #:</th>
							<td><input name='DDR[BSB]' maxlength='6' value='' type='text' /></td>
						</tr>
						<tr>
							<th>Account #:</th>
							<td><input name='DDR[AccountNumber]' maxlength='9' value='' type='text' /></td>
						</tr>
						<tr>
							<th>Account Name:</th>
							<td><input name='DDR[AccountName]' maxlength='255' value='' type='text' /></td>
						</tr>
						</table>
					</li>
					<li>
						<input type='radio' name='Account[BillingType]' value='2' /> Direct Debit - from Credit Card
						<table>
						<tr>
							<th>Card Type:</th>
							<td>
								<select tabindex='23' name='CC[CardType]'>
									<option value='1'>Visa</option>
									<option value='2'>MasterCard</option>
									<option value='4'>American Express</option>
									<option value='5'>Diners Club</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>Card Holder Name:</th>
							<td><input name='CC[Name]' maxlength='255' value='' type='text' /></td>
						</tr>
						<tr>
							<th>Credit Card Number:</th>
							<td><input name='CC[CardNumber]' maxlength='20' value='' type='text' /></td>
						</tr>
						<tr>
							<th>Expiration Date:</th>
							<td>
								<select tabindex='26' name='CC[ExpMonth]'>
									<option value='1'>1</option>
									<option value='2'>2</option>
									<option value='3'>3</option>
									<option value='4'>4</option>
									<option value='5'>5</option>
									<option value='6'>6</option>
									<option value='7'>7</option>
									<option value='8'>8</option>
									<option value='9'>9</option>
									<option value='10'>10</option>
									<option value='11'>11</option>
									<option value='12'>12</option>
								</select> / 
								<select tabindex='27' name='CC[ExpYear]'>
									<option value='2008'>2008</option>
									<option value='2009'>2009</option>
									<option value='2010'>2010</option>
									<option value='2011'>2011</option>
									<option value='2012'>2012</option>
									<option value='2013'>2013</option>
									<option value='2014'>2014</option>
									<option value='2015'>2015</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>CVV #:</th>
							<td><input name='CC[CVV]' maxlength='255' value='' type='text' /></td>
						</tr>
						</table>
					</li>
				</ul>
			</td>
		</tr>
		</table>\n";

		// Primary Contact Details
		echo "
		<div class='Seperator'></div>
		<h2 class='Account'>Primary Contact Details</h2>
		<table class='form-layout'>
		<tr>
			<th></th>
			<td>
				<ul>
					<li><input type='radio' name='Contact[USE]' value='1' /> Select an existing contact from the list below:</li>
					<li><input type='radio' name='Contact[USE]' value='0' /> Create a new Contact using the following details:</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th>Title:</th>
			<td>
				<select name='Contact[Title]'>
					<option value=''></option>
				</select>
			</td>
		</tr>
		<tr>
			<th>First Name:</th>
			<td><input type='text' name='Contact[FirstName]' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Last Name:</th>
			<td><input type='text' name='Contact[LastName]' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Date of Birth:</th>
			<td>
				<select name='Contact[DOB][day]'>
					<option></option>
				</select>
				<select name='Contact[DOB][month]'>
					<option></option>
				</select>
				<select name='Contact[DOB][year]'>
					<option></option>
				</select>
			</td>
		</tr>
		<tr>
			<th>Job Title:</th>
			<td><input type='text' name='Contact[JobTitle]' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Email Address:</th>
			<td><input type='text' name='Contact[Email]' maxlength='255' /></td>
		</tr>
		<tr>
			<th>Phone Number:</th>
			<td><input type='text' name='Contact[Phone]' maxlength='25' /></td>
		</tr>
		<tr>
			<th>Mobile Number:</th>
			<td><input type='text' name='Contact[Mobile]' maxlength='25' /></td>
		</tr>
		<tr>
			<th>Fax Number:</th>
			<td><input type='text' name='Contact[Fax]' maxlength='25' /></td>
		</tr>
		<tr>
			<th>Password:</th>
			<td><input type='text' name='Contact[Password]' maxlength='255' /></td>
		</tr>
		</table>\n";

		// Legend
		echo "
		<div class='Seperator'></div>
		<h2 class='Account'>Legend</h2>
		<table class='form-layout'>
		<tr>
			<th></th>
			<td>
				<ul>
					<li><strong><span class='Red'>* </span></strong>: Required field</li>
					<li><strong><span class='Red'><sup>1</sup> </span></strong>: One or both fields required</li>
					<li><strong><span class='Red'><sup>2</sup> </span></strong>: One or both fields required only when the associated option is selected</li>
					<li><strong><span class='Red'><sup>#</sup> </span></strong>: Required only when the associated option is selected</li>
				</ul>
			</td>
		</tr>
		</table>\n";

		// Add account or Close page
		echo "
		<div class='Seperator'></div>
		<div style='text-align: center;'>
			<input type='button' name='Add_Account_Submit' value='Save' />
			<input type='button' name='Add_Account_Cancel' value='Cancel' />
		</div>";
		
		// End page reset wrapper
		echo "
		</form>
		</div>";
		
	}
	
}