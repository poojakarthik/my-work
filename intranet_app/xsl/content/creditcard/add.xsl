<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add Credit Card Details</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'CardType'">
						The Card Type you selected was invalid.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Name'">
						You must enter a Card Holder Name. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CardNumber'">
						You must enter a Card Card Number. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ExpMonth'">
						The Month you entered for Credit Card Expiration was Invalid. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ExpYear'">
						The Year you entered for Credit Card Expiration was Invalid. Please try again.
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
			
			<h2 class="Account">Account Details</h2>
			<div class="Filter-Form">
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
			<div class="Seperator"></div>
			
			<h2 class="Payment">Credit Card Details</h2>
			<div class="Filter-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
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
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Credit Card Details &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
