<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!-- ADMIN ONLY -->
		<!-- Page for adding a Rate Plan -->
		
		<h1>Add Rate Plan</h1>
		
		<form method="POST" action="rates_plan_add.php">
			<xsl:if test="/Response/RatePlan/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/RatePlan/Error = 'Blank'">
							Please enter a Rate Plan Name.
						</xsl:when>
						<xsl:when test="/Response/RatePlan/Error = 'Exists'">
							The Rate Plan Name you entered already exists.  Please enter a unique Rate Plan Name.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<!-- Plan Details -->
			<h2 class = "Plan"> Plan Details </h2>
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Name" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RatePlan/Name" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceType">
									<xsl:for-each select="/Response/RatePlan/ServiceTypes/ServiceType">
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
				</div>
					
				<div class="Clear"></div>
			</div>
			<div class="SmallSeperator"></div>
			<div class = "Right">
					<input type="submit" value="Continue &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
