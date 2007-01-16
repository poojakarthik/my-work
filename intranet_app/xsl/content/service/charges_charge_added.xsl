<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Charge Assigned</h1>
		
		<p>
			Your charge has successfully been assigned to your requested service.
		</p>
		
		<p>
			You can now return to
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_view.php?Id=</xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
				<xsl:text>Details about this Service</xsl:text>
			</a>.
		</p>
	</xsl:template>
</xsl:stylesheet>
