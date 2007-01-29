<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--Page for adding a new customer or associated account -->
		
		<xsl:choose>
			<xsl:when test="/Response/AccountGroup">
				<h1>Add Associated Account</h1>
			</xsl:when>
			<xsl:otherwise>
				<h1>Add Customer</h1>
			</xsl:otherwise>
		</xsl:choose>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<!-- Serious Errors : Should never show, but just in case -->
					<xsl:when test="/Response/Error = 'Account CustomerGroup'">
						The Customer Group you selected was Invalid. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Billing Method'">
						The Billing Method you selected was Invalid. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard CardType'">
						The Credit Card Type you selected was Invalid. Please try again.
					</xsl:when>
					
					<!-- Account -->
					<xsl:when test="/Response/Error = 'Account BusinessName'">
						You did not enter a Business Name. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account ABN-ACN'">
						You did not enter either an ABN# or ACN#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account ABN Invalid'">
						You did not enter a valid ABN#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account ACN Invalid'">
						You did not enter a valid ACN#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account Address'">
						You did not enter an Address. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account Suburb'">
						You did not enter a Suburb. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account Postcode'">
						You did not enter a Postcode. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Account State'">
						You did not enter a State. Please try again.
					</xsl:when>
					
					<!-- Direct Debit -->
					<xsl:when test="/Response/Error = 'DirectDebit BankName'">
						You did not enter a Direct Debit Bank Name. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit BSB'">
						You did not enter a Direct Debit BSB#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit AccountNumber'">
						You did not enter a Direct Debit Account#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'DirectDebit AccountName'">
						You did not enter a Direct Debit Account Name. Please try again.
					</xsl:when>
					
					<!-- Credit Card -->
					<xsl:when test="/Response/Error = 'CreditCard Name'">
						You did not enter a Credit Card Holder Name. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard CardNumber'">
						You did not enter a Credit Card Number. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard ExpMonth'">
						You did not enter a Credit Card Expiration Month. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CreditCard ExpYear'">
						You did not enter a Credit Card Expiration Year. Please try again.
					</xsl:when>
					
					
					<!-- Contact -->
					<xsl:when test="/Response/Error = 'Contact Title'">
						You must enter a Salutation for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact FirstName'">
						You must enter a First Name for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact LastName'">
						You must enter a Last Name for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact DOB'">
						You must enter a valid Date of Birth for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Email'">
						You must enter an Email Address for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact Phones Empty'">
						You must enter a Contact Number for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact UserName Empty'">
						You must enter a Username for the Contact you are creating.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact UserName Exists'">
						The Username you selected already exists. Please select another Username and try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Contact PassWord'">
						You must enter a Password for the Contact you are creating.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="account_add.php">
			<xsl:choose>
				<xsl:when test="/Response/AccountGroup">
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
					
					<input type="hidden" name="AccountGroup">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/AccountGroup/Id" />
						</xsl:attribute>
					</input>
				</xsl:when>
				<xsl:otherwise>
					<div class="MsgNoticeWide">
						<strong><span class="Attention">Attention</span> :</strong>
						This form will add a new Customer.  If you wish to add an 
						account to an existing Customer you will need to use the
						&quot;Add Associated Account&quot; link from the customers
						existing account.						
					</div>
				</xsl:otherwise>
			</xsl:choose>
			<div class="Seperator"></div>
			
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
							<input type="text" name="Account[BusinessName]" class="input-string">
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
							<input type="text" name="Account[TradingName]" class="input-string">
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
							<input type="text" name="Account[Address1]" class="input-string">
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
							<input type="text" name="Account[Address2]" class="input-string">
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
							<input type="text" name="Account[Suburb]" class="input-string">
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
							<input type="text" name="Account[State]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Account/State" />
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
								<xsl:with-param name="entity" select="string('CustomerGroup')" />
								<xsl:with-param name="field" select="string('CustomerGroup')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="Account[CustomerGroup]">
								<xsl:for-each select="/Response/CustomerGroups/CustomerGroup">
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
										<xsl:value-of select="./Name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Invoice">Billing Details</h2>
			
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
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
											Account
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
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('BankName')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[BankName]" class="input-string">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/BankName" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('BSB')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[BSB]" class="input-string">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/BSB" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('AccountNumber')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[AccountNumber]" class="input-string">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/DirectDebit/AccountNumber" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Direct Debit')" />
														<xsl:with-param name="field" select="string('AccountName')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="DDR[AccountName]" class="input-string">
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
																<xsl:choose>
																	<xsl:when test="@selected='selected'">
																		<xsl:attribute name="selected">
																			<xsl:text>selected</xsl:text>
																		</xsl:attribute>
																	</xsl:when>
																</xsl:choose>
																<xsl:value-of select="./Name" />
															</option>
														</xsl:for-each>
													</select>
												</td>
											</tr>
											<tr>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('Name')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="CC[Name]" class="input-string">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/CreditCard/Name" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Credit Card')" />
														<xsl:with-param name="field" select="string('CardNumber')" />
													</xsl:call-template>
												</th>
												<td>
													<input type="text" name="CC[CardNumber]" class="input-string">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/ui-values/CreditCard/CardNumber" />
														</xsl:attribute>
													</input>
												</td>
											</tr>
											<tr>
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
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
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
											<xsl:value-of select="./Title" />
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
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
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
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Title')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[Title]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/Title" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('FirstName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[FirstName]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/FirstName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('LastName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[LastName]" class="input-string">
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
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('DOB')" />
							</xsl:call-template>
						</th>
						<!-- TODO!bash! [  DONE  ]		Make DOB DD-MM-YYYY ... do this any place you have a Date -->
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
							<input type="text" name="Contact[JobTitle]" class="input-string">
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
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Email')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[Email]" class="input-string">
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
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('Phone')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[Phone]" class="input-string">
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
							<input type="text" name="Contact[Mobile]" class="input-string">
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
							<input type="text" name="Contact[Fax]" class="input-string">
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
					<!-- TODO!!!! - LOW PRIORITY - auto generate username-->
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('UserName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Contact[UserName]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/Contact/UserName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
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
				<strong><span class="Red"><sup>2</sup> </span></strong>: One or both fields required<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Add Account &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
