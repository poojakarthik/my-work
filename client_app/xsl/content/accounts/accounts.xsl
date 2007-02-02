<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Accounts">List of All Accounts</h2>
		
		<p>
			Contained below is a list of all your accounts. Click on an 
			Account for view further details.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
			<tr class="first">
				<th>Account #</th>
				<th>Business Name</th>
				<th>Trading Name</th>
			</tr>
			<xsl:for-each select="/Response/Accounts/Account">
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
						<xsl:text>window.location='account.php?Id=</xsl:text>
						<xsl:value-of select="./Id" />
						<xsl:text>'</xsl:text>
					</xsl:attribute>
					<td>
						<xsl:value-of select="Id" />
					</td>
					<td>
						<xsl:value-of select="./BusinessName"  />
					</td>
					<td>
						<xsl:value-of select="./TradingName"  />
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
