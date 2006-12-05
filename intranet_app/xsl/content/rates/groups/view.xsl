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
					<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
				</tr>
				<tr>
					<td>Archive Status:</td>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/RateGroupDetails/RateGroup/Archived = 0">
								Currently Available 
									[<a>
										<xsl:attribute name="href">
											<xsl:text>rates_group_archive.php?Id=</xsl:text>
											<xsl:value-of select="/Response/RateGroupDetails/RateGroup/Id" />
										</xsl:attribute>
										<xsl:text>Archive Rate Group</xsl:text>
									</a>]
							</xsl:when>
							<xsl:otherwise>
								
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</table>
			<div class="Clear"></div>
		</div>
	</xsl:template>
</xsl:stylesheet>
