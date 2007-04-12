<?xml version="1.0" encoding="utf-8"?>
<!-- TODO!bash! [  DONE  ]		No Plan Assigned when I add a service -->
<!-- TODO!bash! [  DONE  ]		It lets me add a service with the same no. over and over again -->
<!-- TODO!bash! [  DONE  ]		URGENT! it lets me add any damn thing i want as the FNN. should allow a valid FNN or blank... NOTHING ELSE  - make sure you test this properly!!!-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!--TODO!bash! [  DONE  ]		URGENT! this page needs a menu!-->
		<!-- This page adds a service to an account (Part 2/2)-->
		<h1>Add Service</h1>
		
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
			
			<!--Account Details-->
			<h2 class="Account">Account Details</h2>
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
							<td><xsl:value-of select="/Response/Account/Id" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
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
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			<!-- TODO!bash! [  DONE  ]		if i hit continue without entering any details i get taken back to the service view page ????? -->
			<h2 class="Service">Service Details</h2>
			<!-- TODO!bash! DO NOT UNDER ANY CIRCUMSTANCES CHANGE ANY ERROR MESSAGES IN THIS SYSTEM!!!! IF YOU HAVE TO ADD ERRORS COPY THEM EXACTLY FROM EXISTING ERRORS!!! -->
			<!-- TODO!bash! IF AN APPROPRIATE EXISTING ERROR MESSAGE DOES NOT EXIST, ADD A BLANK ERROR WITH AN URGENT TODO FOR FLAME  -->
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Mismatch'">
							Your Service #s did not match. Please try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'FNN ServiceType'">
						Please select a valid Service #.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Unarchived FNN Exists'">
							The Service # you entered already exists.  Please enter a unique Service #.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Rate Plan Invalid'">
							Please enter a valid Rate Plan.

						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<!-- Service Details-->
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceType">
									<xsl:for-each select="/Response/ui-values/ServiceTypes/ServiceType">
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
						<tr>
							<td><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/ui-values/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<!-- TODO!bash! [  DONE  ]		mark this as mandatory -->
								<input type="text" name="FNN-1" id="FNN-1" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/FNN-1" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('RepeatFNN')" />
								</xsl:call-template>
							</th>
							<td>
								<!-- TODO!bash! [  DONE  ]		mark this as mandatory -->
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
								<td></td>
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
													This service is the Primary Number for a 100 number Indial Range
												</label>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="MicroSeperator"></div>
								</td>
							</tr>
						</xsl:if>
						
						<!-- Cost Centre -->
						<xsl:if test="/Response/CostCentres/Results/collationLength != 0">
							<tr>
								<td></td>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('CostCentre')" />
									</xsl:call-template>
								</th>
								<td>
									<select name="CostCentre">
										<option value=""></option>
										<xsl:for-each select="/Response/CostCentres/Results/rangeSample/CostCentre">
											<xsl:sort select="./Name" />
											
											<option>
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="./Id" />
												</xsl:attribute>
												<xsl:if test="./Id = /Response/Service/CostCentre">
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
						</xsl:if>
						
						<!-- Rate Plan -->
						<tr>
							<td><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('SelectPlan')" />
								</xsl:call-template>
							</th>
							<td >
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
								<!-- TODO!bash! [  DONE  ]		this needs to open the new plan summary page, not this crap one -->
								<input type="button" value="View Plan Details &#0187;" class="input-submit" 
								title="Viewing Plan Details" alt="Information about Charges incurred on this Plan"
								onclick="window.open ('rates_plan_summary.php?Id=' + document.getElementById ('RatePlan').options [document.getElementById ('RatePlan').options.selectedIndex].value, '', '')" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Add Service &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
