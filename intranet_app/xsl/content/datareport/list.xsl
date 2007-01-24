<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Data Report Listing</h1>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Report Name</th>
				<th>Report Summary</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/DataReports/Results/rangeSample/DataReport">
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
					
					<td><xsl:value-of select="/Response/DataReports/Results/rangeStart + position()" />.</td>
					<td><xsl:value-of select="./Name" /></td>
					<td><xsl:value-of select="./Summary" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>datareport_run.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>Run Report</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/DataReports/Results/collationLength = 0">
				<div class="MsgError">
					There are no Data Reports currently in the System.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/DataReports/Results/rangeSample/DataReport) = 0">
				<div class="MsgNotice">
					There are no Data Reports in the Range you Searched through.
				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
