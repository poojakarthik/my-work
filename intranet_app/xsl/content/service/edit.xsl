<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:template name="Content">
		<h1>Edit Service</h1>
		
		<form method="POST" action="service_edit.php">
			<h2 class="Service">Service Details</h2>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
						</xsl:attribute>
					</input>
					<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
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
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Service/ServiceTypes/ServiceType[@selected='selected']/Name" />
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
								<input type="text" name="FNN" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/FNN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2 class="Archive">Archive Status</h2>
			<!-- TODO!!!! - URGENT - allow unarchive of service -->  	 
			<!-- if an active service exists with this FNN : show a link to change of lessee --> 	 
			<!-- if a more recent archived service exists with this FNN : add a new service --> 	 
			<!-- otherwise, just re-activate the service --> 	 
			<!-- dont forget the service address details for provisioning -->
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<xsl:choose>
						<xsl:when test="./ClosedOn/year">
							<strong><span class="Red">
								This service closes on:
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="./ClosedOn/year" />
									<xsl:with-param name="month"	select="./ClosedOn/month" />
									<xsl:with-param name="day"		select="./ClosedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</span></strong>
						</xsl:when>
						<xsl:when test="./CreatedOn/year and ./Available = 0">
							<strong><span class="Blue">
								This service opens on:
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="./CreatedOn/year" />
									<xsl:with-param name="month"	select="./CreatedOn/month" />
									<xsl:with-param name="day"		select="./CreatedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</span></strong>
						</xsl:when>
						<xsl:otherwise>
							<strong><span class="Green">This service is currently available</span></strong>
						</xsl:otherwise>
					</xsl:choose>
					
					<div class="Seperator"></div>
					
					<xsl:choose>
						<xsl:when test="/Response/Service/Archived = 1">
							<p>Services can not be unarchived. Instead - add a new service with the same number.</p>
						</xsl:when>
						<xsl:otherwise>
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<td><input type="checkbox" name="Archived" value="1" id="Archive:TRUE" /></td>
									<td>
										<label for="Archive:TRUE">
											Yes, please <strong><span class="Red">Archive this Service</span></strong>.
										</label>
									</td>
								</tr>
							</table>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
		</form>
	</xsl:template>
</xsl:stylesheet>
