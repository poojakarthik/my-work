<?php

class HtmlTemplate_Account_Create extends FlexHtmlTemplate
{
	
	const CREDIT_CARD_EXPIRY_LIMIT_YEARS	= 10;
	const MINIMUM_AGE_REQUIRED_FOR_ACCOUNT	= 18;
	const MAXIMUM_AGE_REQUIRED_FOR_ACCOUNT	= 150;
	
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript("reflex_validation");
		$this->LoadJavascript("account_create");
		$this->LoadJavascript("credit_card_payment");
		$this->LoadJavascript("sp/validation");
		$this->LoadJavascript("reflex_template");
	}

	public function Render()
	{
		
		$intCurrentYear = (int)date("Y");
		
		echo "
		<div class='page-reset'>
		<form method='post' autocomplete='off' action='reflex.php/Account/Save/' id='account-create' name='account-create' onsubmit='return this.oAccountCreate.submit();'>\n";

		// Message Notice
		if(array_key_exists("Associated", $_GET))
		{			
			echo "
			<input type='hidden' name='Associated' value='{$_GET['Associated']}' />

			<div style='position: relative; float: right;'>
				<input onclick=document.location='" . MenuItems::AccountOverview(htmlspecialchars($_GET['Associated'])) . "' type='button' class='normal-button' name='View_Account' value='View Account'/>
			</div>
			<h1>Add Associated Account</h1>
			<div class='line-small'></div>
			<div style='margin-bottom: 1em;' class='GroupedContent' id='filterOptions'>

				<strong><span class='Attention'>Attention</span> :</strong>
				The account's status will default to &quot;Pending Activation&quot;

			</div>\n";
		}
		
		if (!array_key_exists("Associated", $_GET))
		{
			echo "
			<input type='hidden' name='Contact[USE]' value='0' />

			<h1>Add Customer</h1>
			<div class='line-small'></div>
			<div style='margin-bottom: 1em;' class='GroupedContent' id='filterOptions'>

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

		<table class='form-layout reflex'>
			<caption>
				<div class='caption_bar' id='caption_bar'>
					<div class='caption_title' id='caption_title'>Account Details</div>
				</div>
			</caption>
			<thead>
				<tr>
					<th colspan='2'>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<th><div class='Required'>*</div>Business Name:</th>
				<td><input type='text' name='Account[BusinessName]' class='input-string' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>&nbsp;</div>Trading Name:</th>
				<td><input type='text' name='Account[TradingName]' class='input-string' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>&nbsp;</div>ABN:</th>
				<td><input type='text' name='Account[ABN]' id='Account[ABN]' class='input-string' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>&nbsp;</div>ACN:</th>
				<td><input type='text' name='Account[ACN]' id='Account[ACN]' class='input-string' maxlength='255' /></td>
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
					<select name='Account[State]' id='Account[State]' class='input-string'>
						<option value=''></option>";
					
					// Generate a dropdown menu of available states, eg. QLD, NSW, VIC
					foreach ($this->mxdDataToRender['arrStates'] as $oState)
					{
						echo "<option value='{$oState->code}'>{$oState->name}</option>\n";
					}
					
					echo "
					</select>
				</td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Customer Group:</th>
				<td>
					<select name='Account[CustomerGroup]' class='input-string' onchange='oAccountCreate.customerGroupChange()'>
						<option value=''></option>";
					
					// Generate a dropdown menu of available customer groups
					foreach ($this->mxdDataToRender['arrCustomerGroups'] as $oCustomerGroup)
					{
						echo "<option value='{$oCustomerGroup->id}' data-default-account-class-id='{$oCustomerGroup->default_account_class_id}'>{$oCustomerGroup->name}</option>\n";
					}
					
					echo "
					</select>
				</td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Account Class:</th>
				<td>
					<select name='Account[account_class_id]' class='input-string' onchange='oAccountCreate.accountClassChange()'>
						<option value=''></option>";
					
					// Generate a dropdown menu of account classes
					foreach ($this->mxdDataToRender['arrAccountClasses'] as $oAccountClass)
					{
						echo "<option value='{$oAccountClass->id}' data-collection-scenario-id='{$oAccountClass->collection_scenario_id}'>{$oAccountClass->name} ({$oAccountClass->description})</option>\n";
					}
					
					echo "
					</select>
				</td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Collection Scenario:</th>
				<td>
					<select name='Account[collection_scenario_id]' class='input-string' onchange='oAccountCreate.scenarioChange()'>
						<option value=''></option>";
					
					// Generate a dropdown menu of collection scenarios
					foreach ($this->mxdDataToRender['arrScenarios'] as $oScenario)
					{
						echo "<option value='{$oScenario->id}'>{$oScenario->name} ({$oScenario->description})</option>\n";
					}
					
					echo "
					</select>
				</td>
			</tr>
			</tbody>
			<tfoot>
				<tr>
					<th colspan='2'>&nbsp;</th>
				</tr>
			</tfoot>
		</table>\n";
	
		// Billing Details
		echo "
		<div class='Seperator'></div>
		<table class='form-layout reflex'>
			<caption>
				<div class='caption_bar' id='caption_bar'>
					<div class='caption_title' id='caption_title'>Billing Details</div>
				</div>
			</caption>
			<thead>
				<tr>
					<th colspan='2'>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
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
						<li><input type='radio' name='Account[DisableLatePayment]' value='0' CHECKED /> Charge a late payment fee</li>
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
					<select name='Account[DeliveryMethod]' class='input-string'>
						<option value=''>Please Select</option>";
					
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
							<input type='radio' name='Account[BillingType]' id='Account[BillingType]' value='" . BILLING_TYPE_ACCOUNT . "' /> Invoice
						</li>
						<li>
							<div class='SmallSeperator'></div>
							<input type='radio' name='Account[BillingType]' id='Account[BillingType]' value='" . BILLING_TYPE_DIRECT_DEBIT . "' /> Direct Debit - from Bank Account
							<table>
							<tr>
								<th style='width: 175px;'><div class='Required'>*</div>Bank Name:</th>
								<td><input name='DDR[BankName]' id='DDR[BankName]' maxlength='255' value='' type='text' /></td>
							</tr>
							<tr>
								<th><div class='Required'>*</div>BSB #:</th>
								<td><input name='DDR[BSB]' id='DDR[BSB]' maxlength='6' value='' type='text' /></td>
							</tr>
							<tr>
								<th><div class='Required'>*</div>Account #:</th>
								<td><input name='DDR[AccountNumber]' id='DDR[AccountNumber]' maxlength='9' value='' type='text' /></td>
							</tr>
							<tr>
								<th><div class='Required'>*</div>Account Name:</th>
								<td><input name='DDR[AccountName]' id='DDR[AccountName]' maxlength='255' value='' type='text' /></td>
							</tr>
							</table>
						</li>
						<li>
							<div class='SmallSeperator'></div>
							<input type='radio' name='Account[BillingType]' id='Account[BillingType]' value='" . BILLING_TYPE_CREDIT_CARD . "' checked /> Direct Debit - from Credit Card
							<table>
								<tr>
									<th style='width: 175px;'><div class='Required'>*</div>Card Type:</th>
									<td>
										<select tabindex='23' name='CC[CardType]' id='CC[CardType]'>";
		
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
									<th><div class='Required'>*</div>Card Holder Name:</th>
									<td><input name='CC[Name]' id='CC[Name]' maxlength='255' value='' type='text' /></td>
								</tr>
								<tr>
									<th><div class='Required'>*</div>Credit Card Number:</th>
									<td><input name='CC[CardNumber]' id='CC[CardNumber]' maxlength='20' value='' type='text' /></td>
								</tr>
								<tr>
									<th><div class='Required'>*</div>Expiration Date:</th>
									<td>
										<select tabindex='26' name='CC[ExpMonth]' id='CC[ExpMonth]'>";
										
											// Generate 12 months in the year
											for ($i=1; $i<13; $i++)
											{
												echo "<option value='{$i}'>$i</option>\n";
											}
										
										echo "
										</select> / 
										<select tabindex='27' name='CC[ExpYear]' id='CC[ExpYear]'>";
											
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
									<th><div class='Required'>*</div>CVV #:</th>
									<td><input name='CC[CVV]' id='CC[CVV]' maxlength='4' value='' type='text' /></td>
								</tr>
							</table>
						</li>
					</ul>
				</td>
			</tr>
			</tbody>
			<tfoot>
				<tr>
					<th colspan='2'>&nbsp;</th>
				</tr>
			</tfoot>
		</table>\n";
		
				
		// Primary Contact Details
		if(!array_key_exists("Associated", $_GET))
		{
			echo "<input type='radio' name='Contact[USE]' id='Contact[USE]' value='0' style='display: none;' checked />";	
		}
		echo "
		<div class='Seperator'></div>
		<table class='form-layout reflex'>
			<caption>
				<div class='caption_bar' id='caption_bar'>
					<div class='caption_title' id='caption_title'>Primary Contact Details</div>
				</div>
			</caption>
			<thead>
				<tr>
					<th colspan='2'>&nbsp;</th>
				</tr>
			</thead>
			<tbody>";
		if(array_key_exists("Associated", $_GET))
		{
			// Check query string for primary contact
			$iPrimaryContactId	= (isset($_GET['Contact']) ? $_GET['Contact'] : false);
			
			echo "
			<tr>
				<th></th>
				<td>
					<input type='radio' name='Contact[USE]' id='Contact[USE]' value='1' checked />
					Select an existing contact from the list below:
				</td>
			</tr>
			<tr>
				<th><div class='Required'></div></th>
				<td>
					<select name='Contact[Id]' id='Contact[Id]'>
						<option value='false'></option>";

					// Generate a dropdown menu of existing contacts
					foreach ($this->mxdDataToRender['arrAssociatedContacts'] as $iId=>$oAssociatedContact)
					{
						// Pre-select the option if it is the primary contact given in the query string
						$sSelected	= (($iPrimaryContactId && $iPrimaryContactId == $iId) ? 'selected' : '');
						echo "<option value='{$oAssociatedContact['Id']}' $sSelected>{$oAssociatedContact['FirstName']} {$oAssociatedContact['LastName']}</option>\n";
					}
					
					echo "
					</select>
				</td>
			</tr>
			<tr>
				<th><div class='Required'></th>
				<td>
					<div class='SmallSeperator'></div>
					<input type='radio' name='Contact[USE]' id='Contact[USE]' value='0' />
					Create a new Contact using the following details:
				</td>
			</tr>";
			
		}
		
		echo "
			<tr>
				<th><div class='Required'>*</div>Title:</th>
				<td>
					<select name='Contact[Title]' id='Contact[Title]'>
						<option value=''></option>";
	
						// Generate a dropdown menu of contact titles, eg. Mr, Dr
						foreach ($this->mxdDataToRender['arrContactTitles'] as $oContactTitle)
						{
							// echo "<option value='{$oContactTitle->id}'>{$oContactTitle->name}</option>\n";
							echo "<option value='{$oContactTitle->name}'>{$oContactTitle->name}</option>\n";
						}
					
					echo "
					</select>
				</td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>First Name:</th>
				<td><input type='text' name='Contact[FirstName]' id='Contact[FirstName]' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Last Name:</th>
				<td><input type='text' name='Contact[LastName]' id='Contact[LastName]' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Date of Birth:</th>
				<td>
					<select name='Contact[DOB][Day]' id='Contact[DOB][Day]'>
						<option value=''>DD</option>";
		
						// Generate 31 days in a month
						for ($i=1; $i<32; $i++)
						{
							echo "<option value='{$i}'>{$i}</option>\n";
						}
						
					echo "
					</select>
					<select name='Contact[DOB][Month]' id='Contact[DOB][Month]'>
						<option value=''>MM</option>";
		
						// Generate 12 months in the year
						for ($i=1; $i<13; $i++)
						{
							echo "<option value='{$i}'>{$i}</option>\n";
						}
						
					echo "
					</select>
					<select name='Contact[DOB][Year]' id='Contact[DOB][Year]'>
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
				<td><input type='text' name='Contact[JobTitle]' id='Contact[JobTitle]' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Email Address:</th>
				<td><input type='text' name='Contact[Email]' id='Contact[Email]' maxlength='255' /></td>
			</tr>
			<tr>
				<th><div class='Required'>&nbsp;</div>Phone Number:</th>
				<td><input type='text' name='Contact[Phone]' id='Contact[Phone]' maxlength='25' /></td>
			</tr>
			<tr>
				<th><div class='Required'>&nbsp;</div>Mobile Number:</th>
				<td><input type='text' name='Contact[Mobile]' id='Contact[Mobile]' maxlength='25' /></td>
			</tr>
			<tr>
				<th><div class='Required'>&nbsp;</div>Fax Number:</th>
				<td><input type='text' name='Contact[Fax]' id='Contact[Fax]' maxlength='25' /></td>
			</tr>
			<tr>
				<th><div class='Required'>*</div>Password:</th>
				<td><input type='text' name='Contact[Password]' id='Contact[Password]' maxlength='255' /></td>
			</tr>
			</tbody>
			<tfoot>
				<tr>
					<th colspan='2'>
						<input type='submit' class='normal-button' name='Add_Account_Submit' value='Save' class='reflex-button' />
						<input onclick=document.location='" . $this->mxdDataToRender['strCancelURI'] . "' type='button' class='normal-button' name='Add_Account_Cancel' value='Cancel'/>
					</th>
				</tr>
			</tfoot>
		</table>
		<div class='Seperator'></div>

		</form>
		</div>
		<script type='text/javascript'>

			oAccountCreate	= new Account_Create(\$ID('account-create'));
			
		</script>";
		
	}
	
}