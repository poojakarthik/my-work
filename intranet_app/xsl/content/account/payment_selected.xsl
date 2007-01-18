<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Payment Method Selected</h1>
		
		<p>
			You have successfully change the way in which this account pays their Account.
		</p>
		
		<ul>
			<li>
				Return to 
				<a>
					<xsl:attribute name="href">
						<xsl:text>account_view.php?Id=</xsl:text>
						<xsl:value-of select="/Response/Account/Id" />
					</xsl:attribute>
					<xsl:text>Information about this Account</xsl:text>
				</a>.
			</li>
		</ul>
	</xsl:template>
</xsl:stylesheet>
