<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!-- Confirmation Page following Update of Service Address Details -->
		
		<h1>Service Address Updated</h1>
		
		<div class = "MsgNoticeWide">
			Your Service Address Details have been successfully updated.
		</div>
		
		<div class = "Right">
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_address.php?Service=</xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
				<xsl:text>Return to Provisioning</xsl:text>
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
