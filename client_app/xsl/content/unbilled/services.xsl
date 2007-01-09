<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Invoice">Unbilled Charges</h2>
		
		<h3>Services</h3>
		<p>
			Click a service to view call details for the particular service.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr class="first">
				<th>Service Number</th>
				<th>Service Type</th>
				<th class="Currency">Unbilled Charges</th>
			</tr>
			<xsl:for-each select="/Response/Services/Service">
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
						<xsl:text>window.location='unbilled_service.php?Account=</xsl:text>
						<xsl:value-of select="/Response/Account/Id" />
						<xsl:text>&amp;Service=</xsl:text>
						<xsl:value-of select="./Id" />
						<xsl:text>'</xsl:text>
					</xsl:attribute>
					<td>
						<xsl:value-of select="./FNN"  />
					</td>
					<td>
						<xsl:value-of select="./NamedServiceType/NamedServiceType[@selected='selected']" />
						<xsl:if test="./Indial100 = '1'">
							(100 Indial Number)
						</xsl:if>
					</td>
					<td class="Currency"><xsl:value-of select="./TotalCharge" /></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
