<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="utf-8" />
	
	<xsl:template match="/">
		<xsl:for-each select="/Response/Payments/Results/rangeSample/Payment">
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./TXNReference" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./Amount" />
			<xsl:text>"</xsl:text>
			<xsl:text>&#10;</xsl:text>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
