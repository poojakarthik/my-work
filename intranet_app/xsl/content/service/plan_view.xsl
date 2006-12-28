<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Service Plan Details</h1>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<h2>Current Allocation</h2>
				<p>
					The service you are currently viewing is connected to the following plan:<br />
					<strong><xsl:value-of select="/Response/Service/RatePlan/Name" /></strong>
				</p>
				
				<div class="Seperator"></div>
				
				<ul>
					<li>
						<a>
							<xsl:attribute name="href">
								<xsl:text>javascript:nohref()</xsl:text>
							</xsl:attribute>
							<xsl:attribute name="onclick">
								<xsl:text>return openPopup(</xsl:text>
									<xsl:text>'rates_plan_view.php?Id=</xsl:text><xsl:value-of select="/Response/Service/RatePlan/Id" /><xsl:text>'</xsl:text>
								<xsl:text>)</xsl:text>
							</xsl:attribute>
							View Plan Details
						</a>
					</li>
				</ul>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<h2>Change Plan</h2>
				<p>
					If you would like to change the Plan the person is currently on, 
					select a plan from the list below (and fill in the extra options
					if you want to) and click the submit button.
				</p>
				
				<div class="Seperator"></div>
				
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
								<input type="button" value="View Plan Details &#0187;" class="input-submit-disabled">
									<xsl:attribute name="onclick">
										<xsl:text>return openPopup(</xsl:text>
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
