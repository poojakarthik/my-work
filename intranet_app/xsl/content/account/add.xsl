<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Create Account</h1>
		<div class="Seperator"></div>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>

		<form method="POST" action="account_add.php">
			<div class="Seperator"></div>
			
			<xsl:choose>
				<xsl:when test="/Response/AccountGroup">
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
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
					<div class="MsgNotice">
						<strong><span class="Attention">Attention</span> :</strong>
						You are currently requesting to add an Account for a Customer who we have
						no pre-existing relationships with. If a pre-existing relationship with the 
						customer exists, please create a new Account from the Console of an 
						account which has been setup previously.
					</div>
				</xsl:otherwise>
			</xsl:choose>
			<div class="Seperator"></div>
			
			<h2 class="Account">General Account Information</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="BusinessName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/BusinessName" disable-output-escaping="yes" />
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
								<input type="text" name="TradingName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/TradingName" disable-output-escaping="yes" />
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
								<input type="text" name="ABN" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/ABN" disable-output-escaping="yes" />
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
								<input type="text" name="ACN" class="input-ACN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/ACN" disable-output-escaping="yes" />
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
								<input type="text" name="Address1" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Address1" disable-output-escaping="yes" />
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
								<input type="text" name="Address2" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Address2" disable-output-escaping="yes" />
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
								<input type="text" name="Suburb" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Suburb" disable-output-escaping="yes" />
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
								<input type="text" name="Postcode" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Postcode" disable-output-escaping="yes" />
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
								<input type="text" name="State" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/State" disable-output-escaping="yes" />
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
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BillingMethod')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="BillingMethod">
									<xsl:for-each select="/Response/BillingMethods/BillingMethod">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
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
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('PaymentMethod')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="5" cellspacing="0">
									<tr>
										<td><input type="radio" name="PaymentMethod" value="2" id="PaymentMethod:DDR" /></td>
										<th>
											<label for="PaymentMethod:DDR">
												Pay this account via Direct Debit
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
															<xsl:with-param name="entity" select="string('DirectDebit')" />
															<xsl:with-param name="field" select="string('BankName')" />
														</xsl:call-template>
													</th>
													<td>
														<input type="text" name="DDR[BankName]" />
													</td>
												</tr>
												<tr>
													<th class="JustifiedWidth">
														<xsl:call-template name="Label">
															<xsl:with-param name="entity" select="string('DirectDebit')" />
															<xsl:with-param name="field" select="string('BSB')" />
														</xsl:call-template>
													</th>
													<td>
														<input type="text" name="DDR[BSB]" />
													</td>
												</tr>
												<tr>
													<th class="JustifiedWidth">
														<xsl:call-template name="Label">
															<xsl:with-param name="entity" select="string('DirectDebit')" />
															<xsl:with-param name="field" select="string('AccountNumber')" />
														</xsl:call-template>
													</th>
													<td>
														<input type="text" name="DDR[AccountNumber]" />
													</td>
												</tr>
												<tr>
													<th class="JustifiedWidth">
														<xsl:call-template name="Label">
															<xsl:with-param name="entity" select="string('DirectDebit')" />
															<xsl:with-param name="field" select="string('AccountName')" />
														</xsl:call-template>
													</th>
													<td>
														<input type="text" name="DDR[AccountName]" />
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
										<td><input type="radio" name="PaymentMethod" value="2" id="PaymentMethod:CC" /></td>
										<th>
											<label for="PaymentMethod:CC">
												Pay this account via Credit Card
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
															<xsl:with-param name="entity" select="string('CreditCard')" />
															<xsl:with-param name="field" select="string('CardType')" />
														</xsl:call-template>
													</th>
													<td>
														<select name="CC[CardType]">
														
														</select>
													</td>
												</tr>
												<tr>
													<th class="JustifiedWidth">
														<xsl:call-template name="Label">
															<xsl:with-param name="entity" select="string('CreditCard')" />
															<xsl:with-param name="field" select="string('Name')" />
														</xsl:call-template>
													</th>
													<td>
														<input type="text" name="CC[Name]" />
													</td>
												</tr>
												<tr>
													<th class="JustifiedWidth">
														<xsl:call-template name="Label">
															<xsl:with-param name="entity" select="string('CreditCard')" />
															<xsl:with-param name="field" select="string('CardNumber')" />
														</xsl:call-template>
													</th>
													<td>
														<input type="text" name="CC[CardNumber]" />
													</td>
												</tr>
												<tr>
													<th class="JustifiedWidth">
														<xsl:call-template name="Label">
															<xsl:with-param name="entity" select="string('CreditCard')" />
															<xsl:with-param name="field" select="string('ExpirationDate')" />
														</xsl:call-template>
													</th>
													<td>
														<select name="ExpMonth"></select> /
														<select name="ExpYear"></select>
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
						<tr>
							<td></td>
							<td>
								<input type="submit" value="Create Account &#0187;" class="input-submit" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
		</form>
	</xsl:template>
</xsl:stylesheet>
