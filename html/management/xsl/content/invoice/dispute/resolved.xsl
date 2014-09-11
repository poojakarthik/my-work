<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!--TODO!bash! This page needs a menu option going to Account!!-->
		<!-- Confirmation page after resolving an Invoice Dispute -->
		<h1>Invoice Dispute Resolved</h1>
		
		<div class="MsgNoticeWide">
			The Invoice Dispute has been successfully resolved.
		</div>
	</xsl:template>
</xsl:stylesheet>
