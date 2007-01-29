<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!-- Cancelling Provisioning did not work -->
		
		<h1>Provisioning Cancellation Failed</h1>
		
		<div class="MsgErrorWide">
			The Provisioning Request could not be cancelled

		</div>
		
		<div class="Right">
		Return to 
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_address.php?Service=</xsl:text>
					<xsl:value-of select="/Response/ProvisioningRequest/Service" />
				</xsl:attribute>
				<xsl:text>Provisioning</xsl:text>
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
