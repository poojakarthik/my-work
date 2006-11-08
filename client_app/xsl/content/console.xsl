<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h2>Console</h2>
		
		<p>
			Welcome, <strong><xsl:value-of select="/Response/Authentication/AuthenticatedContact/UserName" /></strong>.
			You are currently logged into your account.
		</p>
		
		<ul>
			<xsl:choose>
				<xsl:when test="/Response/Authentication/AuthenticatedContact/CustomerContact = 1">
					<li><a href="contacts.php">List my Contacts</a></li>
					<li><a href="accounts.php">View my Accounts</a></li>
				</xsl:when>
			</xsl:choose>
			<li><a href="account.php">View my Primary Account</a></li>
		</ul>
	</xsl:template>
</xsl:stylesheet>
