<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	
	<xsl:template name="Content">
	
	<!--Is this page being used?-->
		<h1>Rate Selection Summary</h1>
		
		<xsl:choose>
			<xsl:when test="/Response/Availability">
				<table width="100%" border="0" cellpadding="2" cellspacing="0" class="summary-table">
					<tr>
						<th></th>
						<th width="3%" colspan="4">12</th>
						<th width="3%" colspan="4">1</th>
						<th width="3%" colspan="4">2</th>
						<th width="3%" colspan="4">3</th>
						<th width="3%" colspan="4">4</th>
						<th width="3%" colspan="4">5</th>
						<th width="3%" colspan="4">6</th>
						<th width="3%" colspan="4">7</th>
						<th width="3%" colspan="4">8</th>
						<th width="3%" colspan="4">9</th>
						<th width="3%" colspan="4">10</th>
						<th width="3%" colspan="4">11</th>
						<th width="3%" colspan="4">12</th>
						<th width="3%" colspan="4">1</th>
						<th width="3%" colspan="4">2</th>
						<th width="3%" colspan="4">3</th>
						<th width="3%" colspan="4">4</th>
						<th width="3%" colspan="4">5</th>
						<th width="3%" colspan="4">6</th>
						<th width="3%" colspan="4">7</th>
						<th width="3%" colspan="4">8</th>
						<th width="3%" colspan="4">9</th>
						<th width="3%" colspan="4">10</th>
						<th width="3%" colspan="4">11</th>
					</tr>
					<xsl:for-each select="/Response/Availability/Availability-Day">
						<tr>
							<td class="black-border-top"><xsl:value-of select="@name" /></td>
							<xsl:call-template name="Summary-BuildDay">
								<xsl:with-param name="day" select="@name" />
							</xsl:call-template>
						</tr>
					</xsl:for-each>
				</table>
				
				<div class="Seperator"></div>
				
				<div class="Left">
					<table border="0" cellpadding="2" cellspacing="0">
						<tr>
							<td>
								<a href="javascript:window.close()">
									<img src="img/template/close.png" style="vertical-align: middle" border="0" />
								</a>
							</td>
							<td>
								<a href="javascript:window.close()">
									Close Window
								</a>
							</td>
						</tr>
					</table>
				</div>
				<div class="Right">
					<table border="0" cellpadding="0" cellspacing="5">
						<tr>
							<td class="summary-blank black-border" width="25" height="25"></td>
							<td>Unallocated</td>
						</tr>
						<tr>
							<td class="summary-single black-border" width="25" height="25"></td>
							<td>Allocated Correctly</td>
						</tr>
						<tr>
							<td class="summary-overlap black-border" width="25" height="25"></td>
							<td>Over Allocated</td>
						</tr>
					</table>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<p>
					You must select at least one rate to view an allocation summary. 
					Please close this window and select atleast one rate.
				</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template name="Summary-BuildDay">
		<xsl:param name="day" />
		
		<xsl:for-each select="/Response/Availability/Availability-Day[@name=$day]/Availability-Hour">
			<xsl:call-template name="Summary-BuildHour">
				<xsl:with-param name="day" select="$day" />
				<xsl:with-param name="hour" select="@number" />
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="Summary-BuildHour">
		<xsl:param name="day" />
		<xsl:param name="hour" />
		
		<xsl:for-each select="/Response/Availability/Availability-Day[@name=$day]/Availability-Hour[@number=$hour]/Availability-Quarter">
			<xsl:call-template name="Summary-BuildQuarter">
				<xsl:with-param name="day" select="$day" />
				<xsl:with-param name="hour" select="$hour" />
				<xsl:with-param name="quarter" select="@number" />
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="Summary-BuildQuarter">
		<xsl:param name="day" />
		<xsl:param name="hour" />
		<xsl:param name="quarter" />
		
		<xsl:variable name="Position" select="/Response/Availability/Availability-Day[@name=$day]/Availability-Hour[@number=$hour]/Availability-Quarter[@number=$quarter]" />
		
		<td>
			<xsl:attribute name="class">
				<xsl:text>black-border-top cell-</xsl:text>
				<xsl:value-of select="$quarter" />
				<xsl:text> </xsl:text>
				<xsl:choose>
					<xsl:when test="count($Position/Rate) = 1">
						<xsl:text>summary-single</xsl:text>
					</xsl:when>
					<xsl:when test="count($Position/Rate) &gt; 1">
						<xsl:text>summary-overlap</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>summary-blank</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
		</td>
	</xsl:template>
</xsl:stylesheet>
