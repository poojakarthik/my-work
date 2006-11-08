<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2>Contact: <xsl:value-of select="/Response/Contact/UserName" /></h2>
		
		<h3>Profile Updated</h3>
		<p>
			You have successfully updated the profile for
			<xsl:value-of select="/Response/Contact/UserName" />.
		</p>
		
		<a>
			<xsl:attribute name="href">
				<xsl:text>contact.php?Id=</xsl:text>
				<xsl:value-of select="/Response/Contact/Id" />
			</xsl:attribute>
			Back to Profile
		</a>
	</xsl:template>
</xsl:stylesheet>
