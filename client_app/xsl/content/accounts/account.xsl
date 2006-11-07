<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2>
			Account: <xsl:value-of select="/Response/Account/BusinessName" />
			[<xsl:value-of select="/Response/Account/TradingName" />]
		</h2>
		
		<h3>Account Details</h3>
		<p>
			Details about this particular account.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Account Number:</th>
				<td><xsl:value-of select="/Response/Account/Id" /></td>
			</tr>
			<tr>
				<th>Business Name:</th>
				<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
			</tr>
			<tr>
				<th>Trading Name:</th>
				<td><xsl:value-of select="/Response/Account/TradingName" /></td>
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
				<td><xsl:value-of select="/Response/Account/Address1" /></td>
			</tr>
			<tr>
				<th></th>
				<td><xsl:value-of select="/Response/Account/Address2" /></td>
			</tr>
			<tr>
				<th>Suburb:</th>
				<td><xsl:value-of select="/Response/Account/Suburb" /></td>
			</tr>
			<tr>
				<th>Postcode:</th>
				<td><xsl:value-of select="/Response/Account/Postcode" /></td>
			</tr>
			<tr>
				<th>State:</th>
				<td><xsl:value-of select="/Response/Account/State" /></td>
			</tr>
			<tr>
				<th>Country:</th>
				<td><xsl:value-of select="/Response/Account/Country" /></td>
			</tr>
		</table>
		
		<h3>Account Charges</h3>
		<ul>
			<li>
				<a>
					<xsl:attribute name="href">
						<xsl:text>unbilled.php?Account=</xsl:text>
						<xsl:value-of select="/Response/Account/Id" />
					</xsl:attribute>
					View my Unbilled Charges
				</a>
			</li>
		</ul>
		
		<h3>Invoices</h3>
		<table border="1" cellpadding="5" cellspacing="0">
			<tr>
				<th>Id</th>
				<th>Created On</th>
			</tr>
			<xsl:for-each select="/Response/Invoices/Invoice">
				<tr>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							#<xsl:value-of select="./Id" />
						</a>
					</td>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./CreatedOn/year" />
							<xsl:with-param name="month"	select="./CreatedOn/month" />
							<xsl:with-param name="day"	select="./CreatedOn/day" />
 							<xsl:with-param name="hour"	select="0" />
							<xsl:with-param name="minute"	select="0" />
							<xsl:with-param name="second"	select="0" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
