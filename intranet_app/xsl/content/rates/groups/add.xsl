<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Rate Group</h1>
		
		<form method="POST" action="rates_group_add.php">
			<xsl:if test="/Response/RateGroup/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/RateGroup/Error = 'Blank'">
							Your Group Name and your ServiceType must not be Blank.
							Please choose a name for your Group and Try Again.
						</xsl:when>
						<xsl:when test="/Response/RateGroup/Error = 'Exists'">
							The Group Name that you entered already exists in the System.
							Please choose another Group Name.
						</xsl:when>
						<xsl:when test="/Response/RateGroup/Error = 'ServiceType'">
							The Service Type that you entered is Invalid. Please choose a
							valid Service Type and try again.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Group')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Name" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RateGroup/Name" />
									</xsl:attribute>
								</input>
							</td>
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
									<xsl:for-each select="/Response/RateGroup/NamedServiceTypes/ServiceType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="@selected='selected'">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
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
