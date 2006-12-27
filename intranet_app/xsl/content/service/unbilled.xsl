<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Unbilled Charges</h1>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Calling Party</th>
				<th>Start Date/Time</th>
				<th class="Currency">Duration</th>
				<th class="Currency">Cost</th>
				<th class="Currency">Options</th>
			</tr>
			<xsl:for-each select="/Response/CDRs-Unbilled/rangeSample/CDR">
				<tr>
					<xsl:attribute name="class">
						<xsl:choose>
							<xsl:when test="position() mod 2 = 1">
								<xsl:text>Odd</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>Even</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
					<td><xsl:value-of select="/Response/CDRs-Unbilled/rangeStart + position()" />.</td>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Service/ServiceType = 103">
								<xsl:value-of select="./Source" disable-output-escaping="yes" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="./Destination" disable-output-escaping="yes" />
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./StartDatetime/year" />
							<xsl:with-param name="month"	select="./StartDatetime/month" />
							<xsl:with-param name="day"		select="./StartDatetime/day" />
	 						<xsl:with-param name="hour"	select="./StartDatetime/hour" />
							<xsl:with-param name="minute"	select="./StartDatetime/minute" />
							<xsl:with-param name="second"	select="./StartDatetime/second" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
						</xsl:call-template>
					</td>
					<td class="Currency"><xsl:value-of select="./Units" disable-output-escaping="yes" /></td>
					<td class="Currency"><xsl:value-of select="./Charge" disable-output-escaping="yes" /></td>
					<td class="Currency">
						<a>
							<xsl:attribute name="href">
							</xsl:attribute>
							View CDR Record
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/CDRs-Unbilled/collationLength = 0">
				<div class="MsgError">
					There are no CDR Records associated with this service.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/CDRs-Unbilled/rangeSample/CDR) = 0">
				<div class="MsgNotice">
					There are no CDR Records for the Range that you Searched for.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<xsl:if test="/Response/CDRs-Unbilled/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="33%" align="left">
							<xsl:if test="/Response/CDRs-Unbilled/rangePage &gt; 1">
								<a>
									<xsl:attribute name="href">
										<xsl:text>service_unbilled.php</xsl:text>
										
										<xsl:text>?Id=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" />
										
										<xsl:text>&amp;rangeLength=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Unbilled/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Unbilled/rangePage - 1" />
									</xsl:attribute>
									<xsl:text>- Prev</xsl:text>
								</a>
							</xsl:if>
						</td>
						<td width="34%" align="center">
							Page <xsl:value-of select="/Response/CDRs-Unbilled/rangePage" />
							of <xsl:value-of select="/Response/CDRs-Unbilled/rangePages" /><br />
							Showing  
							<xsl:value-of select="/Response/CDRs-Unbilled/rangeStart + 1" />
							to
							<xsl:value-of select="/Response/CDRs-Unbilled/rangeStart + /Response/CDRs-Unbilled/rangeLength" />
							of
							<xsl:value-of select="/Response/CDRs-Unbilled/collationLength" />
						</td>
						<td width="33%" align="right">
							<xsl:if test="/Response/CDRs-Unbilled/rangePage &lt; /Response/CDRs-Unbilled/rangePages">
								<a>
									<xsl:attribute name="href">
										<xsl:text>service_unbilled.php</xsl:text>
										
										<xsl:text>?Id=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" />
										
										<xsl:text>&amp;rangeLength=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Unbilled/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Unbilled/rangePage + 1" />
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
