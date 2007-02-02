<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Account">
			Account: <xsl:value-of select="/Response/Account/BusinessName"  />
			<xsl:if test="/Response/Account/TradingName != ''">
				[<xsl:value-of select="/Response/Account/TradingName"  />]
			</xsl:if>
		</h2>
		
		<h3>Account Details</h3>
		<p>
			Details about this particular account.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<th>Account Number:</th>
				<td><xsl:value-of select="/Response/Account/Id" /></td>
			</tr>
			<tr>
				<th>Business Name:</th>
				<td><xsl:value-of select="/Response/Account/BusinessName"  /></td>
			</tr>
			<tr>
				<th>Trading Name:</th>
				<td><xsl:value-of select="/Response/Account/TradingName"  /></td>
			</tr>
			<tr>
				<th>ABN:</th>
				<td><xsl:value-of select="/Response/Account/ABN" /></td>
			</tr>
			<tr>
				<th>ACN:</th>
				<td><xsl:value-of select="/Response/Account/ACN" /></td>
			</tr>
			<tr>
				<th>Address:</th>
				<td><xsl:value-of select="/Response/Account/Address1"  /></td>
			</tr>
			<xsl:if test="/Response/Account/Address2 != ''">
				<tr>
					<th></th>
					<td><xsl:value-of select="/Response/Account/Address2"  /></td>
				</tr>
			</xsl:if>
			<tr>
				<th>Suburb:</th>
				<td><xsl:value-of select="/Response/Account/Suburb"  /></td>
			</tr>
			<tr>
				<th>Postcode:</th>
				<td><xsl:value-of select="/Response/Account/Postcode" /></td>
			</tr>
			<tr>
				<th>State:</th>
				<td><xsl:value-of select="/Response/Account/State"  /></td>
			</tr>
			<tr>
				<th>Country:</th>
				<td><xsl:value-of select="/Response/Account/Country"  /></td>
			</tr>
		</table>
		
		<hr style="margin-top: 20px; margin-bottom: 20px" />
		
		<h3>Account Charges</h3>
		<ul>
			<li>
				<a class="link">
					<xsl:attribute name="href">
						<xsl:text>unbilled.php?Account=</xsl:text>
						<xsl:value-of select="/Response/Account/Id" />
					</xsl:attribute>
					View my Unbilled Charges
				</a>
			</li>
		</ul>
		
		<h3>Invoices</h3>
		<p>
			Listed below are a list of all the invoices that have been 
			issued to you in the past. Click on an invoice to view 
			further information.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
			<tr class="first">
				<th>Invoice Number</th>
				<th>Issued On</th>
				<th>Settled On</th>
				<th class="Currency">Invoice Total</th>
			</tr>
			<xsl:for-each select="/Response/Invoices/Invoice">
				<tr>
					<xsl:attribute name="class">
						<xsl:choose>
							<xsl:when test="position() mod 2 = 1">
								<xsl:text>odd</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>even</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> Clickable</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="onclick">
						<xsl:text>window.location='invoice.php?Id=</xsl:text>
						<xsl:value-of select="./Id" />
						<xsl:text>'</xsl:text>
					</xsl:attribute>
					<td>#<xsl:value-of select="./Id" /></td>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./CreatedOn/year" />
							<xsl:with-param name="month"	select="./CreatedOn/month" />
							<xsl:with-param name="day"	select="./CreatedOn/day" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
						</xsl:call-template>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Invoice/Status = 103">
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"	select="/Response/Invoice/SettledOn/year" />
									<xsl:with-param name="month"	select="/Response/Invoice/SettledOn/month" />
									<xsl:with-param name="day"	select="/Response/Invoice/SettledOn/day" />
									<xsl:with-param name="hour"	select="0" />
									<xsl:with-param name="minute"	select="0" />
									<xsl:with-param name="second"	select="0" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<span style="color: #CC0000">Not Settled</span>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td class="Currency">
						<xsl:call-template name="Currency">
							<xsl:with-param name="Number"	select="./Total + ./Tax" />
							<xsl:with-param name="Decimal"	select="number('2')" />
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
