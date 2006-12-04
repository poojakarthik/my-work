<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>Rate Group Details</h1>
		
		<div class="Filter-Form-Content">
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td>Rate Group Id:</td>
					<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/Id" /></td>
				</tr>
				<tr>
					<td>Rate Group Name:</td>
					<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/Name" /></td>
				</tr>
				<tr>
					<td>Rate Group Description:</td>
					<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/Description" /></td>
				</tr>
				<tr>
					<td>Service Type:</td>
					<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/NamedServiceTypes/ServiceType[@selected='selected']/Name" /></td>
				</tr>
			</table>
			<div class="Clear"></div>
		</div>
	</xsl:template>
</xsl:stylesheet>
