<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Employee  Added</h1>
		
		<div class = "MsgNoticeWide">
			The Employee has been successfully added.  Please note that they may not be able to log in until you have updated their permissions.
		</div>
		
		<div class = "Right">
				Continue to 
				<a>
					<xsl:attribute name="href">
						<xsl:text>employee_permissions.php?Id=</xsl:text>
						<xsl:value-of select="/Response/Employee/Id" />
					</xsl:attribute>
					<xsl:text>Setup Employee Permissions</xsl:text>.
				</a>.
		</div>
		<div class = "Right">
				Return to  <a href="employee_list.php">Employee Listing</a>.
		</div>
				
</xsl:template>
</xsl:stylesheet>
