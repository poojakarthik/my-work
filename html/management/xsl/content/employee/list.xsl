<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Employee Listing</h1>
		
		<h2 class="Contact"> Employee Details</h2>
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>User Name</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/Employees/Record">
				<xsl:sort select="./LastName" />
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
					
					<td><xsl:value-of select="position()" />.</td>
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
						</a>,
						<a>
							<xsl:attribute name="href">
								<xsl:text>employee_permissions.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>Edit Permissions</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/Employees/collationLength = 0">
				<div class="MsgErrorWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/Employees/Record) = 0">
				<div class="MsgNoticeWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="LinkAdd">
			<a href="employee_add.php">Add Employee</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
