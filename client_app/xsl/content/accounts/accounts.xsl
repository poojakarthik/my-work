<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2>List of All Accounts</h2>
		
		<p>
			Contained below is a list of all your accounts. Click on an 
			account to view further details.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>Account Number</th>
				<th>Business Name</th>
				<th>Trading Name</th>
			</tr>
			<xsl:for-each select="/Response/Accounts/Account">
				<tr>
					<td>
						<xsl:value-of select="Id" />
					</td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>account.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./BusinessName" />
						</a>
					</td>
					<td>
						<xsl:value-of select="./TradingName" />
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
