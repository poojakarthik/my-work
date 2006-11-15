<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h2>Console</h2>
		
		<p>
			Welcome, <strong><xsl:value-of select="/Response/Authentication/AuthenticatedContact/UserName" /></strong>.
			You are currently logged into your account.
		</p>
		
		<table border="0" cellpadding="10" cellspacing="0">
			<tr>
				<td><a href="contacts.php"><img src="img/contacts.png" border="0" /></a></td>
				<td>
					<a href="contacts.php">List Company Contacts</a><br />
					Let's you edit Contacts who are associated with your Company.
				</td>
			</tr>
			<tr>
				<td><a href="contact.php"><img src="img/contact.png" border="0" /></a></td>
				<td>
					<a href="contact.php">View My Profile</a><br />
					Allows you to edit your Person Profile.
				</td>
			</tr>
			<tr>
				<td><a href="accounts.php"><img src="img/accounts.png" border="0" /></a></td>
				<td>
					<a href="accounts.php">List All Accounts</a><br />
					List all the Accounts associated with your Company.
				</td>
			</tr>
			<tr>
				<td><a href="account.php"><img src="img/account.png" border="0" /></a></td>
				<td>
					<a href="account.php">View My Account</a><br />
					View details relating to your Primary Account.
				</td>
			</tr>
			<tr>
				<td><a href="logout.php"><img src="img/logout.png" border="0" /></a></td>
				<td>
					<a href="logout.php">Logout of Account</a><br />
					Logout of your TelcoBlue Internet Account.
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
