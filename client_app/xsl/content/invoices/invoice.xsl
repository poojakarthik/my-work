<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Invoice">Invoice #<xsl:value-of select="/Response/Invoice/Id" /></h2>
		
		<h3>Invoice Details</h3>
		<p>
			Information about an invoice that has been charged to you.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Created On:</th>
				<td>
					<xsl:call-template name="dt:format-date-time">
						<xsl:with-param name="year"	select="/Response/Invoice/CreatedOn/year" />
						<xsl:with-param name="month"	select="/Response/Invoice/CreatedOn/month" />
						<xsl:with-param name="day"	select="/Response/Invoice/CreatedOn/day" />
 						<xsl:with-param name="hour"	select="0" />
						<xsl:with-param name="minute"	select="0" />
						<xsl:with-param name="second"	select="0" />
						<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
					</xsl:call-template>
				</td>
			</tr>
			<tr>
				<th>Due Date:</th>
				<td>
					<xsl:call-template name="dt:format-date-time">
						<xsl:with-param name="year"	select="/Response/Invoice/DueOn/year" />
						<xsl:with-param name="month"	select="/Response/Invoice/DueOn/month" />
						<xsl:with-param name="day"	select="/Response/Invoice/DueOn/day" />
 						<xsl:with-param name="hour"	select="0" />
						<xsl:with-param name="minute"	select="0" />
						<xsl:with-param name="second"	select="0" />
						<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
					</xsl:call-template>
				</td>
			</tr>
			<tr>
				<th>Settlement:</th>
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
			</tr>
		</table>
		
		<h3>Invoice Charges</h3>
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Total Debits:</th>
				<td class="Currency">
					<xsl:call-template name="Currency">
						<xsl:with-param name="Number"	select="/Response/Invoice/Debits" />
						<xsl:with-param name="Decimal"	select="number('4')" />
					</xsl:call-template>
				</td>
			</tr>
			<tr>
				<th>Total Credits:</th>
				<td class="Currency">
					<xsl:call-template name="Currency">
						<xsl:with-param name="Number"	select="/Response/Invoice/Credits" />
						<xsl:with-param name="Decimal"	select="number('4')" />
					</xsl:call-template>
				</td>
			</tr>
			<tr>
				<th>Sub-Total:</th>
				<td class="Currency">
					<xsl:call-template name="Currency">
						<xsl:with-param name="Number"	select="/Response/Invoice/Total" />
						<xsl:with-param name="Decimal"	select="number('2')" />
					</xsl:call-template>
				</td>
			</tr>
			<tr>
				<th>Tax:</th>
				<td class="Currency">
					<xsl:call-template name="Currency">
						<xsl:with-param name="Number"	select="/Response/Invoice/Tax" />
						<xsl:with-param name="Decimal"	select="number('2')" />
					</xsl:call-template>
				</td>
			</tr>
			<tr>
				<th>Invoice Amount:</th>
				<td class="Currency">
					<xsl:call-template name="Currency">
						<xsl:with-param name="Number"	select="/Response/Invoice/Tax + /Response/Invoice/Total" />
						<xsl:with-param name="Decimal"	select="number('2')" />
					</xsl:call-template>
				</td>
			</tr>
		</table>

		<h3>Services</h3>
		<p>Click a service to view call details for the particular service.</p>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
			<tr class="first">
				<th>Service Number</th>
				<th class="Currency">Total Charges (ex. GST)</th>
			</tr>
			<xsl:for-each select="/Response/Invoice/InvoiceServices/InvoiceService">
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
					</xsl:attribute>
					<xsl:attribute name="onclick">
						<xsl:text>window.location='invoice_service.php?Invoice=</xsl:text>
						<xsl:value-of select="/Response/Invoice/Id" />
						<xsl:text>&amp;Id=</xsl:text>
						<xsl:value-of select="./Service" />
						<xsl:text>'</xsl:text>
					</xsl:attribute>
					<td><xsl:value-of select="./FNN" /></td>
					<td class="Currency">
						<xsl:call-template name="Currency">
							<xsl:with-param name="Number"	select="./TotalCharge" />
							<xsl:with-param name="Decimal"	select="number('4')" />
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
