<?php

class HtmlTemplate_Account_Create extends FlexHtmlTemplate
{
	
	const CREDIT_CARD_EXPIRY_LIMIT_YEARS	= 10;
	const MINIMUM_AGE_REQUIRED_FOR_ACCOUNT	= 18;
	const MAXIMUM_AGE_REQUIRED_FOR_ACCOUNT	= 150;
	
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		
		$intCurrentYear = (int)date("Y");
	
		echo "
		<div class='page-reset'>
		<form method='post' action='" . MenuItems::AddAccount() . "'>\n";

		// Message Notice
		if(array_key_exists("Associated", $_GET))
		{
			echo "
			<input type='hidden' name='Associated' value='{$_GET['Associated']}' />

			<div style='position: relative; float: right;'>
				<input onclick=document.location='" . MenuItems::AccountOverview(htmlspecialchars($_GET['Associated'])) . "' type='button' class='normal-button' name='View_Account' value='View Account'/>
			</div>
			<div><h1>Add Associated Account</h1></div>
			<div class='line-small'></div>
			<div class='MsgNoticeWide'>

				<strong><span class='Attention'>Attention</span> :</strong>
				The account's status will default to &quot;Pending Activation&quot;

			</div>\n";
		}
		
		if (!array_key_exists("Associated", $_GET))
		{
			echo "
			<input type='hidden' name='Contact[USE]' value='0' />

			<h1>Add Customer</h1>
			<div class='MsgNoticeWide'>

				<strong><span class='Attention'>Attention</span> :</strong>
				This form will add a new Customer. If you wish to add an Account to an existing Customer, 
				you will need to use the &quot;Add Associated Account&quot; link from the existing Account.<br />

				<strong><span class='Attention'>Attention</span> :</strong>
				The account's status will default to &quot;Pending Activation&quot;

			</div>\n";
		}

		// Account Details
		echo "
		<div class='Seperator'></div>
		<h2 class='Account'>Account Details</h2>
		<table class='form-layout'>
		<tr>
			<th><div class='Required'>*</div>Business Name:</th>
			<td><input type='text' name='Account[BusinessName]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>&nbsp;</div>Trading Name:</th>
			<td><input type='text' name='Account[TradingName]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>1</div>ABN:</th>
			<td><input type='text' name='Account[ABN]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>1</div>ACN:</th>
			<td><input type='text' name='Account[ACN]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>Address (Line 1):</th>
			<td><input type='text' name='Account[Address1]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>&nbsp;</div>Address (Line 2):</th>
			<td><input type='text' name='Account[Address2]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>Suburb:</th>
			<td><input type='text' name='Account[Suburb]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>Postcode:</th>
			<td><input type='text' name='Account[Postcode]' class='input-string' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>State:</th>
			<td>
				<select name='Account[State]'>
					<option value=''></option>";
				
				// Generate a dropdown menu of available states, eg. QLD, NSW, VIC
				foreach ($this->mxdDataToRender['arrStates'] as $oState)
				{
					echo "<option value='{$oState->id}'>{$oState->name}</option>\n";
				}
				
				echo "
				</select>
			</td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>Customer Group:</th>
			<td>
				<select name='Account[CustomerGroup]'>
					<option value=''></option>";
				
				// Generate a dropdown menu of available customer groups
				foreach ($this->mxdDataToRender['arrCustomerGroups'] as $oCustomerGroup)
				{
					echo "<option value='{$oCustomerGroup->id}'>{$oCustomerGroup->name}</option>\n";
				}
				
				echo "
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
			<th><div class='Required'>&nbsp;</div>NDD Fee:</th>
			<td>
				<input type='checkbox' name='Account[DisableDDR]' value='1' />
				Do NOT charge an admin fee (non direct debit fee)
				<div class='SmallSeperator'></div>
			</td>
		</tr>
		<tr>
			<th><div class='Required'>&nbsp;</div>Late Payments:</th>
			<td>
				<ul>
					<li><input type='radio' name='Account[DisableLatePayment]' value='0' /> Charge a late payment fee</li>
					<li><input type='radio' name='Account[DisableLatePayment]' value='-1' /> Don't charge a late payment fee on the next invoice</li>
					<li>
						<input type='radio' name='Account[DisableLatePayment]' value='1' /> Never charge a late payment fee
						<div class='SmallSeperator'></div>
					</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>Delivery Method:</th>
			<td>
				<select name='Account[DeliveryMethod]'>";
				
