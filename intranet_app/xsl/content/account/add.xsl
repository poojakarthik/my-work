<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<xsl:choose>
			<xsl:when test="/Response/AccountGroup">
				<h1>Add Associated Account</h1>
			</xsl:when>
			<xsl:otherwise>
				<h1>Add New Customer</h1>
			</xsl:otherwise>
		</xsl:choose>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'BusinessName'">
						The Business Name that you entered was Blank.
					</xsl:when>
					<xsl:when test="/Response/Error = 'TradingName'">
						The Trading Name that you entered was Blank.
					</xsl:when>
					<xsl:when test="/Response/Error = 'UserName-Blank'">
						The Username you entered was Blank.
					</xsl:when>
					<xsl:when test="/Response/Error = 'UserName'">
						The Username you entered already exists in the System.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Email-Blank'">
						The Email Address you entered was blank.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CustomerGroup'">
						The Customer Group you selected was Invalid. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'BillingMethod'">
						The Billing Method you selected was Invalid. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CardType'">
						The Credit Card Type you selected was Invalid. Please try again.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="account_add.php">
			<xsl:choose>
				<xsl:when test="/Response/AccountGroup">
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
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
					<div class="MsgNotice" style="width: 450px">
						<strong><span class="Attention">Attention</span> :</strong>
						This form will add a new Customer.  If you wish to add an 
						account to an existing Customer you will need to use the
						&quot;Add Associated Account&quot; link from the customers
						existing account.						
					</div>
				</xsl:otherwise>
			</xsl:choose>
			<div class="Seperator"></div>
			
			<h2 class="Account">General Account Details</h2>
			
			<div class="Filter-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
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
						<td coslpan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
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
						<td coslpan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
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
						<td coslpan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
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
			
			<div class="Filter-Form">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
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
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Payment')" />
								<xsl:with-param name="field" select="string('PaymentMethod')" />
							</xsl:call-template>
						</th>
						<td>
							<table border="0" cellpadding="5" cellspacing="0">
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
										<div class="Seperator"></div>
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
													<select name="CC[ExpMonth]">
														<xsl:call-template name="Date_Loop">
															<xsl:with-param name="start" select="1" />
															<xsl:with-param name="cease" select="12" />
															<xsl:with-param name="select" select="/Response/ui-values/CreditCard/ExpMonth" />
														</xsl:call-template>
													</select> /
													<select name="CC[ExpYear]">
														<xsl:call-template name="Date_Loop">
															<xsl:with-param name="start" select="7" />
															<xsl:with-param name="cease" select="17" />
															<xsl:with-param name="select" select="/Response/ui-values/CreditCard/ExpYear" />
														</xsl:call-template>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Contact">Primary Contact Details</h2>
			
			<div class="Filter-Form">
				<xsl:if test="/Response/Contacts">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
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
				
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
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
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Contact')" />
								<xsl:with-param name="field" select="string('DOB')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="Contact[DOB][year]">
								<option value="">YYYY</option>
								<xsl:call-template name="Date_Loop">
									<xsl:with-param name="start" select="1900" />
									<xsl:with-param name="cease" select="1990" />
									<xsl:with-param name="select" select="/Response/ui-values/Contact/DOB-year" />
								</xsl:call-template>
							</select>
							-
							<select name="Contact[DOB][month]">
								<option value="">MM</option>
								<xsl:call-template name="Date_Loop">
									<xsl:with-param name="start" select="1" />
									<xsl:with-param name="cease" select="12" />
									<xsl:with-param name="select" select="/Response/ui-values/Contact/DOB-month" />
								</xsl:call-template>
							</select>
							-
							<select name="Contact[DOB][day]">
								<option value="">DD</option>
								<xsl:call-template name="Date_Loop">
									<xsl:with-param name="start" select="1" />
									<xsl:with-param name="cease" select="31" />
									<xsl:with-param name="select" select="/Response/ui-values/Contact/DOB-day" />
								</xsl:call-template>
							</select>
						</td>
					</tr>
					<tr>
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
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
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
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<!-- TODO!!!! - LOW PRIORITY - auto generate username-->
					<tr>
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
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Account &#0187;" class="input-submit" />
		</form>
	</xsl:template>
	
	<xsl:template name="Date_Loop">
		<xsl:param name="start">1</xsl:param>
		<xsl:param name="cease">0</xsl:param>
		<xsl:param name="steps">1</xsl:param>
		<xsl:param name="count">0</xsl:param>
		
		<xsl:param name="select">0</xsl:param>
		
		<xsl:if test="number($start) + number($count) &lt;= number($cease)">
			<option>
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="$start + $count" />
				</xsl:attribute>
				
				<xsl:choose>
					<xsl:when test="$select = $start + $count">
						<xsl:attribute name="selected">
							<xsl:text>selected</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:value-of select="$start + $count" />
			</option>
			<xsl:call-template name="Date_Loop">
				<xsl:with-param name="start" select="$start" />
				<xsl:with-param name="cease" select="$cease" />
				<xsl:with-param name="steps" select="$steps" />
				<xsl:with-param name="count" select="$count + $steps" />
				<xsl:with-param name="select" select="$select" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
