<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Employee Permissions Changed</h1>
		
		<div class = "MsgNoticeWide">
			The Permissions for the Employee have been successfully changed.

		</div>
		
		<div class = "Right">
				Return to <a href="employee_list.php">Employee Listing</a>.
		</div>
	</xsl:template>
</xsl:stylesheet>
