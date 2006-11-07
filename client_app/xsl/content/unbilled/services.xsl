<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2>Unbilled Charges</h2>
		
		<h3>Services</h3>
		<p>
			Click a service to view call details for the particular service.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Service Number</th>
				<th>Unbilled Charges</th>
			</tr>
			<xsl:for-each select="/Response/Services/Service">
				<tr>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>unbilled_service.php?Account=</xsl:text>
								<xsl:value-of select="/Response/Account/Id" />
								<xsl:text>&amp;Service=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./FNN" />
						</a>
					</td>
					<td><xsl:value-of select="./TotalCharge" /></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
