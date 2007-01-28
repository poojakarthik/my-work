<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Unbilled Charges</h1>
		
		<h2 class="Service">Service Details</h2>
		
		<div class="Wide-Form">
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Service')" />
							<xsl:with-param name="field" select="string('FNN')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Service/FNN" />
					</td>
				</tr>
			</table>
		</div>
		
		<div class="Seperator"></div>
		
		
		<h2 class="Charge">Unbilled Charges</h2>
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>
					<xsl:choose>
						<xsl:when test="/Response/Service/ServiceType = 103">
							Source
						</xsl:when>
						<xsl:otherwise>
							Destination
						</xsl:otherwise>
					</xsl:choose>
				</th>
				<th>Start Date/Time</th>
				<th class="Currency">Duration</th>
				<th class="Currency">Charge</th>
				<th class="Currency">Actions</th>
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
								<xsl:value-of select="./Source" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="./Destination" />
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
					<td class="Currency"><xsl:value-of select="./Units" /></td>
					<td class="Currency"><xsl:value-of select="./Charge" /></td>
					<td class="Currency">
						<a href="#" title="CDR Information" alt="View CDR Record Information">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, </xsl:text>
									<xsl:text>'cdr_view.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>'</xsl:text>
								<xsl:text>)</xsl:text>
							</xsl:attribute>
							View CDR
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/CDRs-Unbilled/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Unbilled Charges associated with this service.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/CDRs-Unbilled/rangeSample/CDR) = 0">
				<div class="MsgErrorWide">
					There are no Unbilled Charges matching your search.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<xsl:if test="/Response/CDRs-Unbilled/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/rangePage != 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_unbilled.php</xsl:text>
											
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/rangeLength" />
											
											<xsl:text>&amp;rangePage=1</xsl:text>
										</xsl:attribute>
										<xsl:text>- First</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									- First
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/rangePage &gt; 1">
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
								</xsl:when>
								<xsl:otherwise>
									- Prev
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="60%" align="center">
							Page <xsl:value-of select="/Response/CDRs-Unbilled/rangePage" />
							of <xsl:value-of select="/Response/CDRs-Unbilled/rangePages" /><br />
							Showing <xsl:value-of select="/Response/CDRs-Unbilled/rangeStart + 1" />
							to
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/rangeLength + /Response/CDRs-Unbilled/rangeStart &gt; /Response/CDRs-Unbilled/collationLength">
									<xsl:value-of select="/Response/CDRs-Unbilled/collationLength" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDRs-Unbilled/rangeStart + /Response/CDRs-Unbilled/rangeLength" />
								</xsl:otherwise>
							</xsl:choose>
							of <xsl:value-of select="/Response/CDRs-Unbilled/collationLength" />
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/rangePage &lt; /Response/CDRs-Unbilled/rangePages">
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
								</xsl:when>
								<xsl:otherwise>
									Next -
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/rangePage != /Response/CDRs-Unbilled/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_unbilled.php</xsl:text>
											
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/rangePages" />
										</xsl:attribute>
										<xsl:text>Last -</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									Last -
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</table>
			</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
