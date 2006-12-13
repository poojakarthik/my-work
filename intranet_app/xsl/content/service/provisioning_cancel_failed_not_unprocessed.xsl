<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:template name="Content">
		<h1>Provisioning Cancellation Failed</h1>
		
		<p>
			Your request to cancel a Provisioning Request has been failed because it is not
			unprocessed. Only unprocessed provisioning requests can be cancelled.
		</p>
		
		<p>
			You can return to 
			<a>
				<xsl:attribute name="href">
					<xsl:text>provisioning_requests.php?Service=</xsl:text>
					<xsl:value-of select="/Response/ProvisioningRequest/Service" />
				</xsl:attribute>
				<xsl:text>provisioning information about this service</xsl:text>
			</a>.
		</p>
	</xsl:template>
</xsl:stylesheet>
