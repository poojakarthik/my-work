<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Payment Details</h1>
		
		<h2>Invoice Specific Details</h2>
		<div class="Seperator"></div>
		
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Invoice Id :</th>
						<td><xsl:value-of select="/Response/InvoicePayment/Invoice" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Payment# :</th>
						<td><xsl:value-of select="/Response/InvoicePayment/Payment" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Amount :</th>
						<td><xsl:value-of select="/Response/InvoicePayment/Amount" /></td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		<div class="Seperator"></div>
		
		<h2>Payment Details</h2>
		<div class="Seperator"></div>
		
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Payment Id :</th>
						<td><xsl:value-of select="/Response/Payment/Id" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Payed On :</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"		select="/Response/Payment/PaidOn/year" />
								<xsl:with-param name="month"	select="/Response/Payment/PaidOn/month" />
								<xsl:with-param name="day"		select="/Response/Payment/PaidOn/day" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Amount :</th>
						<td><xsl:value-of select="/Response/Payment/Amount" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">TXN Reference :</th>
						<td><xsl:value-of select="/Response/Payment/TXNReference" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Balance :</th>
						<td><xsl:value-of select="/Response/Payment/Balance" /></td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
