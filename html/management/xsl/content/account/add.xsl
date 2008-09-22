<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!--Page for adding a new customer or associated account -->
		
		<!-- Heading 1 -->
		<xsl:choose>
			<!-- If add associated account page -->
			<xsl:when test="/Response/AccountGroup">
			<!--TODO!Bash! add associated account page needs menu -->
				<h1>Add Associated Account</h1>
			</xsl:when>
			<xsl:otherwise>
			<!--Else add customer page-->
				<h1>Add Customer</h1>
			</xsl:otherwise>
		</xsl:choose>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<!-- Enumeration Errors -->
					<xsl:when test="/Response/Error = 'Account CustomerGroup'">
						Please select a valid Customer Group.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Billing Method'">
						Please select a valid Billing Method.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard CardType'">
						Please select a valid Credit Card Type.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account State'">
						Please select a valid State.
					</xsl:when>
					
					<!-- Account -->
					<xsl:when test="/Response/Error = 'Account BusinessName'">
						Please enter a Business Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account ABN-ACN'">
						Please enter an ABN or ACN.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account ABN Invalid'">
						Please enter a valid ABN.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account ACN Invalid'">
						Please enter a valid ACN.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account Address'">
						Please enter an Address.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account Suburb'">
						Please enter a Suburb.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account Postcode'">
						Please enter a Postcode.
					</xsl:when>
					
					<!-- Direct Debit -->
					<xsl:when test="/Response/Error = 'DirectDebit BankName'">
						Please enter a Direct Debit Bank Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit BSB'">
						Please enter a Direct Debit BSB #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit BSB Invalid'">
						Please enter a valid Direct Debit BSB #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit AccountNumber'">
						Please enter a Direct Debit Account #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit AccountNumber Invalid'">
						Please enter a valid Direct Debit Account #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit AccountName'">
						Please enter a Direct Debit Account Name.
					</xsl:when>
					
					<!-- Credit Card -->
					<xsl:when test="/Response/Error = 'CreditCard Name'">
						Please enter a Credit Card Holder Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard CardNumber'">
						Please enter a Credit Card #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard Invalid'">
						Please enter a valid Credit Card Number.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard ExpMonth'">
						Please enter a Credit Card Expiry Month.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard ExpYear'">
						Please enter a Credit Card Expiry Year.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard Expired'">
						Please enter a valid Credit Card Expiration Date.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard CVV'">
						Please enter a valid Credit Card CVV.
					</xsl:when>
					
					
					<!-- Contact -->
					<xsl:when test="/Response/Error = 'Contact Title'">
						Please enter a Title.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact FirstName'">
						Please enter a First Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact LastName'">
						Please enter a Last Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact DOB'">
						Please enter a valid Date of Birth.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Email'">
						Please enter an Email Address.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Phones Empty'">
						Please enter a Contact Number.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Phone Invalid'">
						Please enter a valid Phone Number.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Mobile Invalid'">
						Please enter a valid Mobile Number.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Fax Invalid'">
						Please enter a valid Fax Number.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact UserName Empty'">
						Please enter a Username.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact UserName Exists'">
						The Username you entered already exists. Please enter a unique Username.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Email Exists'">
						The Email you entered already exists. Please enter a unique Email.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact PassWord'">
						Please enter a Password.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="account_add.php">
			<xsl:choose>
				<xsl:when test="/Response/AccountGroup">
					<div class="MsgNoticeWide">
						<strong><span class="Attention">Attention</span> : </strong>The account's status will default to &quot;Pending Activation&quot;
					</div>
					<div class="Wide-Form">
						<div class="Form-Content">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<th class="Required"></th>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('AccountGroup')" />
											<xsl:with-param name="field" select="string('Id')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/AccountGroup/Id" />
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<input type="hidden" name="Associated">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Associated" />
						</xsl:attribute>
					</input>
				</xsl:when>
				<xsl:otherwise>
					<div class="MsgNoticeWide">
						<strong><span class="Attention">Attention</span> :</strong>
						This form will add a new Customer. If you wish to add an Account to an existing Customer, 
						you will need to use the &quot;Add Associated Account&quot; link from the existing Account.
						<br />
						<strong><span class="Attention">Attention</span> : </strong>The account's status will default to &quot;Pending Activation&quot;
					</div>
				</xsl:otherwise>
			</xsl:choose>
			<div class="Seperator"></div>
			
			
			<!--Account Details -->
			<h2 class="Account">Account Details</h2>
			
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="Required"><strong><span class="Red">*</span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('BusinessName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[BusinessName]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/BusinessName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('TradingName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[TradingName]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/TradingName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td coslpan="3">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red"><sup>1</sup></span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('ABN')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[ABN]" class="input-ABN">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/ABN" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red"><sup>1</sup></span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('ACN')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[ACN]" class="input-ACN">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/ACN" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td coslpan="3">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red">*</span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Address1')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[Address1]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/Address1" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Address2')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[Address2]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/Address2" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red">*</span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Suburb')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Account[Suburb]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/Suburb" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red">*</span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Postcode')" />
							</xsl:call-template>
						</th>
						<td>
						<!--TODO!bash! [  DONE  ]		Only accept 4 digit numbers!! -->
							<input type="text" name="Account[Postcode]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/Postcode" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red">*</span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('State')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="Account[State]">
								<option value=""></option>
								<xsl:for-each select="/Response/ServiceStateTypes/ServiceStateType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="@selected='selected'">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="./Name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr>
						<td coslpan="3">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="Required"><strong><span class="Red">*</span></strong></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CustomerGroup')" />
								<xsl:with-param name="field" select="string('CustomerGroup')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="Account[CustomerGroup]">
								<option value=""></option>
								<xsl:for-each select="/Response/CustomerGroups/CustomerGroup">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="@selected='selected'">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="./Name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<!-- Billing Details -->
			<!--TODO!bash! [  DONE  ]		URGENT! - Billing details are not being saved!!! (this may only be happening if there has been an error on when adding customer - check)-->
			<h2 class="Invoice">Billing Details</h2>
			
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('DisableDDR')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="checkbox" name="Account[DisableDDR]" id="DisableDDR:TRUE" value="1">
								<xsl:if test="/Response/ui-values/Account/DisableDDR = 1">
									<xsl:attribute name="checked">
										<xsl:text>checked</xsl:text>
									</xsl:attribute>
								</xsl:if>
							</input>
							
							<label for="DisableDDR:TRUE">
								Do NOT charge an admin fee (non direct debit fee)
							</label>
						</td>
					</tr>
					<tr>
						<td></td>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('DisableLatePayment')" />
							</xsl:call-template>
						</th>
						<td>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="radio" name="Account[DisableLatePayment]" id="DisableLatePayment:FALSE" value="0">
											<xsl:if test="/Response/ui-values/Account/DisableLatePayment = 0">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="DisableLatePayment:FALSE">
											Charge a late payment fee
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="Account[DisableLatePayment]" id="DisableLatePayment:DONKEY" value="-1">
											<xsl:if test="/Response/ui-values/Account/DisableLatePayment = -1">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="DisableLatePayment:DONKEY">
											Don't charge a late payment fee on the next invoice
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="Account[DisableLatePayment]" id="DisableLatePayment:TRUE" value="1">
											<xsl:if test="/Response/ui-values/Account/DisableLatePayment = 1">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="DisableLatePayment:TRUE">
											Never charge a late payment fee
										</label>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Billing')" />
								<xsl:with-param name="field" select="string('BillingMethod')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="Account[BillingMethod]">
								<xsl:for-each select="/Response/BillingMethods/BillingMethod">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:choose>
											<xsl:when test="@selected='selected'">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:when>
										</xsl:choose>
										<xsl:text></xsl:text>
										<xsl:value-of select="./Name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Payment')" />
								<xsl:with-param name="field" select="string('PaymentMethod')" />
							</xsl:call-template>
						</th>
						<td>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="radio" name="Account[BillingType]" value="3" id="PaymentMethod:AC">
											<xsl:if test="count(/Response/BillingTypes/BillingType[@selected='selected']) = 0 or /Response/BillingTypes/BillingType[@selected='selected']/Id = 3">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<th>
										<label for="PaymentMethod:AC">
											Invoice
										</label>
									</th>
								</tr>
								<!-- TODO!!!! - LOW PRIORITY - Payment terms.-->
								<!-- put it in a seperate file, option for text entyr or select box  -->
								<tr>
									<td>
										<input type="radio" name="Account[BillingType]" value="1" id="PaymentMethod:DDR">
											<xsl:if test="/Response/BillingTypes/BillingType[@selected='selected']/Id = 1">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<th>
										<label for="PaymentMethod:DDR">
											Direct Debit - from Bank Account
										</label>
									</th>
								</tr>
								<tr>
									<td></td>
									<td>
										<table border="0" cellpadding="2" cellspacing="0">
											<tr>
											<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('BankName')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[BankName]" class="input-string" maxlength="255">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/BankName" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<!--TODO!bash! [  DONE  ]		Verify - only accept 6 digit number!!-->
											<tr>
											<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('BSB')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[BSB]" class="input-string" maxlength="6">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/BSB" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<!--TODO!bash! [  DONE  ]		URGENT -Verify -  only accept 9 digit number!!-->
											<tr>
											<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('AccountNumber')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[AccountNumber]" class="input-string" maxlength="9">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/AccountNumber" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
											<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('AccountName')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[AccountName]" class="input-string" maxlength="255">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/AccountName" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<div class="MicroSeperator"></div>
									</td>
								</tr>
								
								<!--Direct Debit -->
								<tr>
									<td>
										<input type="radio" name="Account[BillingType]" value="2" id="PaymentMethod:CC">
											<xsl:if test="/Response/BillingTypes/BillingType[@selected='selected']/Id = 2">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<th>
										<label for="PaymentMethod:CC">
											Direct Debit - from Credit Card
										</label>
									</th>
								</tr>
								<tr>
									<td></td>
									<td>
										<table border="0" cellpadding="2" cellspacing="0">
											<tr>
											<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('CardType')" />
													</xsl:call-template>
												</th>
												<td>
													<select name="CC[CardType]">
														<xsl:for-each select="/Response/CreditCardTypes/CreditCardType">
															<option>
																<xsl:attribute name="value">
																	<xsl:text></xsl:text>
																	<xsl:value-of select="./Id" />
																</xsl:attribute>
																<xsl:if test="@selected='selected'">
																	<xsl:attribute name="selected">
																		<xsl:text>selected</xsl:text>
																	</xsl:attribute>
																</xsl:if>
																<xsl:value-of select="./Name" />
															</option>
														</xsl:for-each>
													</select>
												</td>
											</tr>
											<tr>
											<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('Name')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="CC[Name]" class="input-string" maxlength="255">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/CreditCard/Name" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<!--TODO!bash! [  DONE  ]		URGENT - Verify Credit Card Number (talk to flame if you aren't sure how)-->
											<tr>
												<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('CardNumber')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="CC[CardNumber]" class="input-string" maxlength="20">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/CreditCard/CardNumber" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
												<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('ExpiryDate')" />
													</xsl:call-template>
												</th>
												<td>
													<xsl:call-template name="CreditCardExpiry">
														<xsl:with-param name="Name-Month"		select="string('CC[ExpMonth]')" />
														<xsl:with-param name="Name-Year"		select="string('CC[ExpYear]')" />
														<xsl:with-param name="Selected-Month"	select="/Response/ui-values/CreditCard/ExpMonth" />
														<xsl:with-param name="Selected-Year"	select="/Response/ui-values/CreditCard/ExpYear" />
													</xsl:call-template>
												</td>
											</tr>
											<tr>
												<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('CVV')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="CC[CVV]" class="input-string2">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/CreditCard/CVV" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Contact">Primary Contact Details</h2>
			
			<div class="Wide-Form">
				<xsl:if test="/Response/Contacts">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<td class="Required" valign="top"></td>
							<td>
								<input type="radio" name="Contact[USE]" value="1" id="Contact[USE]:TRUE">
									<xsl:choose>
										<xsl:when test="/Response/ui-values/Contact/USE = '1'">
											<xsl:attribute name="checked">
												<xsl:text>checked</xsl:text>
											</xsl:attribute>
										</xsl:when>
									</xsl:choose>
								</input>
							</td>
							<th>
								<label for="Contact[USE]:TRUE">
								<!--TODO!bash! [  DONE  ]		URGENT this option does not always work (maybe not when there have been errors? - check - maybe because their account access is still 'this account only' on the other account? but it is definately broken-->
								<!--TODO!bash! URGENT - no, this is not done.  fix it.-->
									Select an existing contact from the list below:
								</label>
							</th>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td>
								<select name="Contact[Id]">
									<xsl:for-each select="/Response/Contacts/Contact">
										<xsl:variable name="Contact" select="." />
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/ui-values/Contact/Id = ./Id">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="/Response/TitleTypes/TitleType[./Id=$Contact/Title]/Name" />
											<xsl:text> </xsl:text>
											<xsl:value-of select="./FirstName" />
											<xsl:text> </xsl:text>
											<xsl:value-of select="./LastName" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<td class="Required" valign="top"></td>
							<td>
								<input type="radio" name="Contact[USE]" value="0" id="Contact[USE]:FALSE">
									<xsl:choose>
										<xsl:when test="not(/Response/ui-values/Contact/USE) or /Response/ui-values/Contact/USE = '0'">
											<xsl:attribute name="checked">
												<xsl:text>checked</xsl:text>
											</xsl:attribute>
										</xsl:when>
									</xsl:choose>
								</input>
							</td>
							<th>
								<label for="Contact[USE]:FALSE">
									Create a new Contact using the following details:
								</label>
							</th>
						</tr>
					</table>
				</xsl:if>
				
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
					
					<xsl:choose>
						<xsl:when test="/Response/AccountGroup">
							<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						</xsl:otherwise>
					</xsl:choose>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Title')" />
							</xsl:call-template>
						</th>
						<td>
						<!--TODO!bash! [  DONE  ]		Make this a dropdown box - Do this Everywhere where there is a title!!!-->
							<select name="Contact[Title]">
								<option value=""></option>
								<xsl:for-each select="/Response/TitleTypes/TitleType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ui-values/Contact/Title = ./Id">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
										</xsl:if>
										<xsl:text></xsl:text>
										<xsl:value-of select="./Name" />
									</option>
								</xsl:for-each>
							</select>
						</td>	
					</tr>
					<tr>
						<xsl:choose>
						<xsl:when test="/Response/AccountGroup">
							<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						</xsl:otherwise>
					</xsl:choose>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('FirstName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[FirstName]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/FirstName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<xsl:choose>
						<xsl:when test="/Response/AccountGroup">
							<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						</xsl:otherwise>
					</xsl:choose>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('LastName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[LastName]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/LastName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<xsl:choose>
						<xsl:when test="/Response/AccountGroup">
							<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						</xsl:otherwise>
					</xsl:choose>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('DOB')" />
							</xsl:call-template>
						</th>
						<!-- TODO!bash! URGENT! [  DONE  ]		DOB is not always storing properly! make sure it is fixed properly. -->
						<!-- TODO!bash! URGENT! [  DONE  ]		Only allow realistic dob - i.e., not less than 18 yrs old. -->
						<td>
							<xsl:call-template name="DOB">
								<xsl:with-param name="Name-Day"			select="string('Contact[DOB][day]')" />
								<xsl:with-param name="Name-Month"		select="string('Contact[DOB][month]')" />
								<xsl:with-param name="Name-Year"		select="string('Contact[DOB][year]')" />
								<xsl:with-param name="Selected-Day"		select="/Response/ui-values/Contact/DOB-day" />
								<xsl:with-param name="Selected-Month"	select="/Response/ui-values/Contact/DOB-month" />
								<xsl:with-param name="Selected-Year"	select="/Response/ui-values/Contact/DOB-year" />
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<td></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('JobTitle')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[JobTitle]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/JobTitle" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<xsl:choose>
							<xsl:when test="/Response/AccountGroup">
								<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
							</xsl:when>
							<xsl:otherwise>
								<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
							</xsl:otherwise>
						</xsl:choose>
					
						<th class="JustifiedWidth">
							<!--TODO!bash! [  DONE  ]		URGENT! Verify - only allow strings including an @ symbol-->
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Email')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[Email]" class="input-string" maxlength="255">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/Email" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red"><sup>2</sup></span></strong></td>
						<th class="JustifiedWidth">
						<!--TODO!bash! URGENT - verify - only numeric -->
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Phone')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[Phone]" class="input-string" maxlength="25">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/Phone" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red"><sup>2</sup></span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Mobile')" />
							</xsl:call-template>
						</th>
						<td>
						<!--TODO!bash! URGENT - Verify - Only numeric -->
							<input type="text" name="Contact[Mobile]" class="input-string" maxlength="25">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/Mobile" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="Required"></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Fax')" />
							</xsl:call-template>
						</th>
						<td>
						<!--TODO!bash! URGENT! - Verify - only numeric -->
							<input type="text" name="Contact[Fax]" class="input-string" maxlength="25">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/Fax" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<xsl:choose>
						<xsl:when test="/Response/AccountGroup">
							<td class="Required" valign="top"><strong><span class="Red"><sup>#</sup></span></strong></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						</xsl:otherwise>
					</xsl:choose>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('PassWord')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[PassWord]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/PassWord" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<!-- TODO!!!! - LOW PRIORITY - button to auto generate a password-->
				</table>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
				<strong><span class="Red"><sup>1</sup> </span></strong>: One or both fields required<br/>
				<xsl:choose>
						<xsl:when test="/Response/AccountGroup">
							<strong><span class="Red"><sup>2</sup> </span></strong>: One or both fields required only when the associated option is selected<br/>
						</xsl:when>
						<xsl:otherwise>
							<strong><span class="Red"><sup>2</sup> </span></strong>: One or both fields required<br/>
						</xsl:otherwise>
					</xsl:choose>
				
				<strong><span class="Red"><sup>#</sup> </span></strong>: Required only when the associated option is selected<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Add Account &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
