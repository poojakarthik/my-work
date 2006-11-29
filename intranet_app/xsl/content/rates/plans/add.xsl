<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Rate Plan</h1>
		
		<form method="POST" action="rates_plan_add.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td><input type="text" name="Name" class="input-string" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceType">
									<xsl:for-each select="/Response/NamedServiceTypes/ServiceType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
					
					<div class="Seperator"></div>
					
					<input type="submit" value="Continue &#0187;" class="input-submit" />
					
					<div class="Clear"></div>
				</div>
					
				<div class="Clear"></div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
