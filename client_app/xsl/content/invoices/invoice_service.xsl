<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Invoice">Invoice #<xsl:value-of select="/Response/Invoice/Id" /></h2>
		
		<h3>Service Details</h3>
		<p>
			Information about a services that appears on a particular invoice.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Service Number:</th>
				<td>
					<xsl:value-of select="/Response/InvoiceService/FNN" />
				</td>
			</tr>
		</table>
		
		<xsl:if test="/Response/InvoiceService/InvoiceServiceCalls/rangePage = 1">
			<h3>Service Charges</h3>
			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr class="first">
					<th>Charged On</th>
					<th>Ref.</th>
					<th>Description</th>
					<th>Amount</th>
				</tr>
				<xsl:choose>
					<xsl:when test="count(/Response/InvoiceService/InvoiceServiceCharges/rangeSample/Charge) != 0">
						<xsl:for-each select="/Response/InvoiceService/InvoiceServiceCharges/rangeSample/Charge">
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
								
								<td>
									<xsl:call-template name="dt:format-date-time">
										<xsl:with-param name="year"		select="./ChargedOn/year" />
										<xsl:with-param name="month"	select="./ChargedOn/month" />
										<xsl:with-param name="day"		select="./ChargedOn/day" />
										<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
									</xsl:call-template>
								</td>
								<td><xsl:value-of select="./ChargeType" disable-output-escaping="yes" /></td>
								<td><xsl:value-of select="./Description" disable-output-escaping="yes" /></td>
								<td><xsl:value-of select="./Amount" /> <xsl:value-of select="./Nature" /></td>
							</tr>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<tr class="odd">
							<td colspan="4">No Service Charges were applied to this Service on this Invoice.</td>
						</tr>
					</xsl:otherwise>
				</xsl:choose>
			</table>
		</xsl:if>
		
		<h3>Call Information</h3>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr class="first">
				<th>#</th>
				<th>Date</th>
				<th>Time</th>
				<th>Called Party</th>
				<th>Duration</th>
				<th>Charge</th>
			</tr>
			<xsl:choose>
				<xsl:when test="count (/Response/InvoiceService/InvoiceServiceCalls/rangeSample/CDR) != 0">
					<xsl:for-each select="/Response/InvoiceService/InvoiceServiceCalls/rangeSample/CDR">
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
							
							<td>
								<xsl:value-of select="./Id" />
							</td>
							<td>
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="./StartDatetime/year" />
									<xsl:with-param name="month"	select="./StartDatetime/month" />
									<xsl:with-param name="day"		select="./StartDatetime/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</td>
							<td>
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="hour"		select="./StartDatetime/hour" />
									<xsl:with-param name="minute"	select="./StartDatetime/minute" />
									<xsl:with-param name="second"	select="./StartDatetime/second" />
									<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
								</xsl:call-template>
							</td>
							<td>
								<xsl:value-of select="./Destination" />
							</td>
							<td>
								<xsl:value-of select="./Duration" />
							</td>
							<td>
								<xsl:value-of select="./Charge" />
							</td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
					<tr class="odd">
						<td colspan="6">
							There were no calls on this invoice for the range you are viewing.
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
		</table>
		
		<xsl:if test="/Response/InvoiceService/InvoiceServiceCalls/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="33%" align="left">
							<xsl:if test="/Response/InvoiceService/InvoiceServiceCalls/rangePage &gt; 1">
								<a>
									<xsl:attribute name="href">
										<xsl:text>invoice_service.php?Invoice=</xsl:text>
										<xsl:value-of select="/Response/Invoice/Id" />
										<xsl:text>&amp;Id=</xsl:text>
										<xsl:value-of select="/Response/InvoiceService/Id" />
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/InvoiceService/InvoiceServiceCalls/rangePage - 1" />
									</xsl:attribute>
									<xsl:text>- Prev</xsl:text>
								</a>
							</xsl:if>
						</td>
						<td width="34%" align="center">
							Page <xsl:value-of select="/Response/InvoiceService/InvoiceServiceCalls/rangePage" />
							of <xsl:value-of select="/Response/InvoiceService/InvoiceServiceCalls/rangePages" /><br />
							(Results Per Page: <xsl:value-of select="/Response/InvoiceService/InvoiceServiceCalls/rangeLength" />)
						</td>
						<td width="33%" align="right">
							<xsl:if test="/Response/InvoiceService/InvoiceServiceCalls/rangePage &lt; /Response/InvoiceService/InvoiceServiceCalls/rangePages">
								<a>
									<xsl:attribute name="href">
										<xsl:text>invoice_service.php?Invoice=</xsl:text>
										<xsl:value-of select="/Response/Invoice/Id" />
										<xsl:text>&amp;Id=</xsl:text>
										<xsl:value-of select="/Response/InvoiceService/Id" />
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/InvoiceService/InvoiceServiceCalls/rangePage + 1" />
									</xsl:attribute>
									<xsl:text>Next -</xsl:text>
								</a>
							</xsl:if>
						</td>
					</tr>
				</table>
			</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
