<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Lessee Changed</h1>
		<div class = "MsgNoticeWide">
			The Lessee for this Service has been changed. Details are listed below.
		</div>
		<!-- TODO Use get variables to check whether charges have actually been transferred-->
		<div class = "MsgNoticeWide">
			The Unbilled Charges for this Service have been transferred.
		</div>

		<div class="Seperator"></div>
		
		<div class="Wide-Form">
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<th>Option</th>
					<th>Old Details</th>
					<th>New Details</th>
				</tr>
				<tr>
					<th>Account</th>
					<td>
						<a target="_blank">
							<xsl:attribute name="href">
								<xsl:text>account_view.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Services/Old/Service/Account" />
							</xsl:attribute>
							View Account
						</a>
					</td>
					<td>
						<a target="_blank">
							<xsl:attribute name="href">
								<xsl:text>account_view.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Services/New/Service/Account" />
							</xsl:attribute>
							View Account
						</a>
					</td>
				</tr>
				<tr>
					<th>Service</th>
					<td>
						<a target="_blank">
							<xsl:attribute name="href">
								<xsl:text>service_view.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Services/Old/Service/Id" />
							</xsl:attribute>
							View Service
						</a>
					</td>
					<td>
						<a target="_blank">
							<xsl:attribute name="href">
								<xsl:text>service_view.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Services/New/Service/Id" />
							</xsl:attribute>
							View Service
						</a>
					</td>
				</tr>
				<tr>
					<th>Line Number</th>
					<td><xsl:value-of select="/Response/Services/Old/Service/FNN" /></td>
					<td><xsl:value-of select="/Response/Services/New/Service/FNN" /></td>
				</tr>
				<tr>
					<th>Start Date</th>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Services/Old/Service/CreatedOn/year">
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="/Response/Services/Old/Service/CreatedOn/year" />
									<xsl:with-param name="month"	select="/Response/Services/Old/Service/CreatedOn/month" />
									<xsl:with-param name="day"		select="/Response/Services/Old/Service/CreatedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Green">No Start Date</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Services/New/Service/CreatedOn/year">
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="/Response/Services/New/Service/CreatedOn/year" />
									<xsl:with-param name="month"	select="/Response/Services/New/Service/CreatedOn/month" />
									<xsl:with-param name="day"		select="/Response/Services/New/Service/CreatedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Red">No Start Date</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
				<tr>
					<th>Cease Date</th>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Services/Old/Service/ClosedOn/year">
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="/Response/Services/Old/Service/ClosedOn/year" />
									<xsl:with-param name="month"	select="/Response/Services/Old/Service/ClosedOn/month" />
									<xsl:with-param name="day"		select="/Response/Services/Old/Service/ClosedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Red">No Close Date</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Services/New/Service/ClosedOn/year">
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="/Response/Services/New/Service/ClosedOn/year" />
									<xsl:with-param name="month"	select="/Response/Services/New/Service/ClosedOn/month" />
									<xsl:with-param name="day"		select="/Response/Services/New/Service/ClosedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Green">No Close Date</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</table>
		</div>
	</xsl:template>
</xsl:stylesheet>
