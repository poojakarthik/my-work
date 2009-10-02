<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="utf-8" />
	
	<xsl:template match="/">
		<xsl:for-each select="/customer-groups/customer-group">
			<!-- Customer Group Details -->
			<xsl:text>"Customer Group:","</xsl:text>
			<xsl:value-of select="./name" />
			<xsl:text>",</xsl:text>
			
			<xsl:text>"Bank Account Name:","</xsl:text>
			<xsl:value-of select="./bank-account-name" />
			<xsl:text>",</xsl:text>
			
			<xsl:text>"BSB:",</xsl:text>
			<xsl:value-of select="./bank-bsb" />
			<xsl:text>",</xsl:text>
			
			<xsl:text>"Bank Account Number:",</xsl:text>
			<xsl:value-of select="./bank-account-number" />
			<xsl:text>"</xsl:text>
			
			<xsl:text>&#10;</xsl:text>
			
			<!-- Payment Column Headers -->
			<xsl:text>"Payment ID","Account Group","Account","Account Name","Trading Name","Reference","Date","Amount"</xsl:text>
			<xsl:text>&#10;</xsl:text>
			
			<!-- Payments -->
			<xsl:for-each select="./payments/payment">
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./payment-id" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./account-group-id" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./account-id" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./business-name" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./trading-name" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./transaction-reference" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./paid-on" />
				<xsl:text>"</xsl:text>
				<xsl:text>,</xsl:text>
				
				<xsl:text>"</xsl:text>
				<xsl:value-of select="./amount" />
				<xsl:text>"</xsl:text>
				
				<xsl:text>&#10;</xsl:text>
				
			</xsl:for-each>
			
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>