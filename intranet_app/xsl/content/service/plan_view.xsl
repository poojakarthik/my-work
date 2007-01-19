<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Service Plan Details</h1>
		
		<h2 class="Service">Service Details</h2>
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Service/Id" />
							[<a>
								<xsl:attribute name="href">
									<xsl:text>service_view.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:text>View Service</xsl:text>
							</a>]
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('FNN')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/Service/FNN" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('Plan')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/Service/RatePlan/Name">
									<p>
										The service you are currently viewing is connected to the following plan:<br />
										<strong><xsl:value-of select="/Response/Service/RatePlan/Name" /></strong>
									</p>
									
									<div class="Seperator"></div>
									
									<ul>
										<li>
											<a href="#" title="Viewing Plan Details" alt="Information about Charges incurred on this Plan">
												<xsl:attribute name="onclick">
													<xsl:text>return ModalExternal (this, </xsl:text>
														<xsl:text>'rates_plan_view.php?Id=</xsl:text><xsl:value-of select="/Response/Service/RatePlan/Id" /><xsl:text>'</xsl:text>
													<xsl:text>)</xsl:text>
												</xsl:attribute>
												View Plan Details
											</a>
										</li>
									</ul>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>This service is currently not on a plan.</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</table>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<h2 class="Plan">Select Plan</h2>
		<div class="Filter-Form">
			<div class="Filter-Form-Content">	
				<form method="post" action="service_plan.php">
					<input type="hidden" name="Service">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
						</xsl:attribute>
					</input>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('Name')" />
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
								title="Viewing Plan Details" alt="Information about Charges incurred on this Plan">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, </xsl:text>
											<xsl:text>'rates_plan_view.php?Id=' + document.getElementById ('RatePlan').options [document.getElementById ('RatePlan').options.selectedIndex].value</xsl:text>
										<xsl:text>)</xsl:text>
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Seperator"></div>
					
					<input type="submit" value="Assign Plan &#0187;" class="input-submit" />
				</form>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
