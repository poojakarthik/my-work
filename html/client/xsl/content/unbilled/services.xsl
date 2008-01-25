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
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
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
						<xsl:text> Clickable</xsl:text>
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
							(100 Indial)
						</xsl:if>
					</td>
					<td class="Currency">
						<xsl:call-template name="Currency">
							<xsl:with-param name="Number"	select="./TotalCharge" />
							<xsl:with-param name="Decimal"	select="number('2')" />
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
			<tr class="Foot">
				<td>Total Charges:</td>
				<td></td>
				<td class="Currency">
					<xsl:call-template name="Currency">
						<xsl:with-param name="Number"	select="sum(/Response/Services/Service/TotalCharge)" />
						<xsl:with-param name="Decimal"	select="number('2')" />
					</xsl:call-template>
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
