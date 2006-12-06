<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Rate Details</h1>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
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
						<th>Start Time:</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
		 						<xsl:with-param name="hour"	select="/Response/RateDetails/Rate/StartTime/hour" />
								<xsl:with-param name="minute"	select="/Response/RateDetails/Rate/StartTime/minute" />
								<xsl:with-param name="second"	select="/Response/RateDetails/Rate/StartTime/second" />
								<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th>Cease Time:</th>
						<td>
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
							Hours: <xsl:value-of select="floor (/Response/RateDetails/Rate/quarter-length div 4)" />
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							Minutes: <xsl:value-of select="floor (/Response/RateDetails/Rate/quarter-length mod 4)" />
						</td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<h2>Rate Groups using this Rate</h2>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Rate Group Name</th>
				<th>Record Type</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/RateDetails/RateGroups/RateGroup">
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
					<td><xsl:value-of select="position()" />.</td>
					<td><xsl:value-of select="./Name" /></td>
					<td><xsl:value-of select="./RecordType/Name" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>rates_group_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>View Rate Group Details</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
	</xsl:template>
</xsl:stylesheet>
