<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Data Reports</h1>
		
		<h2 class="Report">Report Listing</h2>
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Report Name</th>
				<th width="30">Type</th>
				<th>Report Summary</th>
			</tr>
			<xsl:for-each select="/Response/DataReports/Results/rangeSample/DataReport">
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
					<td><xsl:value-of select="/Response/DataReports/Results/rangeStart + position()" />.</td>
					<td>
						<a>
							<xsl:choose>
								<xsl:when test="(/Response/Authentication/AuthenticatedEmployee/Email = '') and (./RenderMode = 1)">
									<xsl:attribute name="href">
										<!-- TODO: Make this prettier -->
										<xsl:text>javascript:alert("You must set your Employee Email Address before requesting Email Reports")</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="class">
										<xsl:text>Disabled</xsl:text>
									</xsl:attribute>
								</xsl:when>
								<xsl:otherwise>
									<xsl:attribute name="href">
										<xsl:text>datareport_run.php?Id=</xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:value-of select="./Name" />
						</a>
					</td>
					<td align="center" valign="middle">
						<xsl:choose>
							<xsl:when test="./RenderMode = 1">
								<img src="img/template/report_email.png" alt="Email" title="Email" />
							</xsl:when>
							<xsl:otherwise>
								<img src="img/template/report_instant.png" alt="Screen" title="Screen" />
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td><xsl:value-of select="./Summary" /></td>
					
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/DataReports/Results/collationLength = 0">
				<div class="MsgErrorWide">
					There are no Reports

				</div>
			</xsl:when>
			<xsl:when test="count(/Response/DataReports/Results/rangeSample/DataReport) = 0">
				<div class="MsgNoticeWide">
					There are no Reports

				</div>
			</xsl:when>
			<xsl:otherwise>
				<div class="SmallSeperator"></div>
				<table cellpadding="0">
					<tr>
						<td valign="middle">
							<img src="img/template/report_instant.png" alt="Screen" title="Screen" />
						</td>
						<td valign="middle">
							: The Report will be generated immediately and display on screen
						</td>
					</tr>
					<tr>
						<td valign="middle">
							<img src="img/template/report_email.png" alt="Email" title="Email" />
						</td>
						<td valign="middle">
							: The Report (due to size and complexity) will be emailed to you once generated
						</td>
					</tr>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
