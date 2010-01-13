<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--This page is for editing Service Details -->
		
		<h1>Edit Service Details</h1>
		
		
		<!--TODO!bash! [  DONE  ]		This page has a popup error instead of using these.  it needs to use these errors!!!-->
		<!--DO NOT CHANGE THE WORDING OF ANY ERRORS-->
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Mismatch'">
						Please correctly confirm your Service #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'FNN ServiceType'">
						Please enter a valid Service #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Unarchive Fail'">
						Service could not be activated.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="service_edit.php">
			<!--Service Details -->
			<h2 class="Service">Service Details</h2>
			<div class="Narrow-Form">
				<div class="Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
						</xsl:attribute>
					</input>
					<table border="0" cellpadding="3" cellspacing="0">
				
						<tr>
							<td class = "Required"></td>
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
							<td class = "Required"></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Service/ServiceTypes/ServiceType[@selected='selected']/Name" />
							</td>
						</tr>
						<!-- TODO!bash! [  DONE  ]		Urgent! Verify Service # properly!! -->
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FNN[1]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/FNN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('RepeatFNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FNN[2]" class="input-string" />
							</td>
						</tr>
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
						<xsl:if test="/Response/Service/Indial100 = 1">
							<tr>
								<td class = "Required"></td>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('ELB')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="checkbox" name="ELB">
										<xsl:choose>
											<xsl:when test="/Response/Service/ELB = 1">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:when>
											<xsl:otherwise>
												<xsl:text></xsl:text>
											</xsl:otherwise>
										</xsl:choose>
									</input>
								</td>
							</tr>
						</xsl:if>
					</table>
				</div>
			</div>
			

			<xsl:if test="/Response/Service/ServiceType = 101">			
				
				<div class="Seperator"></div>
				<h2 class="Service">Mobile Details</h2>
				
				
							
				<div class="Narrow-Form">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Mobile')" />
									<xsl:with-param name="field" select="string('SimPUK')" />
								</xsl:call-template>
							</th>
						<td>
							<input type="text" name="SimPUK" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/SimPUK" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Mobile')" />
								<xsl:with-param name="field" select="string('SimESN')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="SimESN" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/SimESN" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Mobile')" />
								<xsl:with-param name="field" select="string('SimState')" />
							</xsl:call-template>
						</th>
						<!-- TODO!flame! (originally Bash) You need to base state and date of birth on contact details-->
						<!-- TODO!flame! 
							Recommendation: Because this is the information stored on the service's server (eg. UNITEL),
							I dont' know if this is since a good idea.
						-->
						<td>
							<select name="SimState">
								<xsl:for-each select="/Response/ServiceStateTypes/ServiceStateType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="./Id = /Response/ui-values/SimState">
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
					<!--TODO!bash! Urgent - do not show dates which allow the person to be <18-->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Mobile')" />
								<xsl:with-param name="field" select="string('DOB')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="DOB">
								<xsl:with-param name="Name-Day"			select="string('DOB[day]')" />
								<xsl:with-param name="Name-Month"		select="string('DOB[month]')" />
								<xsl:with-param name="Name-Year"		select="string('DOB[year]')" />
								<xsl:with-param name="Selected-Day"		select="/Response/ui-values/DOB-day" />
								<xsl:with-param name="Selected-Month"	select="/Response/ui-values/DOB-month" />
								<xsl:with-param name="Selected-Year"	select="/Response/ui-values/DOB-year" />
								<xsl:with-param name="Now"				select="/Response/Now" />
								<xsl:with-param name="Minimum-Age"		select="18" />
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Mobile')" />
								<xsl:with-param name="field" select="string('Comments')" />
							</xsl:call-template>
						</th>
						<td>
							<textarea name="Comments" class="input-summary">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/ui-values/Comments" />
							</textarea>
						</td>
					</tr>
				</table>
			</div>
		</xsl:if>
		<xsl:if test="Response/Service/ServiceType = 103">
		<div class="Seperator"></div>
			<h2 class="Service">Inbound Details</h2>
			<div class="Narrow-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Inbound')" />
								<xsl:with-param name="field" select="string('AnswerPoint')" />
							</xsl:call-template>
						</th>
					<td>
						<input type="text" name="AnswerPoint" class="input-string">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/ui-values/AnswerPoint" />
							</xsl:attribute>
						</input>
					</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Inbound')" />
								<xsl:with-param name="field" select="string('Config')" />
							</xsl:call-template>
						</th>
					<td>
						<input type="text-area" name="Config" class="input-string">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/ui-values/Config" />
							</xsl:attribute>
						</input>
					</td>
					</tr>
				</table>
			</div>	
		</xsl:if>		
		
		<div class="SmallSeperator"></div>
						
						
			<div class="Seperator"></div>
			
			
			<!-- Archive Status -->
			<h2 class="Archive">Archive Status</h2>
			<div class="Narrow-Form">
				<div class="Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/Service/ClosedOn/year">
							<strong><span class="Red">
								This service closes on:
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="/Response/Service/ClosedOn/year" />
									<xsl:with-param name="month"	select="/Response/Service/ClosedOn/month" />
									<xsl:with-param name="day"		select="/Response/Service/ClosedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</span></strong>
						</xsl:when>
						<xsl:when test="/Response/Service/CreatedOn/year and /Response/Service/Available = 0">
							<strong><span class="Blue">
								This service opens on:
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="/Response/Service/CreatedOn/year" />
									<xsl:with-param name="month"	select="/Response/Service/CreatedOn/month" />
									<xsl:with-param name="day"		select="/Response/Service/CreatedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</span></strong>
						</xsl:when>
						<xsl:otherwise>
							<strong><span class="Green">This service is currently available</span></strong>
						</xsl:otherwise>
					</xsl:choose>
					
					<div class="MicroSeperator"></div>
					
					<xsl:choose>
						<xsl:when test="not(/Response/Service/ClosedOn/year)">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td><input type="checkbox" name="Archived" value="1" id="Archive:TRUE" /></td>
									<td>
										<label for="Archive:TRUE">
											<strong><span class="Red">Archive</span></strong> this Service.
										</label>
									</td>
								</tr>
							</table>
						</xsl:when>
						<xsl:otherwise>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td><input type="checkbox" name="Archived" value="0" id="Archive:FALSE" /></td>
									<td>
										<label for="Archive:FALSE">
											<strong><span class="Green">Activate</span></strong> this Service.
										</label>
									</td>
								</tr>
							</table>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class="Right">
				<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
