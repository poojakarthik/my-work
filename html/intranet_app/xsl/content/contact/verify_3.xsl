<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Find Customer</h1>
		
		<script language="javascript" src="js/validate_customer.js"></script>
		
		<h2 class="Contact">Customer Verification</h2>
		
		<!-- TODO!bash! Entering details, and then using the Back/Forward buttons wipes the details from input boxes, but they remain green : fix this -->
		
		<div class="MsgNoticeWide">
			Customers must provide verification details before being allowed access to an Account.
			Input boxes will turn green when correct details have been entered.
			The continue button will turn green once sufficient verification details have been entered.
		</div>
			
		<form method="POST" action="contact_verify.php">
			<input type="hidden" name="ui-Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Account" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-Account-Sel">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Account-Sel" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-Contact-First">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Contact-First" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-Contact-Last">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Contact-Last" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-Contact-Sel">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Contact-Sel" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-BusinessName">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/BusinessName" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-ABN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/ABN" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-ACN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/ACN" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-Invoice">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Invoice" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-FNN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/FNN" />
				</xsl:attribute>
			</input>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<!-- Service Number (FNN) - if entered -->
						<xsl:if test="/Response/ui-values/FNN != ''">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('FNN')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:value-of select="/Response/ui-values/FNN" />
									<input type="hidden" name="Service" id="Service" ValidLevel="2">
										<xsl:if test="/Response/ui-values/FNN != ''">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ui-values/FNN" />
											</xsl:attribute>
											<xsl:attribute name="ValidValue">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ui-values/FNN" />
											</xsl:attribute>
										</xsl:if>
									</input>
								</td>
							</tr>
						</xsl:if>
						<!-- Account Business Name -->
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/ui-answers/Account/BusinessName = ''">
										<span class="Red"> </span>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="/Response/ui-answers/Account/BusinessName" />
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
						<xsl:choose>
							<xsl:when test="/Response/ui-answers/Account/TradingName = ''">
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<td></td>
									<td>
										<xsl:value-of select="/Response/ui-answers/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
						<tr>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<!-- Account Address -->
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td width="30"><input type="checkbox" name="Address" id="Address:TRUE" autocomplete="off"
										onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="2" /></td>
										<th>
											<label for="Address:TRUE">
												Address verified by customer
											</label>
										</th>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<xsl:value-of select="/Response/ui-answers/Account/Address1" /><br/>
								<xsl:value-of select="/Response/ui-answers/Account/Address2" /><br/>
								<xsl:value-of select="/Response/ui-answers/Account/Suburb" />, 
								<xsl:value-of select="/Response/ui-answers/Account/Postcode" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="SmallSeperator"></div>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<!-- Account Id -->
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Account" id="Account" class="input-string" autocomplete="off"
								onkeyup="ValidateCustomer.ValidateInput (this)"
								onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="2">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Account" />
									</xsl:attribute>
									<xsl:attribute name="ValidValue">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-answers/Account/Id" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<!-- Account ABN -->
						<xsl:if test="/Response/ui-answers/Account/ABN != ''">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('ABN')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ABN" id="ABN" class="input-string" autocomplete="off"
									onkeyup="ValidateCustomer.ValidateInput (this)" 
									onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-values/ABN" />
										</xsl:attribute>
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Account/ABN" />
										</xsl:attribute>
									</input>
								</td>
							</tr>
						</xsl:if>
						
						<!-- Account ACN -->
						<xsl:if test="/Response/ui-answers/Account/ACN != ''">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('ACN')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ACN" id="ACN" class="input-string" autocomplete="off"
									onkeyup="ValidateCustomer.ValidateInput (this)" 
									onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-values/ACN" />
										</xsl:attribute>
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Account/ACN" />
										</xsl:attribute>
									</input>
								</td>
							</tr>
						</xsl:if>
					</table>
				</div>
			</div>
			
			<div class="SmallSeperator"></div>

			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<!-- Contact Name -->
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/ui-answers/Contact/FirstName" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="/Response/ui-answers/Contact/LastName" />
							</td>
						</tr>
						<!-- Contact Date of Birth -->
						<xsl:if test="/Response/ui-answers/Contact/DOB/year">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Contact')" />
										<xsl:with-param name="field" select="string('DOB')" />
									</xsl:call-template>
								</th>
								<td>

									<select name="DOB-day" id="DOB-day" ValidLevel="1" autocomplete="off" 
									onchange="ValidateCustomer.ValidateInput (this)"
									onkeyup="ValidateCustomer.ValidateInput (this)"
									onclick="ValidateCustomer.ValidateInput (this)">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Contact/DOB/day" />
										</xsl:attribute>
										<option value=""></option>
										<xsl:call-template name="DateLoop">
											<xsl:with-param name="start" select="number('1')" />
											<xsl:with-param name="cease" select="number('31')" />
											<xsl:with-param name="step" select="number('1')" />
										</xsl:call-template>
									</select> /
									<select name="DOB-month" id="DOB-month" ValidLevel="1" autocomplete="off" 
									onchange="ValidateCustomer.ValidateInput (this)"
									onkeyup="ValidateCustomer.ValidateInput (this)"
									onclick="ValidateCustomer.ValidateInput (this)">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Contact/DOB/month" />
										</xsl:attribute>
										<option value=""></option>
										<option value="01">01 - JAN</option>
										<option value="02">02 - FEB</option>
										<option value="03">03 - MAR</option>
										<option value="04">04 - APR</option>
										<option value="05">05 - MAY</option>
										<option value="06">06 - JUN</option>
										<option value="07">07 - JUL</option>
										<option value="08">08 - AUG</option>
										<option value="09">09 - SEP</option>
										<option value="10">10 - OCT</option>
										<option value="11">11 - NOV</option>
										<option value="12">12 - DEC</option>
									</select> /
									<select name="DOB-year" id="DOB-year" ValidLevel="1" autocomplete="off" 
									onchange="ValidateCustomer.ValidateInput (this)"
									onkeyup="ValidateCustomer.ValidateInput (this)"
									onclick="ValidateCustomer.ValidateInput (this)">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Contact/DOB/year" />
										</xsl:attribute>
										<option value=""></option>
										<xsl:call-template name="DateLoop">
											<xsl:with-param name="start" select="number('1900')" />
											<xsl:with-param name="cease" select="number('1990')" />
											<xsl:with-param name="step" select="number('1')" />
										</xsl:call-template>
									</select>
								</td>
							</tr>
						</xsl:if>
						<!-- TODO!bash! make email address validation case insensitve!! -->
						<!-- Contact Email Address -->
						<xsl:if test="/Response/ui-answers/Contact/Email != ''">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Contact')" />
										<xsl:with-param name="field" select="string('Email')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Email" id="Email" class="input-string" autocomplete="off"
									onkeyup="ValidateCustomer.ValidateInput (this)" 
									onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Contact/Email" />
										</xsl:attribute>
									</input>
								</td>
							</tr>
						</xsl:if>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			
			<xsl:if test="/Response/ui-answers/Invoice or count(/Response/ui-answers/Invoices/Results/rangeSample/Invoice) != 0 or /Response/ui-answers/Account/DirectDebitDetails/DirectDebit">
			
				<div class="Wide-Form">
					<div class="Form-Content">
						<table border="0" cellpadding="3" cellspacing="0">
							<xsl:choose>
								<xsl:when test="/Response/ui-answers/Invoice">
									<!-- Selected Invoice# -->
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Invoice')" />
												<xsl:with-param name="field" select="string('Id')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/ui-answers/Invoice/Id" />
											<input type="hidden" name="Invoice-Id" id="Invoice-Id" class="input-string" autocomplete="off" ValidLevel="1">
												<xsl:attribute name="ValidValue">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/ui-answers/Invoice/Id" />
												</xsl:attribute>
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/ui-answers/Invoice/Id" />
												</xsl:attribute>
											</input>
										</td>
									</tr>
									<!-- Selected Invoice Amount -->
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Invoice')" />
												<xsl:with-param name="field" select="string('Amount')" />
											</xsl:call-template>
										</th>
										<td>
											<input type="text" name="Invoice-Amount" id="Invoice-Amount" class="input-string" autocomplete="off" 
											onkeyup="ValidateCustomer.ValidateInput (this)" 
											onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
												<xsl:attribute name="ValidValue">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/ui-answers/Invoice/Balance" />
												</xsl:attribute>
											</input>
										</td>
									</tr>
								</xsl:when>
								<xsl:when test="count(/Response/ui-answers/Invoices/Results/rangeSample/Invoice) != 0">
									<!-- Most Recent Invoice# -->
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Invoice')" />
												<xsl:with-param name="field" select="string('RecentId')" />
											</xsl:call-template>
										</th>
										<td>
											<input type="text" name="Invoice-Recent-Id" id="Invoice-Recent-Id" class="input-string" autocomplete="off" 
											onkeyup="ValidateCustomer.ValidateInput (this)" 
											onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
												<xsl:attribute name="ValidValue">
													<xsl:text>:</xsl:text>
													<xsl:for-each select="/Response/ui-answers/Invoices/Results/rangeSample/Invoice">
														<xsl:value-of select="./Id" />
														<xsl:text>:</xsl:text>
													</xsl:for-each>
												</xsl:attribute>
											</input>
										</td>
									</tr>
									<!-- Most Recent Invoice Amount -->
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Invoice')" />
												<xsl:with-param name="field" select="string('RecentAmount')" />
											</xsl:call-template>
										</th>
										<td>
											<input type="text" name="Invoice-Amount" id="Invoice-Recent-Amount" class="input-string" autocomplete="off" 
											onkeyup="ValidateCustomer.ValidateInput (this)" 
											onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
												<xsl:attribute name="ValidValue">
													<xsl:text>:</xsl:text>
													<xsl:for-each select="/Response/ui-answers/Invoices/Results/rangeSample/Invoice">
														<xsl:value-of select="./Balance" />
														<xsl:text>:</xsl:text>
													</xsl:for-each>
												</xsl:attribute>
											</input>
										</td>
									</tr>
								</xsl:when>
							</xsl:choose>
							
							<!-- Direct Debit BSB -->
							<xsl:if test="/Response/ui-answers/Account/DirectDebitDetails/DirectDebit">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Direct Debit')" />
											<xsl:with-param name="field" select="string('BSB')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="DirectDebit-BSB" id="DirectDebit-BSB" class="input-string" size="8" autocomplete="off" 
										onkeyup="ValidateCustomer.ValidateInput (this)" 
										onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
											<xsl:attribute name="ValidValue">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ui-answers/Account/DirectDebitDetails/DirectDebit/BSB" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
							</xsl:if>
						</table>
					</div>
				</div>
				
				<div class="SmallSeperator"></div>
			</xsl:if>
			
			<!-- Credit Card Details -->
			<xsl:if test="/Response/ui-answers/Account/CreditCardDetails/CreditCard">
				<div class="Wide-Form">
					<div class="Form-Content">
						<table border="0" cellpadding="3" cellspacing="0">
							<!-- Credit Card Number -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Credit Card')" />
										<xsl:with-param name="field" select="string('CardNumber')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="CreditCard-CardNumber" id="CreditCard-CardNumber" class="input-string" size="4" 
									autocomplete="off" onkeyup="ValidateCustomer.ValidateInput (this)" 
									onclick="ValidateCustomer.ValidateInput (this)" ValidLevel="1">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Account/CreditCardDetails/CreditCard/Last4Digits" />
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
									<select name="CreditCard-Exp-Month" id="CreditCard-Exp-Month" ValidLevel="1" autocomplete="off" 
									onchange="ValidateCustomer.ValidateInput (this)"
									onkeyup="ValidateCustomer.ValidateInput (this)"
									onclick="ValidateCustomer.ValidateInput (this)">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Account/CreditCardDetails/CreditCard/ExpMonth" />
										</xsl:attribute>
										<option value="" selected="selected"></option>
										<option value="01">01</option>
										<option value="02">02</option>
										<option value="03">03</option>
										<option value="04">04</option>
										<option value="05">05</option>
										<option value="06">06</option>
										<option value="07">07</option>
										<option value="08">08</option>
										<option value="09">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
									</select> /
									<select name="CreditCard-Exp-Year" id="CreditCard-Exp-Year" ValidLevel="1" 
									onchange="ValidateCustomer.ValidateInput (this)"
									onkeyup="ValidateCustomer.ValidateInput (this)"
									onclick="ValidateCustomer.ValidateInput (this)">
										<xsl:attribute name="ValidValue">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-answers/Account/CreditCardDetails/CreditCard/ExpYear" />
										</xsl:attribute>
										<option value="" selected="selected"></option>
										<option value="07">07</option>
										<option value="08">08</option>
										<option value="09">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<div class="SmallSeperator"></div>
			</xsl:if>
			
			<div class="Right">
				<input type="submit" name="Confirm" class="input-submit-locked" value="Continue &#0187;" id="Confirm" disabled="disabled" />
			</div>
			
			<div class="Clear"></div>
			<div class="Seperator"></div>
			
			<div class="Right">
				<!--TODO!flame! Temporary -->
				<input type="submit" name="Confirm" class="input-submit-unlocked" value="Skip Verification &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
