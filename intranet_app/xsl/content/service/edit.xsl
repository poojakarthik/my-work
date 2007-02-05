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
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="service_edit.php">
			<!--Service Details -->
			<h2 class="Service">Service Details</h2>
			<div class="Wide-Form">
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
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			
			<!-- Archive Status -->
			<h2 class="Archive">Archive Status</h2>
			<div class="Wide-Form">
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