				// Generate a dropdown menu of available delivery methods, eg. Post, Email
				foreach ($this->mxdDataToRender['arrDeliveryMethods'] as $oDeliveryMethod)
				{
					echo "<option value='{$oDeliveryMethod->id}'>{$oDeliveryMethod->name}</option>\n";
				}
				
				echo "
				</select>
				<div class='SmallSeperator'></div>
			</td>
		</tr>
		<tr>
			<th><div class='Required'>*</div>Payment Method:</th>
			<td>
				<ul>
					<li>
						<input type='radio' name='Account[BillingType]' value='3' /> Invoice
					</li>
					<li>
						<div class='SmallSeperator'></div>
						<input type='radio' name='Account[BillingType]' value='1' /> Direct Debit - from Bank Account
						<table>
						<tr>
							<th style='width: 175px;'><div class='Required'>#</div>Bank Name:</th>
							<td><input name='DDR[BankName]' maxlength='255' value='' type='text' /></td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>BSB #:</th>
							<td><input name='DDR[BSB]' maxlength='6' value='' type='text' /></td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>Account #:</th>
							<td><input name='DDR[AccountNumber]' maxlength='9' value='' type='text' /></td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>Account Name:</th>
							<td><input name='DDR[AccountName]' maxlength='255' value='' type='text' /></td>
						</tr>
						</table>
					</li>
					<li>
						<div class='SmallSeperator'></div>
						<input type='radio' name='Account[BillingType]' value='2' /> Direct Debit - from Credit Card
						<table>
						<tr>
							<th style='width: 175px;'><div class='Required'>#</div>Card Type:</th>
							<td>
								<select tabindex='23' name='CC[CardType]'>";

								// Generate a dropdown menu of available credit card types.
								foreach ($this->mxdDataToRender['arrCreditCardTypes'] as $oCreditCardType)
								{
									echo "<option value='{$oCreditCardType->id}'>{$oCreditCardType->name}</option>\n";
								}
								
								echo "
								</select>
							</td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>Card Holder Name:</th>
							<td><input name='CC[Name]' maxlength='255' value='' type='text' /></td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>Credit Card Number:</th>
							<td><input name='CC[CardNumber]' maxlength='20' value='' type='text' /></td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>Expiration Date:</th>
							<td>
								<select tabindex='26' name='CC[ExpMonth]'>";
								
									// Generate 12 months in the year
									for ($i=1; $i<13; $i++)
									{
										echo "<option value='{$i}'>$i</option>\n";
									}
								
								echo "
								</select> / 
								<select tabindex='27' name='CC[ExpYear]'>";
									
									// Generate credit card expiry year options.
									for ($i=$intCurrentYear; $i<$intCurrentYear+self::CREDIT_CARD_EXPIRY_LIMIT_YEARS; $i++)
									{
										echo "<option value='{$i}'>{$i}</option>\n";
									}
								
								echo "
								</select>
							</td>
						</tr>
						<tr>
							<th><div class='Required'>#</div>CVV #:</th>
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
		<table class='form-layout'>";


		if(array_key_exists("Associated", $_GET))
		{
			
			echo "
			<tr>
				<th></th>
				<td>
					<input type='radio' name='Contact[USE]' value='1' />
					Select an existing contact from the list below:
				</td>
			</tr>
				<th><div class='Required'></div></th>
				<td>
					<select name='Contact[Id]'>";
	
					// Generate a dropdown menu of existing contacts
					foreach ($this->mxdDataToRender['arrAccountGroupContacts'] as $iId=>$oAccountGroupContact)
					{
						echo "<option value='{$oAccountGroupContact->id}'>{$oAccountGroupContact->name}{$iId}</option>\n";
					}
					
					echo "
					</select>
				</td>
			</tr>
				<th><div class='Required'></th>
				<td>
						<div class='SmallSeperator'></div>
						<input type='radio' name='Contact[USE]' value='0' />
						Create a new Contact using the following details:</ul>
				</td>
			</tr>";
			
		}
		
