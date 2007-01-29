<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!-- Confirmation page after resolving an Invoice Dispute -->
		<h1>Invoice Dispute Resolved</h1>
		
		<div class="MsgNoticeWide">
			The Invoice Dispute has been successfully resolved.
		</div>
		
		<div class = "Right">
		Return to 
			<a>
				<xsl:attribute name="href">
					<xsl:text>invoice_view.php?Invoice=</xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
				<xsl:text>Invoice</xsl:text>
			</a>
		</div>
		
	</xsl:template>
</xsl:stylesheet>
