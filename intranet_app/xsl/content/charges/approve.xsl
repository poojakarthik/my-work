<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Approve Charges</h1>
		<div class="Seperator"></div>
		
		<h2>Unapporived Charges</h2>
		<div class="Seperator"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Description</th>
				<th>Amount</th>
				<th>Entered By</th>
				<th>Entered On</th>
				<th>Options</th>
			</tr>
			<xsl:for-each select="/Response/Charges-Unapproved/Results/rangeSample/Charge">
				<tr>
					<td><xsl:value-of select="position()" /></td>
					<td><xsl:value-of select="./Description" /></td>
					<td><xsl:value-of select="./Amount" /></td>
					<td><xsl:value-of select="./CreatedBy" /></td>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./CreatedOn/year" />
							<xsl:with-param name="month"	select="./CreatedOn/month" />
							<xsl:with-param name="day"		select="./CreatedOn/day" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
						</xsl:call-template>
					</td>
					<td>
						<select>
							<xsl:attribute name="name">
								<xsl:text>charge[</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>]</xsl:text>
							</xsl:attribute>
							<option selected="selected" value="-1">No Changes</option>
							<option value="0">Decline Request</option>
							<option value="1">Approve Request</option>
						</select>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
