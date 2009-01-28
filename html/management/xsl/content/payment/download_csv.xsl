<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="utf-8" />
	
	<xsl:template match="/">
		<xsl:text>"Sequence","Account Group","Account Id","Business Name","Trading Name","Customer Group","Reference","Date","Amount"</xsl:text>
		<xsl:text>&#10;</xsl:text>
		
		<xsl:for-each select="/Response/Payments/Record">
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="position()" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./AccountGroup" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./Account" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./BusinessName" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./TradingName" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./CustomerGroup" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./TXNReference" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./PaidOn" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="./Amount" />
			<xsl:text>"</xsl:text>
			<xsl:text>&#10;</xsl:text>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
