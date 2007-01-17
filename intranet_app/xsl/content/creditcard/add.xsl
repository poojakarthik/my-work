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
						The Card Holder Name you entered was Blank.
					</xsl:when>
					<xsl:when test="/Response/Error = 'CardNumber'">
						The Credit Card Number you entered was Blank.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ExpMonth'">
						The Month you entered for Credit Card Expiration was Invalid.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ExpYear'">
						The Year you entered for Credit Card Expiration was Invalid.
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
						
						<!-- TODO!bash! Credit card exp date should always display as mm/yyyy -->
						<!-- TODO!bash! put this code in its own template, use the template for ALL cc exp date display EVERYWHERE -->
						<!-- TODO!bash! this way we can update it in one place and have it update on all pages -->
						<td>
							<select name="CreditCard[ExpMonth]">
								<xsl:call-template name="Date_Loop">
									<xsl:with-param name="start" select="1" />
									<xsl:with-param name="cease" select="12" />
									<xsl:with-param name="select" select="/Response/ui-values/CreditCard/ExpMonth" />
								</xsl:call-template>
							</select> /
							<select name="CreditCard[ExpYear]">
								<xsl:call-template name="Date_Loop">
									<xsl:with-param name="start" select="7" />
									<xsl:with-param name="cease" select="17" />
									<xsl:with-param name="select" select="/Response/ui-values/CreditCard/ExpYear" />
								</xsl:call-template>
							</select>
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
