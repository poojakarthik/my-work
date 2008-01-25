<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Contacts">List of All Company Contacts</h2>
		
		<p>
			Contained below is a list of all the people in your account group.
			Click on a Contact for more information.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="listing">
			<tr class="first">
				<th>User Name</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Permissions</th>
			</tr>
			<xsl:for-each select="/Response/Contacts/rangeSample/Contact">
				<tr>
					<xsl:attribute name="class">
						<xsl:choose>
							<xsl:when test="position() mod 2 = 1">
								<xsl:text>odd</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>even</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> Clickable</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="onclick">
						<xsl:text>window.location='contact.php?Id=</xsl:text>
						<xsl:value-of select="./Id" />
						<xsl:text>'</xsl:text>
					</xsl:attribute>
					<td>
						<xsl:value-of select="./UserName"  />
					</td>
					<td>
						<xsl:value-of select="./FirstName"  />
					</td>
					<td>
						<xsl:value-of select="./LastName"  />
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
