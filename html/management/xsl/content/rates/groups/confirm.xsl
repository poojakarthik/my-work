<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Rate Group Added</h1>
		
		<div class = "MsgNoticeWide">
			The Rate Group has been successfully added.

		</div>
		
		
		<div class  = "Right">
			Continue to <a href="rates_group_list.php">List Rate Groups</a>
		</div>
		<div class = "Right">
			or <a href="rates_group_add.php">Add Another Rate Group</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
