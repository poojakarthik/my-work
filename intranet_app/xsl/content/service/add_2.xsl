<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Create Service</h1>
		<div class="Seperator"></div>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Mismatch'">
						You must correctly confirm your Line Number.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="service_add.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="ServiceType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/ServiceTypes/ServiceType[@selected='selected']/Id" />
				</xsl:attribute>
			</input>
			
			<h2 class="Account">Account Details</h2>
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
			
			<h2 class="Service">Service Details</h2>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/ui-values/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FNN-1" id="FNN-1" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/FNN-1" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('RepeatFNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FNN-2" id="FNN-2" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/FNN-2" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<xsl:if test="/Response/ui-values/ServiceTypes/ServiceType[@selected='selected']/Id = 102">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('Indial100')" />
									</xsl:call-template>
								</th>
								<td>
									<table border="0" cellpadding="3" cellspacing="0">
										<tr>
											<td><input type="checkbox" name="Indial100" id="Indial100:TRUE" value="1" /></td>
											<td>
												<label for="Indial100:TRUE">
													this service is the GDN for a 100 number Indial Range
												</label>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="Seperator"></div>
								</td>
							</tr>
						</xsl:if>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('SelectPlan')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="RatePlan" id="RatePlan">
									<xsl:for-each select="/Response/RatePlans/Results/rangeSample/RatePlan">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
								<input type="button" value="View Plan Details &#0187;" class="input-submit" 
								title="Viewing Plan Details" alt="Information about Charges incurred on this Plan"
								onclick="return ModalExternal (this, 'rates_plan_view.php?Id=' + document.getElementById ('RatePlan').options [document.getElementById ('RatePlan').options.selectedIndex].value)" />
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
