<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
	
		<!-- Page for viewing details of plans -->
		<h1>View Plan Details</h1>
		
		<!-- Plan Details -->
		<h2 class="Plan">Plan Details</h2>
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th>Rate Plan Name:</th>
						<td><xsl:value-of select="/Response/RatePlan/Name" /></td>
					</tr>
					<tr>
						<th>Rate Plan Description:</th>
						<td><xsl:value-of select="/Response/RatePlan/Description" /></td>
					</tr>
					<tr>
						<th>Service Type:</th>
						<td><xsl:value-of select="/Response/RatePlan/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th>Archive Status:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RatePlan/Archived = 0">
									<strong><span class="Green">Currently Available</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<strong><span class="Red">Currently Archived</span></strong>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<!-- TODO!bash! Shared? MinMontly? ChargeCap? UsageCap?  where are they? they need to be shown here-->
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<!-- Charges -->
		<h2 class="Invoice">Charges</h2>
		
		<table border="0" width="100%" cellpadding="3" cellspacing="0" style="font-family: monospace; font-size: 9pt;" class="Listing">
			<xsl:for-each select="/Response/RecordTypes/Results/rangeSample/RecordType">
				<xsl:variable name="RecordType" select="." />
				<xsl:variable name="RateGroup" select="/Response/RateGroups/RateGroup[RecordType=$RecordType]" />
			
				<xsl:if test="$RateGroup">
					<tr class="First">
						<th colspan="3">
							<xsl:value-of select="$RecordType/Name" />
							<!-- TODO!bash! link this to view rate group details -->
							(<xsl:value-of select="$RateGroup/Name" />)
						</th>
					</tr>
					<xsl:for-each select="/Response/RateGroupRates/RateGroupRate[RateGroup=$RateGroup/Id]/Rates/Rate">
						<xsl:variable name="RateId" select="." />
						<xsl:variable name="Rate" select="/Response/Rates/Rate[Id=$RateId]" />
						
						<tr>
							<xsl:attribute name="class">
								<xsl:choose>
									<xsl:when test="position() mod 2 = 1">
										<xsl:text>Odd</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>Even</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							
							<td>
								<!-- TODO!bash! link this to view rate details -->
								<xsl:value-of select="$Rate/Description" />	
							</td>
							<td>
								<table border="0" cellpadding="3" cellspacing="0">
									<tr>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Monday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Mo</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Tuesday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Tu</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Wednesday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											We</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Thursday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Th</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Friday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Fr</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Saturday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Sa</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Sunday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Su</span></strong>
										</td>
									</tr>
								</table>
							</td>
							<td>
								<xsl:call-template name="dt:format-date-time">
			 						<xsl:with-param name="hour"		select="$Rate/StartTime/hour" />
									<xsl:with-param name="minute"	select="$Rate/StartTime/minute" />
									<xsl:with-param name="second"	select="$Rate/StartTime/second" />
									<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
								</xsl:call-template>
								to
								<xsl:call-template name="dt:format-date-time">
			 						<xsl:with-param name="hour"		select="$Rate/EndTime/hour" />
									<xsl:with-param name="minute"	select="$Rate/EndTime/minute" />
									<xsl:with-param name="second"	select="$Rate/EndTime/second" />
									<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
								</xsl:call-template>
							</td>
						</tr>
					</xsl:for-each>
					<tr>
						<td colspan="4" align="right">
							<a>
								<xsl:attribute name="href">
									<xsl:text>rates_group_details.php?Id=</xsl:text>
									<xsl:value-of select="$RateGroup/Id" />
								</xsl:attribute>
								<xsl:text>More Details</xsl:text>
							</a>
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
				</xsl:if>
			</xsl:for-each>
			
			<!-- TODO!bash! add in a section for Recurring Charges -->
		</table>
	</xsl:template>
</xsl:stylesheet>
