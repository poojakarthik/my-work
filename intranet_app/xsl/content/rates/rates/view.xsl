<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Rate Details</h1>
		
		
		<div class="FormPopup">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th>Rate Id:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/Id" /></td>
					</tr>
					<tr>
						<th>Rate Name:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/Name" /></td>
					</tr>
					<tr>
						<th>Rate Description:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/Description" /></td>
					</tr>
					<tr>
						<th>Service Type:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th>Record Type:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/RecordType/Name" /></td>
					</tr>
					<tr>
						<th>Archive Status:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RateDetails/Rate/Archived = 0">
									<strong><span class="Green">Currently Available</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<strong><span class="Red">Currently Archived</span></strong>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<th>Avalible Time:</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
		 						<xsl:with-param name="hour"	select="/Response/RateDetails/Rate/StartTime/hour" />
								<xsl:with-param name="minute"	select="/Response/RateDetails/Rate/StartTime/minute" />
								<xsl:with-param name="second"	select="/Response/RateDetails/Rate/StartTime/second" />
								<xsl:with-param name="format"	select="'%I:%M:%S %P'"/> 
							</xsl:call-template>
								to 
							<xsl:call-template name="dt:format-date-time">
		 						<xsl:with-param name="hour"	select="/Response/RateDetails/Rate/EndTime/hour" />
								<xsl:with-param name="minute"	select="/Response/RateDetails/Rate/EndTime/minute" />
								<xsl:with-param name="second"	select="/Response/RateDetails/Rate/EndTime/second" />
								<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
							</xsl:call-template>
							
						</td>
							<td>
							
						</td>
					</tr>

					<tr>
						<th>Duration:</th>
						<td>
							<xsl:value-of select="floor (/Response/RateDetails/Rate/quarter-length div 4)" /> hours,
							<xsl:value-of select="floor (/Response/RateDetails/Rate/quarter-length mod 4)" /> minutes
						</td>
						<td>
							
						</td>
					</tr>
					<tr>
						<th></th>
						
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
	
	</xsl:template>
</xsl:stylesheet>
