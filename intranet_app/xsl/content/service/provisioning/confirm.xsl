<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Provisioning Request Added</h1>
		
		<p>
			Your Provisioning Request has been successfully added.
		</p>
		
		<p>
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_address.php?Service=</xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
				<xsl:text>Return to Provisioning</xsl:text>
			</a>.
		</p>
	</xsl:template>
</xsl:stylesheet>
