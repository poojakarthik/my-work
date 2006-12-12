<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<h2>Stage 3: Verification</h2>
		<div class="Seperator"></div>
		
		<form method="POST" action="contact_list.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="FNN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/FNN" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="Contact">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Contact/Id" />
				</xsl:attribute>
			</input>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					Now that an Account/Service and a Contact has been selected,
					you must verify at least three of the following pieces of Information:
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						
						<tr>
							<td></td>
							<th colspan="2">
								Account Information
							</th>
						</tr>
						<!-- Account Id -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Account-Id]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Values[Account-Id]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						
						<!-- Account ABN -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Account-ABN]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Values[Account-ABN]" class="input-ABN" />
							</td>
						</tr>
						
						<!-- Account ACN -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Account-ACN]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Values[Account-ACN]" class="input-ACN" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								<div style="width: 500px">
									<span class="Attention">Attention!</span>
									You must match both the Business Name and the Trading Name
									of an Account if this is how the person wishes to verify themselves.
								</div>
							</td>
						</tr>
						<!-- Account Business Name -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Account-BusinessName]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/BusinessName" />
							</td>
						</tr>
						<!-- Account Trading Name -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Account-TradingName]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/TradingName" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						<!-- Account Address -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Account-Address]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address1')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Address1" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address2')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Address2" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Suburb')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Suburb" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Postcode')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Postcode" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<tr>
							<th colspan="3">
								Contact Information
							</th>
						</tr>
						<!-- Contact DOB -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Contact-DOB]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('DOB')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Values[Contact-DOB][Day]">
									<option value="">DD</option>
									<xsl:call-template name="Date-Loop">
										<xsl:with-param name="start" select="1" />
										<xsl:with-param name="cease" select="31" />
										<xsl:with-param name="steps" select="1" />
									</xsl:call-template>
								</select> / 
								<select name="Values[Contact-DOB][Month]">
									<option value="">MM</option>
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
								<select name="Values[Contact-DOB][Year]">
									<option value="">YYYY</option>
									<xsl:call-template name="Date-Loop">
										<xsl:with-param name="start" select="1910" />
										<xsl:with-param name="cease" select="1990" />
										<xsl:with-param name="steps" select="1" />
									</xsl:call-template>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<tr>
							<th colspan="3">
								Previous Invoice Information
							</th>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								The information depicted on the previous invoice for the account being requested.
							</td>
						</tr>
						<!-- Invoice Billed Amount -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[Invoice-Amount]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Invoice')" />
									<xsl:with-param name="field" select="string('Amount')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Values[Invoice-Amount]" class="input-string" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<tr>
							<th colspan="3">
								Billing Information
							</th>
						</tr>
						<tr>
							<td></td>
							<th colspan="2">
								Credit Card Billing
							</th>
						</tr>
						<!-- Credit Card Number -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[CreditCard-CardNumber]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Credit Card')" />
									<xsl:with-param name="field" select="string('CardNumber')" />
								</xsl:call-template>
							</th>
							<td>
								XXXX - XXXX - XXXX -
								<input type="text" name="Values[CreditCard-CardNumber]" class="input-string" size="4" />
							</td>
						</tr>
						<!-- Credit Card Expiration Date -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[CreditCard-Expiration]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Credit Card')" />
									<xsl:with-param name="field" select="string('ExpiryDate')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Values[CreditCard-Expiration][Month]">
									<option value=""></option>
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
								<select name="Values[CreditCard-Expiration][Year]">
									<option value=""></option>
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
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<th colspan="2">
								Direct Debit Billing
							</th>
						</tr>
						<!-- Direct Debit BSB -->
						<tr>
							<td>
								<input type="checkbox" name="Fields[DirectDebit-BSB]" value="1" />
							</td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Direct Debit')" />
									<xsl:with-param name="field" select="string('BSB')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Values[DirectDebit-BSB][1]" class="input-string" size="3" /> -
								<input type="text" name="Values[DirectDebit-BSB][2]" class="input-string" size="3" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<tr>
							<td colspan="2"></td>
							<td>
								<input type="submit" class="input-submit" value="Continue &#0187;" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</xsl:template>
	
	<xsl:template name="Date-Loop">
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
			<xsl:call-template name="Date-Loop">
				<xsl:with-param name="start" select="$start" />
				<xsl:with-param name="cease" select="$cease" />
				<xsl:with-param name="steps" select="$steps" />
				<xsl:with-param name="count" select="$count + $steps" />
				<xsl:with-param name="select" select="$select" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
