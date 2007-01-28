<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		
		<!-- Confirmation Page following a Cancellation of a Provisioning Request-->
		<h1>Provisioning Request Cancelled</h1>
		
		<div class = "MsgNoticeWide">
			Provisioning Request has been successfully Cancelled.
		</div>
		<div class = "Right">
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_address.php?Service=</xsl:text>
					<xsl:value-of select="/Response/ProvisioningRequest/Service" />
				</xsl:attribute>
				<xsl:text>Return to Provisioning</xsl:text>
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
