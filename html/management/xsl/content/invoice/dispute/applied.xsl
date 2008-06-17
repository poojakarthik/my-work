<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!--TODO!bash! Urgent! this needs a menu link to view account -->
		<!-- Confirmation page after disputing an Invoice -->
		<h1>Invoice Disputed</h1>
		
		<div class="MsgNoticeWide">
			The Invoice has been successfully disputed.
		</div>
	</xsl:template>
</xsl:stylesheet>
