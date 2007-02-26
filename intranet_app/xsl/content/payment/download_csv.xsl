<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="utf-8" />
	
	<xsl:template match="/">
		<xsl:text>"Account Group", "Account Id", "Business Name", "Trading Name", "Reference", "Date", "Amount"</xsl:text>
		<xsl:text>&#10;</xsl:text>
		
		<xsl:for-each select="/Response/Payments/Results/rangeSample/Payment">
			<xsl:variable name="Payment" select="." />
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="$Payment/AccountGroup" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="$Payment/Account" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="/Response/Accounts/Account[./Id = $Payment/Account]/BusinessName" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="/Response/Accounts/Account[./Id = $Payment/Account]/TradingName" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="$Payment/TXNReference" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="$Payment/PaidOn/year" />
			<xsl:text>-</xsl:text>
			<xsl:value-of select="$Payment/PaidOn/month" />
			<xsl:text>-</xsl:text>
			<xsl:value-of select="$Payment/PaidOn/day" />
			<xsl:text>"</xsl:text>
			<xsl:text>,</xsl:text>
			
			<xsl:text>"</xsl:text>
			<xsl:value-of select="$Payment/Amount" />
			<xsl:text>"</xsl:text>
			<xsl:text>&#10;</xsl:text>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
