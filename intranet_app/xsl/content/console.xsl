<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Administrative Console</h1>
		
		Welcome, <xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/FirstName" />.
		You are currently logged into your Employee Account.
	</xsl:template>
</xsl:stylesheet>
