<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!-- Page for adding Credit Card Details -->
		
		<h1>Add Credit Card Details</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'CardType'">
						Please select a valid Credit Card Type.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Name'">
						Please enter a Credit Card Holder Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CardNumber'">
						Please enter a Credit Card #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ExpMonth'">
						Please enter a Credit Card Expiry Month.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ExpYear'">
						Please enter a Credit Card Expiry Year.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="creditcard_add.php">
			<input type="hidden" name="AccountGroup">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/AccountGroup/Id" />
				</xsl:attribute>
			</input>

			
			<!-- Account Details -->
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
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
			<div class="Seperator"></div>
			
			<h2 class="Payment">Credit Card Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
					<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Credit Card')" />
								<xsl:with-param name="field" select="string('CardType')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="CreditCard[CardType]">
								<xsl:for-each select="/Response/CreditCardTypes/CreditCardType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:choose>
											<xsl:when test="./Id = /Response/ui-values/CreditCard/CardType">
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
					<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Credit Card')" />
								<xsl:with-param name="field" select="string('Name')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="CreditCard[Name]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/CreditCard/Name" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
					<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Credit Card')" />
								<xsl:with-param name="field" select="string('CardNumber')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="CreditCard[CardNumber]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/CreditCard/CardNumber" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
					<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Credit Card')" />
								<xsl:with-param name="field" select="string('ExpiryDate')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="CreditCardExpiry">
								<xsl:with-param name="Name-Month"		select="string('CreditCard[ExpMonth]')" />
								<xsl:with-param name="Name-Year"		select="string('CreditCard[ExpYear]')" />
								<xsl:with-param name="Selected-Month"	select="/Response/ui-values/CreditCard/ExpMonth" />
								<xsl:with-param name="Selected-Year"	select="/Response/ui-values/CreditCard/ExpYear" />
							</xsl:call-template>
						</td>
					</tr>
					<tr>
					<td class="Required"> </td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Credit Card')" />
								<xsl:with-param name="field" select="string('CVV')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="CreditCard[CVV]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/CreditCard/CVV" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Add Credit Card Details &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
