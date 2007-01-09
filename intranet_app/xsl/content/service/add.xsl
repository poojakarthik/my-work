<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Create Service</h1>
		<div class="Seperator"></div>
		
		<form method="POST" action="service_add.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<h2>Account Details</h2>
			<div class="Seperator"></div>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/TradingName" /></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<h2>Service Details</h2>
			<div class="Seperator"></div>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FNN" class="input-string">
									<xsl:attribute name="value">
										<xsl:value-of select="/Response/Service/FNN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceType">
									<xsl:for-each select="/Response/Service/ServiceTypes/ServiceType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="./@selected='selected'">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:text></xsl:text>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Continue &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
