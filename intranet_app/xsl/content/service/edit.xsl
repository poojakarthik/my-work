<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:template name="Content">
		<h1>Edit Service</h1>
		
		<form method="POST" action="service_edit.php">
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
						<tr>
							<th class="JustifiedWidth"></th>
							<td>
								<strong><span class="Attention">Attention</span> :</strong>
								The line number you enter above must of the same Service Type
								listed above.
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2>Archive Status</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/Service/Archived = 0">
							This Service is <strong><span class="Green">Currently Available</span></strong>.
						</xsl:when>
						<xsl:otherwise>
							This Service is <strong><span class="Red">Currently Archived</span></strong>.
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="/Response/Service/Archived = 0">
							If you would like to make this Service Archived, please click the button below:
							<div class="Seperator"></div>
					
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<td><input type="checkbox" name="Archived" value="1" id="Archive:TRUE" /></td>
									<td>
										<label for="Archive:TRUE">
											Make this Service <strong><span class="Red">Archived</span></strong> and unavailable
										</label>
									</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
									</td>
								</tr>
							</table>
						</xsl:when>
						<xsl:otherwise>
							<p>Services can not be unarchived. Instead - add a new service with the same number.</p>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