		echo "
		<tr>
			<th><div class='Required'>#</div>Title:</th>
			<td>
				<select name='Contact[Title]'>
					<option value=''></option>";

					// Generate a dropdown menu of contact titles, eg. Mr, Dr
					foreach ($this->mxdDataToRender['arrContactTitles'] as $oContactTitle)
					{
						echo "<option value='{$oContactTitle->id}'>{$oContactTitle->name}</option>\n";
					}
				
				echo "
				</select>
			</td>
		</tr>
		<tr>
			<th><div class='Required'>#</div>First Name:</th>
			<td><input type='text' name='Contact[FirstName]' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>#</div>Last Name:</th>
			<td><input type='text' name='Contact[LastName]' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>#</div>Date of Birth:</th>
			<td>
				<select name='Contact[DOB][day]'>
					<option value=''>DD</option>";
	
					// Generate 31 days in a month
					for ($i=1; $i<32; $i++)
					{
						echo "<option value='{$i}'>{$i}</option>\n";
					}
					
				echo "
				</select>
				<select name='Contact[DOB][month]'>
					<option value=''>MM</option>";
	
					// Generate 12 months in the year
					for ($i=1; $i<13; $i++)
					{
						echo "<option value='{$i}'>{$i}</option>\n";
					}
					
				echo "
				</select>
				<select name='Contact[DOB][year]'>
					<option value=''>YYYY</option>";
					
					// Generate list of years for date of birth selection
					$intStartMinimumAgeRequired = $intCurrentYear-self::MINIMUM_AGE_REQUIRED_FOR_ACCOUNT;
					
					for ($i=$intStartMinimumAgeRequired; $i>$intStartMinimumAgeRequired-self::MAXIMUM_AGE_REQUIRED_FOR_ACCOUNT; $i--)
					{
						echo "<option value='{$i}'>{$i}</option>\n";
					}
				
				echo "
				</select>
			</td>
		</tr>
		<tr>
			<th><div class='Required'>&nbsp;</div>Job Title:</th>
			<td><input type='text' name='Contact[JobTitle]' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>#</div>Email Address:</th>
			<td><input type='text' name='Contact[Email]' maxlength='255' /></td>
		</tr>
		<tr>
			<th><div class='Required'>2</div>Phone Number:</th>
			<td><input type='text' name='Contact[Phone]' maxlength='25' /></td>
		</tr>
		<tr>
			<th><div class='Required'>2</div>Mobile Number:</th>
			<td><input type='text' name='Contact[Mobile]' maxlength='25' /></td>
		</tr>
		<tr>
			<th><div class='Required'>&nbsp;</div>Fax Number:</th>
			<td><input type='text' name='Contact[Fax]' maxlength='25' /></td>
		</tr>
		<tr>
			<th><div class='Required'>#</div>Password:</th>
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
					<li><div class='Required'>*</div>: Required field</li>
					<li><div class='Required'>1</div>: One or both fields required</li>
					<li><div class='Required'>2</div>: One or both fields required only when the associated option is selected</li>
					<li><div class='Required'>#</div>: Required only when the associated option is selected</li>
				</ul>
			</td>
		</tr>
		</table>\n";

		// Add account or Close page
		echo "
		<div class='Seperator'></div>
		<div style='text-align: center;'>
			<input type='submit' class='normal-button' name='Add_Account_Submit' value='Save' />
			<input type='submit' class='normal-button' name='Add_Account_Cancel' value='Cancel' />
		</div>";
		
		// End page reset wrapper
		echo "
		</form>
		</div>";
		
	}
	
}