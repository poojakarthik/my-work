<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>Provisioning History</h1>
		
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>I/O</th>
				<th>Date</th>
				<th>Carrier</th>
				<th>Type</th>
				<th>Description</th>
			</tr>
			<xsl:for-each select="/Response/ProvisioningLog/Results/rangeSample/ProvisioningRecord">
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
					<td>
						<xsl:choose>
							<xsl:when test="./Direction = 1">
								<img src="img/template/icon_movedown.png" title="Details were Received from the Carrier" 
								alt="Details were Received from the Carrier" />
							</xsl:when>
							<xsl:otherwise>
								<img src="img/template/icon_moveup.png" title="Details were Sent to the Carrier" 
								alt="Details were Sent to the Carrier" />
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./Date/year" />
							<xsl:with-param name="month"	select="./Date/month" />
							<xsl:with-param name="day"		select="./Date/day" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
						</xsl:call-template>
					</td>
					<td>
						<xsl:value-of select="./Carrier/Name" />
					</td>
					<td>
						<xsl:value-of select="./ProvisioningResponseType/Name" />
					</td>
					<td>
						<xsl:value-of select="./Description" />
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/ProvisioningLog/Results/collationLength = 0">
				<div class="MsgNoticeModal" >
					There are no Provisioning Requests associated with this Service.

				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
