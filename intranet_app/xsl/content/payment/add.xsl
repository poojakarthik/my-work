<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Make Payment</h1>
		<div class="Seperator"></div>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Amount'">
						There was an error with the amount that you entered.
					</xsl:when>
				</xsl:choose>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		
		<h2 class="Account">Account Details</h2>
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<!-- TODO!!!! - URGENT - SELECT box of accounts for this contact -->
				<!-- first option is All Accounts -->
				<!-- auto use primary account & don't show SELECT if there is only one account -->
				<!-- select the account we were vieweng if we came from an account page (still show the SELECT box) -->
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('BusinessName')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Account/BusinessName" />
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
							<xsl:value-of select="/Response/Account/TradingName" />
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="Seperator"></div>
		
		<h2 class="Payment">Payment Details</h2>
		<form method="post" action="payment_add.php">
			<xsl:if test="/Response/Account">
				<input type="hidden" name="Account">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/Account/Id" />
					</xsl:attribute>
				</input>
			</xsl:if>
			<xsl:if test="/Response/AccountGroup">
				<input type="hidden" name="AccountGroup">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/AccountGroup/Id" />
					</xsl:attribute>
				</input>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaymentType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="PaymentType">
									<xsl:for-each select="/Response/PaymentTypes/PaymentType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('Amount')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Amount" class="input-string" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('TXNReference')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="TXNReference" class="input-string" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Payment &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
