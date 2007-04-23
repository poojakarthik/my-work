<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Bug List</h1>
		
		<h2 class="Bug"> Bug Details</h2>
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Created On</th>
				<th>Created By</th>
				<th>Status</th>
				<th>Page Name</th>
			</tr>
			<xsl:for-each select="/Response/Bugs/Record">
				<xsl:sort select="./CreatedOn" />
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
					<td><xsl:value-of select="./CreatedOn" /></td>
					<td><xsl:value-of select="./CreatedBy" /></td>
					<td><xsl:value-of select="./Status" /></td>
					<td>
						<!-- <xsl:value-of select="./PageName" /> -->
						<a>
							<xsl:attribute name="href">
								<xsl:text>bug_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./PageName" />
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/Bugs/collationLength = 0">
				<div class="MsgErrorWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/Bugs/Record) = 0">
				<div class="MsgNoticeWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<!-- <div class="LinkAdd">
			<a href="employee_add.php">Add Employee</a>
		</div> -->
	</xsl:template>
</xsl:stylesheet>
