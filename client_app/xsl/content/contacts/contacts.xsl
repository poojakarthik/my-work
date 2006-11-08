<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2>List of Account Group Contacts</h2>
		
		<p>
			Contained below is a list of all the people in your account group.
			Click on them for further information.
		</p>
		
		<table border="1" cellpadding="3" cellspacing="0">
			<tr>
				<th>User Name</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Permissions</th>
			</tr>
			<xsl:for-each select="/Response/Contacts/rangeSample/Contact">
				<tr>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>contact.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./UserName" />
						</a>
					</td>
					<td>
						<xsl:value-of select="./FirstName" />
					</td>
					<td>
						<xsl:value-of select="./LastName" />
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="./CustomerContact = 1">
								Account Group Contact
							</xsl:when>
							<xsl:otherwise>
								Account Contact
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
