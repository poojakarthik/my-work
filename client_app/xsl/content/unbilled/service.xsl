<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Invoice">Unbilled Charges</h2>
		
		<h3>Service Details</h3>
		<p>
			Information about a services that appears on a particular invoice.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Service Number:</th>
				<td><xsl:value-of select="/Response/Service/FNN" /></td>
			</tr>
		</table>
		
		<xsl:if test="/Response/Service/UnbilledCalls/rangePage = 1">
			<h3>Service Charges</h3>
			<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
				<tr class="first">
					<th>Charged On</th>
					<th>Ref.</th>
					<th>Description</th>
					<th>Amount</th>
				</tr>
				<xsl:choose>
					<xsl:when test="count(/Response/Service/UnbilledCharges/rangeSample/Charge) = 0">
						<tr class="odd">
							<td colspan="4">There are no charges in this list.</td>
						</tr>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="/Response/Service/UnbilledCharges/rangeSample/Charge">
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
								<td><xsl:value-of select="./ChargeType"  /></td>
								<td><xsl:value-of select="./Description"  /></td>
								<td><xsl:value-of select="./Amount" /> <xsl:value-of select="./Nature" /></td>
							</tr>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</table>
		</xsl:if>
		
		<h3>Call Information</h3>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
			<tr class="first">
				<th>#</th>
				<th>Date</th>
				<th>Time</th>
				<th>Called Party</th>
				<th>Duration</th>
				<th>Charge</th>
			</tr>
			<xsl:choose>
				<xsl:when test="/Response/Service/UnbilledCalls/rangePages != 0">
					<xsl:for-each select="/Response/Service/UnbilledCalls/rangeSample/CDR">
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
							There are currently no calls in your unbilled charges for this service.
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
		</table>
		
		<xsl:if test="/Response/Service/UnbilledCalls/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="33%" align="left">
							<xsl:if test="/Response/Service/UnbilledCalls/rangePage &gt; 1">
								<a>
									<xsl:attribute name="href">
										<xsl:text>unbilled_service.php?Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
										<xsl:text>&amp;Service=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" />
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/Service/UnbilledCalls/rangePage - 1" />
									</xsl:attribute>
									<xsl:text>- Prev</xsl:text>
								</a>
							</xsl:if>
						</td>
						<td width="34%" align="center">
							Page <xsl:value-of select="/Response/Service/UnbilledCalls/rangePage" />
							of <xsl:value-of select="/Response/Service/UnbilledCalls/rangePages" /><br />
							(Results Per Page: <xsl:value-of select="/Response/Service/UnbilledCalls/rangeLength" />)
						</td>
						<td width="33%" align="right">
							<xsl:if test="/Response/Service/UnbilledCalls/rangePage &lt; /Response/Service/UnbilledCalls/rangePages">
								<a>
									<xsl:attribute name="href">
										<xsl:text>unbilled_service.php?Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
										<xsl:text>&amp;Service=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" />
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/Service/UnbilledCalls/rangePage + 1" />
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
