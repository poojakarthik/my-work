<?xml version="1.0" encoding="utf-8"?>
<!-- TODO!bash! [  DONE   ]		change class of errors and notices MsgNotice & MsgError no longer exist-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Manage Adjustments</h1>

		<h2 class="Adjustment">Adjustment Details</h2>
		<form method="post" action="charges_approve.php">
			<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
				<tr class="First">
					<th width="30">#</th>
					<th>Description</th>
					<th class="Currency">Amount</th>
					<th>Nature</th>
					<th>Account</th>
					<th>Entered By</th>
					<th>Entered On</th>
					<th>Actions</th>
				</tr>
				<xsl:for-each select="/Response/Charges-Unapproved/Results/rangeSample/Charge">
					<xsl:variable name="Charge" select="." />
					<xsl:variable name="CreatedBy" select="/Response/Employees/Employee[./Id=$Charge/CreatedBy]" />
					
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
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="./Amount" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="./Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="./Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
							</strong>
						</td>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_view.php?Id=</xsl:text>
									<xsl:value-of select="./Account" />
								</xsl:attribute>
								<xsl:text></xsl:text>
								<xsl:value-of select="./Account" />
							</a>
						</td>
						<td>
							<xsl:value-of select="$CreatedBy/FirstName" />
							<xsl:text> </xsl:text>
							<xsl:value-of select="$CreatedBy/LastName" />
						</td>
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
				<xsl:otherwise>
					<div class="SmallSeperator"></div>
					<div class="Right">
						<input type="submit" value="Apply Changes &#0187;" class="input-submit" />
					</div>
					<div class="Clear" />
					<div class="Seperator"></div>
					
					<div style='text-align: center;'>
						<xsl:choose>
							<xsl:when test='/Response/Charges-Unapproved/Results/rangePage &gt; 1'>
								<a href='../management/charges_approve.php?p=1'>
									First
								</a>
								|
								<a>
									<xsl:attribute name="href">
										<xsl:text>../management/charges_approve.php?p=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unapproved/Results/rangePage - 1" />
									</xsl:attribute>
									Previous
								</a>
							</xsl:when>
							<xsl:otherwise>
								First | Previous
							</xsl:otherwise>
						</xsl:choose>
						|
						<xsl:choose>
							<xsl:when test='/Response/Charges-Unapproved/Results/rangePage &lt; /Response/Charges-Unapproved/Results/rangePages'>
								<a href='../management/charges_approve.php?p=1'>
									<xsl:attribute name="href">
										<xsl:text>../management/charges_approve.php?p=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unapproved/Results/rangePage + 1" />
									</xsl:attribute>
									Next
								</a>
								|
								<a>
									<xsl:attribute name="href">
										<xsl:text>../management/charges_approve.php?p=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unapproved/Results/rangePages" />
									</xsl:attribute>
									Last
								</a>
							</xsl:when>
							<xsl:otherwise>
								Next | Last
							</xsl:otherwise>
						</xsl:choose>
						<br />
						Page <xsl:value-of select="/Response/Charges-Unapproved/Results/rangePage" />
						of <xsl:value-of select="/Response/Charges-Unapproved/Results/rangePages" />
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</form>
	</xsl:template>
</xsl:stylesheet>
