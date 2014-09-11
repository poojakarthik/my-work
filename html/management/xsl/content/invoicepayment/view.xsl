<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Payment Details</h1>
		<div class="Narrow-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Invoice Number:</th>
						<td><xsl:value-of select="/Response/PaymentDetails/Invoice" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Amount Applied:</th>
						<td><xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/PaymentDetails/Applied" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Paid On:</th>
						<td><xsl:value-of select="/Response/PaymentDetails/PaidOn" /></td>
					</tr>
					
					<tr>
						<th class="JustifiedWidth">Payment Type:</th>
						<td><xsl:value-of select="/Response/PaymentDetails/TypeName" /></td>
					</tr>
					
					<tr>
						<th class="JustifiedWidth">TXN Reference:</th>
						<td><xsl:value-of select="/Response/PaymentDetails/TXN" /></td>
					</tr>
					
					<tr>
						<th class="JustifiedWidth">Payment Amount:</th>
						<xsl:call-template name="Currency">
			       			<xsl:with-param name="Number" select="/Response/PaymentDetails/Amount" />
							<xsl:with-param name="Decimal" select="number('2')" />
	       				</xsl:call-template>
					</tr>
				
					<tr>
						<th class="JustifiedWidth">Payment Balance:</th>
						<xsl:call-template name="Currency">
			       			<xsl:with-param name="Number" select="/Response/PaymentDetails/Balance" />
							<xsl:with-param name="Decimal" select="number('2')" />
	       				</xsl:call-template>
					</tr>
				</table>

			</div>

		</div>
	<xsl:if test="/Response/PaymentDetails/Status = '250'">
		<div class="MsgNoticeNarrow">
			This payment has been reversed
		</div>
	</xsl:if>
	</xsl:template>
</xsl:stylesheet>
