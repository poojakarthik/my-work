<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>Provisioning Requests</h1>
		
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Date</th>
				<th>Carrier</th>
				<th>Type</th>
				<th>Probable Response</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/ProvisioningRequests/Results/rangeSample/ProvisioningRequest">
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
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./RequestDateTime/year" />
							<xsl:with-param name="month"	select="./RequestDateTime/month" />
							<xsl:with-param name="day"		select="./RequestDateTime/day" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
						</xsl:call-template>
					</td>
					<td>
						<xsl:value-of select="./Carrier/Name" />
					</td>
					<td>
						<xsl:value-of select="./ProvisioningRequestType/Name" />
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="./ProvisioningRequestResponse/Id = 300">
								<strong><span class="Blue">Unprocessed</span></strong>
							</xsl:when>
							<xsl:when test="./ProvisioningRequestResponse/Id = 301">
								<strong><span class="Blue">Pending</span></strong>
							</xsl:when>
							<xsl:when test="./ProvisioningRequestResponse/Id = 302">
								<strong><span class="Red">Failed</span></strong>
							</xsl:when>
							<xsl:when test="./ProvisioningRequestResponse/Id = 303">
								<strong><span class="Green">Processed</span></strong>
							</xsl:when>
							<xsl:when test="./ProvisioningRequestResponse/Id = 304">
								<strong><span class="Blue">Cancelled</span></strong>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Red">Unknown</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="./ProvisioningRequestResponse/Id = 300">
								<a>
									<xsl:attribute name="href">
										<xsl:text>provisioning_request_cancel.php?Id=</xsl:text>
										<xsl:value-of select="Id" />
									</xsl:attribute>
									<xsl:text>Cancel</xsl:text>
								</a>
							</xsl:when>
						</xsl:choose>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/ProvisioningRequests/Results/collationLength = 0">
				<div class="MsgNoticeModal">
					There are no Provisioning Requests associated with this Service.

				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
