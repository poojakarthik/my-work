<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<!--TODO!bash! [  DONE  ]		URGENT - Change Plan does not work -->
	
	<xsl:template name="Content">
		<!-- This page allows the user to change a Service's Plan -->
		<h1>Change Plan</h1>
		
		<!--Service Details -->
		<h2 class="Service">Service Details</h2>
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Account/Id" />
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('BusinessName')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Account/BusinessName" />
						</td>
					</tr>
					<!--Check for Trading Name-->
						<xsl:choose>
							<xsl:when test="/Response/Account/TradingName = ''">
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Account')" />
											<xsl:with-param name="field" select="string('TradingName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Service/Id" />
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
				</table>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<!-- Plan Details -->
		<h2 class="Plan">Plan Details</h2>
		<form method="post" action="service_plan.php">
			<div class="Wide-Form">
				<div class="Form-Content">	
					<input type="hidden" name="Service">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
						</xsl:attribute>
					</input>
					
					<table border="0" cellpadding="3" cellspacing="0">
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
										<strong><xsl:value-of select="/Response/Service/RatePlan/Name" /></strong>
									</xsl:when>
									<xsl:otherwise>
										<span class="Red"><strong>No Plan Selected</strong></span>
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
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
											<!-- TODO!bash! [  DONE  ]		current plan should be selected -->
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/Service/RatePlan/Id and ./Id = /Response/Service/RatePlan/Id">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
								<input type="button" value="View Plan Details &#0187;" class="input-submit" 
								title="View Plan Details" alt="View Rates and Charges for a Plan">
									<xsl:attribute name="onclick">
										<xsl:text>window.open (</xsl:text>
											<!-- TODO!bash! [  DONE  ]		URGENT make this open in its own browser-->
											<xsl:text>'rates_plan_summary.php?Id=' + document.getElementById ('RatePlan').options [document.getElementById ('RatePlan').options.selectedIndex].value</xsl:text>
										<xsl:text>)</xsl:text>
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" value="Change Plan &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
