<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<h2>Stage 3: Overall Verification</h2>
		<div class="Seperator"></div>
		
		<form method="POST" action="contact_list.php">
			<input type="hidden" name="ui-Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Account/Id" />
				</xsl:attribute>
			</input>
			
			<div class="Filter-Form-Content Left">
				<xsl:if test="/Response/ui-answers/Contact">
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0">
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
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('DOB')" />
										</xsl:call-template>
									</th>
									<td>
										<select name="DOB-year">
											<option value=""></option>
											<xsl:call-template name="Date-Loop">
												<xsl:with-param name="start" select="number('1900')" />
												<xsl:with-param name="cease" select="number('1990')" />
												<xsl:with-param name="step" select="number('1')" />
											</xsl:call-template>
										</select>
										<select name="DOB-month">
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
										</select>
										<select name="DOB-day">
											<option value=""></option>
											<xsl:call-template name="Date-Loop">
												<xsl:with-param name="start" select="number('1')" />
												<xsl:with-param name="cease" select="number('31')" />
												<xsl:with-param name="step" select="number('1')" />
											</xsl:call-template>
										</select>
									</td>
								</tr>
								<!-- Contact Email Address -->
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Email')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="Email" id="Email" class="input-string" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				
					<div class="Seperator"></div>
				</xsl:if>

				<div class="Filter-Form">
					<div class="Filter-Form-Content">
						<table border="0" cellpadding="5" cellspacing="0">
							<!-- Service Number (FNN) - if entered -->
							<xsl:if test="/Response/ui-values/FNN != ''">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('FNN')" />
										</xsl:call-template>
									</th>
									<td><xsl:value-of select="/Response/ui-values/FNN" /></td>
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
									<table border="0" cellpadding="5" cellspacing="0">
										<tr>
											<td><input type="checkbox" name="Values[BusinessName]" id="BusinessName:TRUE" /></td>
											<th>
												<label for="BusinessName:TRUE">
													Yes: verify this information
												</label>
											</th>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<xsl:choose>
										<xsl:when test="/Response/ui-answers/Account/BusinessName = ''">
											<span class="Red">No Business Name Defined</span>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="/Response/ui-answers/Account/BusinessName" />
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<xsl:choose>
										<xsl:when test="/Response/ui-answers/Account/TradingName = ''">
											<span class="Red">No Trading Name Defined</span>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="/Response/ui-answers/Account/TradingName" />
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="Seperator"></div>
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
									<table border="0" cellpadding="5" cellspacing="0">
										<tr>
											<td><input type="checkbox" name="Values[Address]" id="Address:TRUE" /></td>
											<th>
												<label for="Address:TRUE">
													Yes: verify this information
												</label>
											</th>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td></td>
								<td><xsl:value-of select="/Response/ui-answers/Account/Address1" /></td>
							</tr>
							<tr>
								<td></td>
								<td><xsl:value-of select="/Response/ui-answers/Account/Address2" /></td>
							</tr>
							<tr>
								<td></td>
								<td>
									<xsl:value-of select="/Response/ui-answers/Account/Suburb" />, 
									<xsl:value-of select="/Response/ui-answers/Account/Postcode" />
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<div class="Seperator"></div>
			</div>
			
			<div class="Filter-Form-Content Left">
				<div class="Filter-Form">
					<div class="Filter-Form-Content">
						<table border="0" cellpadding="5" cellspacing="0">
							<!-- Account Id -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Id')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Values[Account-Id]" class="input-string">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-values/Account" />
										</xsl:attribute>
									</input>
								</td>
							</tr>
							<!-- Account ABN -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('ABN')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Values[Account-ABN]" class="input-ABN">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-values/ABN" />
										</xsl:attribute>
									</input>
								</td>
							</tr>
							
							<!-- Account ACN -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('ACN')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Values[Account-ACN]" class="input-ACN">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-values/ACN" />
										</xsl:attribute>
									</input>
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<div class="Seperator"></div>
				
				<div class="Filter-Form">
					<div class="Filter-Form-Content">
						<table border="0" cellpadding="5" cellspacing="0">
							<!-- Most Recent Invoice# -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Invoice')" />
										<xsl:with-param name="field" select="string('Id')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Values[Invoice-Id]" class="input-string" />
								</td>
							</tr>
							<!-- Most Recent Invoice# -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Invoice')" />
										<xsl:with-param name="field" select="string('Amount')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Values[Invoice-Amount]" class="input-string" />
								</td>
							</tr>
							<!-- Direct Debit BSB -->
							<tr>
								<th class="JustifiedWidth">
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
						</table>
					</div>
				</div>
				
				<div class="Seperator"></div>
				
				<div class="Filter-Form">
					<div class="Filter-Form-Content">
						<table border="0" cellpadding="5" cellspacing="0">
							<!-- Credit Card Number -->
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Credit Card')" />
										<xsl:with-param name="field" select="string('CardNumber')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="Values[CreditCard-CardNumber]" class="input-string" size="4" />
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
						</table>
					</div>
				</div>
			</div>
			
			<div class="Clear"></div>
			
			<input type="submit" class="input-submit" value="Continue &#0187;" />
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
