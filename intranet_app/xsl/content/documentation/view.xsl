<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Documentation: <xsl:value-of select="/Response/DocumentationDetails/Field/Label" /></h1>
		
		<h2><xsl:value-of select="/Response/DocumentationDetails/Field/Title" /></h2>
		<div class="Seperator"></div>
		<div class="Filter-Form">
			<pre style="line-height: 100%;"><xsl:value-of select="/Response/DocumentationDetails/Field/Description" /></pre>
		</div>
	</xsl:template>
</xsl:stylesheet>
