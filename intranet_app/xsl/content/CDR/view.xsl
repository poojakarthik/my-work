<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>View CDR</h1>
		<div class="Seperator"></div>
		
		<h2 class="CDR">CDR Details</h2>
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" width="100%" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Id" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('FNN')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/FNN" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Carrier')" />
								<xsl:with-param name="field" select="string('CarrierName')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/Carriers/Carrier[./Id=/Response/CDR/Carrier]/Name" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Source')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/CDR/Source = ''">
									<strong><span class="Red">Undefined</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDR/Source" />
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Destination')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/CDR/Destination = ''">
									<strong><span class="Red">Undefined</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDR/Destination" />
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('StartDatetime')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"	select="/Response/CDR/StartDatetime/year" />
								<xsl:with-param name="month"	select="/Response/CDR/StartDatetime/month" />
								<xsl:with-param name="day"		select="/Response/CDR/StartDatetime/day" />
		 						<xsl:with-param name="hour"	select="/Response/CDR/StartDatetime/hour" />
								<xsl:with-param name="minute"	select="/Response/CDR/StartDatetime/minute" />
								<xsl:with-param name="second"	select="/Response/CDR/StartDatetime/second" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('EndDatetime')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"	select="/Response/CDR/EndDatetime/year" />
								<xsl:with-param name="month"	select="/Response/CDR/EndDatetime/month" />
								<xsl:with-param name="day"		select="/Response/CDR/EndDatetime/day" />
		 						<xsl:with-param name="hour"	select="/Response/CDR/EndDatetime/hour" />
								<xsl:with-param name="minute"	select="/Response/CDR/EndDatetime/minute" />
								<xsl:with-param name="second"	select="/Response/CDR/EndDatetime/second" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_view.php?Id=</xsl:text>
									<xsl:value-of select="/Response/CDR/Account" />
								</xsl:attribute>
								<xsl:text>Open Account</xsl:text>
							</a>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_view.php?Id=</xsl:text>
									<xsl:value-of select="/Response/CDR/Service" />
								</xsl:attribute>
								<xsl:text>Open Service</xsl:text>
							</a>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
