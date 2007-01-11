<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Employee Listing</h1>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>User Name</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/Employees/Results/rangeSample/Employee">
				<tr>
					<xsl:attribute name="class">
						<xsl:choose>
							<xsl:when test="position() mod 2 = 1">
								<xsl:text>Odd</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>Even</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
					
					<td><xsl:value-of select="/Response/Employees/Results/rangeStart + position()" />.</td>
					<td><xsl:value-of select="./FirstName" /></td>
					<td><xsl:value-of select="./LastName" /></td>
					<td><xsl:value-of select="./UserName" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>employee_edit.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>Edit Employee</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/Employees/Results/collationLength = 0">
				<div class="MsgError">
					There are no Accounts with the Search Criteria that you Specified.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/Employees/Results/rangeSample/Employee) = 0">
				<div class="MsgNotice">
					There are no Records for the Range that you Searched for.
				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
