<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--Confirmation Page when Payment Method has been Changed -->
		
		<h1>Payment Method  Changed</h1>
		
		<div class="MsgNoticeWide">
			You have successfully changed the Payment Method for this Account.
		</div>
		
		<div class="Right">		
				<a>
					<xsl:attribute name="href">
						<xsl:text>account_view.php?Id=</xsl:text>
						<xsl:value-of select="/Response/Account/Id" />
					</xsl:attribute>
					<xsl:text>Return to  Account Details</xsl:text>
				</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
