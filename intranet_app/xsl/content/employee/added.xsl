<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Employed Successfully Added</h1>
		
		<p>
			The Employee you wish to add has successfully been created
			and saved to the database. Please note that this Employee
			may not be able to Login until you have up their Permissions.
		</p>
		
		<ul>
			<li>
				Continue to 
				<a>
					<xsl:attribute name="href">
						<xsl:text>employee_permissions.php?Id=</xsl:text>
						<xsl:value-of select="/Response/Employee/Id" />
					</xsl:attribute>
					<xsl:text>Setup Employee Permissions</xsl:text>
				</a>.
			</li>
			<li>
				Return to 
				<a href="employee_list.php">Employee Listing</a>.
			</li>
		</ul>
	</xsl:template>
</xsl:stylesheet>
