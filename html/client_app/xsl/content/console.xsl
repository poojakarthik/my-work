<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Contact">Console</h2>
		
		<p>
			Welcome, <strong><xsl:value-of select="/Response/Authentication/AuthenticatedContact/FirstName" />
			<xsl:text> </xsl:text>
			<xsl:value-of select="/Response/Authentication/AuthenticatedContact/LastName" /></strong>.
			You are currently logged into your account.
		</p>
		
		<hr />
		
		<table border="0" cellpadding="10" cellspacing="0" style="padding-top: 20px">
			<xsl:if test="/Response/Authentication/AuthenticatedContact/CustomerContact = 1">
				<tr>
					<td><a href="contacts.php"><img src="img/contacts.png" /></a></td>
					<td>
						<a href="contacts.php" class="link">List Company Contacts</a><br />
						Let's you edit Contacts who are associated with your Company.
					</td>
				</tr>
			</xsl:if>
			<tr>
				<td><a href="contact.php"><img src="img/contact.png" /></a></td>
				<td>
					<a href="contact.php" class="link">View My Profile</a><br />
					Allows you to edit your Person Profile.
				</td>
			</tr>
			<xsl:if test="/Response/Authentication/AuthenticatedContact/CustomerContact = 1">
				<tr>
					<td><a href="accounts.php"><img src="img/accounts.png" /></a></td>
					<td>
						<a href="accounts.php" class="link">List All Accounts</a><br />
						List all the Accounts associated with your Company.
					</td>
				</tr>
			</xsl:if>
			<tr>
				<td><a href="account.php"><img src="img/account.png" /></a></td>
				<td>
					<a href="account.php" class="link">View My Account</a><br />
					View details relating to your Primary Account.
				</td>
			</tr>
			<tr>
				<td><a href="logout.php"><img src="img/logout.png" /></a></td>
				<td>
					<a href="logout.php" class="link">Logout of Account</a><br />
					Logout of your TelcoBlue Internet Account.
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
