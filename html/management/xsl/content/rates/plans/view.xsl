<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<!--Is this page still being used-->
		<h1>View Rate Plan Details</h1>
		
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th>Rate Plan Id:</th>
						<td><xsl:value-of select="/Response/RatePlanDetails/RatePlan/Id" /></td>
					</tr>
					<tr>
						<th>Rate Plan Name:</th>
						<td><xsl:value-of select="/Response/RatePlanDetails/RatePlan/Name" /></td>
					</tr>
					<tr>
						<th>Rate Plan Description:</th>
						<td><xsl:value-of select="/Response/RatePlanDetails/RatePlan/Description" /></td>
					</tr>
					<tr>
						<th>Service Type:</th>
						<td><xsl:value-of select="/Response/RatePlanDetails/RatePlan/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th>Archive Status:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RatePlanDetails/RatePlan/Archived = 0">
									<strong><span class="Green">Currently Available</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<strong><span class="Red">Currently Archived</span></strong>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<h2>Associated Rate Groups</h2>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Rate Group Name</th>
				<th>Record Type</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/RatePlanDetails/RateGroups/RateGroup">
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
						<a href="#" title="Rate Group Details" alt="Information about this Rate Group and its Charges">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, </xsl:text>
								<xsl:text>'rates_group_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:text>View Rate Group Details</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
		
		<h2>Associated Recurring Charges</h2>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Code</th>
				<th>Description</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/RatePlanDetails/RecurringChargeTypes/RecurringChargeType">
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
					<td><xsl:value-of select="./ChargeType" /></td>
					<td><xsl:value-of select="./Description" /></td>
					<td>
						
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="count(/Response/RatePlanDetails/RecurringChargeTypes/RecurringChargeType) = 0">
				<div class="MsgNoticeWide">
					There are no Recurring Charges associated with this Rate Plan.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="Seperator"></div>
		
	</xsl:template>
</xsl:stylesheet>
