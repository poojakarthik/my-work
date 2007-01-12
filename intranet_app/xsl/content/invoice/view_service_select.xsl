<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Invoice Details</h1>
		<div class="Seperator"></div>
		
		<h2>Select a Service</h2>
		<p>
			In order to view information placed on this invoice, you need to select a Service.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th width="150">Line Number</th>
				<th width="120" class="Currency">Credit</th>
				<th width="120" class="Currency">Debit</th>
			</tr>
			<xsl:for-each select="/Response/ServiceTotals/Results/rangeSample/ServiceTotal">
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
					
					<td><xsl:value-of select="/Response/ServiceTotals/Results/rangeStart + position()" />.</td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_view.php</xsl:text>
								<xsl:text>?Invoice=</xsl:text><xsl:value-of select="/Response/Invoice/Id" />
								<xsl:text>&amp;ServiceTotal=</xsl:text><xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./FNN" />
						</a>
					</td>
					<td class="Currency"><xsl:value-of select="./Credit" /></td>
					<td class="Currency"><xsl:value-of select="./Debit" /></td>
				</tr>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
	</xsl:template>
</xsl:stylesheet>
