<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!-- Confirmation Page following Update of Mobile Details -->
		
		<h1>Mobile Details Updated</h1>
		
		<div class = "MsgNoticeWide">
			Your Mobile Details have been successfully updated.
		</div>
		
		<div class = "Right">
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_mobile_details.php?Service=</xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
				<xsl:text>Return to Mobile Details</xsl:text>
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
