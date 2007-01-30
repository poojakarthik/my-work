<?xml version="1.0" encoding="utf-8"?>
<!-- TODO!bash! change class of errors and notices MsgNotice & MsgError no longer exist-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Approve Charges</h1>
		<div class="Seperator"></div>
		
		<form method="post" action="charges_approve.php">
			<h2>Unapproved Charges</h2>
			<div class="Seperator"></div>
			<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
				<tr class="First">
					<th width="30">#</th>
					<th>Description</th>
					<th>Amount</th>
					<th>Account</th>
					<th>Entered By</th>
					<th>Entered On</th>
					<th>Actions</th>
				</tr>
				<xsl:for-each select="/Response/Charges-Unapproved/Results/rangeSample/Charge">
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
						<td><xsl:value-of select="./Description" /></td>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="./Amount" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_view.php?Id=</xsl:text>
									<xsl:value-of select="./Account" />
								</xsl:attribute>
								<xsl:text>View Account</xsl:text>
							</a>
						</td>
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
								<option value="-1" style="background-color:#FFCC00;font-weight: bold;" selected="selected">No Changes</option>
								<option value="0" style="background-color:#CC0000; color:#FFFFFF;">Decline Request</option>
								<option value="1" style="background-color:#008000; color:#FFFFFF;">Approve Request</option>
							</select>
						</td>
					</tr>
				</xsl:for-each>
			</table>
			
			<xsl:choose>
				<xsl:when test="/Response/Charges-Unapproved/Results/collationLength = 0">
					<div class="MsgNoticeWide">
						There are no requests for Debits or Credits to be processed.
					</div>
				</xsl:when>
			</xsl:choose>
			
			<div class="Seperator"></div>
			
			<input type="submit" value="Delegate Approvals &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
