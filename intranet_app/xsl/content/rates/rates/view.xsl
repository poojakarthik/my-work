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
						<th class="JustifiedWidth">Rate Name:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/Name" /></td>
					</tr>
					<tr>
						<th>Rate Description:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					
					<tr>
						<th>Rate Charge:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RateDetails/Rate/PassThrough = 1">
									Re-billed at Cost
								</xsl:when>
								<xsl:otherwise>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/RateDetails/Rate/StdRatePerUnit" />
										<xsl:with-param name="Decimal" select="number('4')" />
			       					</xsl:call-template>
									per 
									<xsl:value-of select="/Response/RateDetails/Rate/StdUnits" />
									<xsl:text> </xsl:text>
									<xsl:value-of select="/Response/RateDetails/Rate/RecordDisplayType/Suffix" />
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<th>Flagfall:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RateDetails/Rate/StdFlagfall = 0">
									<strong><span class="Attention">No Flagfall</span></strong>
								</xsl:when>
								<xsl:otherwise>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/RateDetails/Rate/StdFlagfall" />
										<xsl:with-param name="Decimal" select="number('4')" />
			       					</xsl:call-template>
			       				</xsl:otherwise>
			       			</xsl:choose>
						</td>
					</tr>
					<tr>
						<th>Minimum Charge:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RateDetails/Rate/StdMinCharge = 0">
									<strong><span class="Attention">No Minimum Charge</span></strong>
								</xsl:when>
								<xsl:otherwise>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/RateDetails/Rate/StdMinCharge" />
										<xsl:with-param name="Decimal" select="number('4')" />
			       					</xsl:call-template>
			       				</xsl:otherwise>
			       			</xsl:choose>
						</td>
					</tr>
					<tr>
						<th>Availability:</th>
						<td>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Monday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										Mo</span></strong>
									</td>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Tuesday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										Tu</span></strong>
									</td>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Wednesday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										We</span></strong>
									</td>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Thursday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										Th</span></strong>
									</td>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Friday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										Fr</span></strong>
									</td>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Saturday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										Sa</span></strong>
									</td>
									<td>
										<strong><span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="/Response/RateDetails/Rate/Sunday = 1">Green</xsl:when>
												<xsl:otherwise>Red</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										Su</span></strong>
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
					
					
					<tr>
						<th>Service Type:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th>Record Type:</th>
						<td><xsl:value-of select="/Response/RateDetails/Rate/RecordType/Name" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
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
					</tr>
					<tr>
						<th>Duration:</th>
						<td>
							<xsl:value-of select="floor (/Response/RateDetails/Rate/quarter-length div 4)" /> hours,
							<xsl:value-of select="floor (/Response/RateDetails/Rate/quarter-length mod 4)" /> minutes
						</td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
	
	</xsl:template>
</xsl:stylesheet>
