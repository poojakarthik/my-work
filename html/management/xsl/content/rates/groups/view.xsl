<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	
	<!--Is this page still being used ?-->
	<xsl:template name="Content">
		<h1>Rate Group Details</h1>
		

		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th>Rate Group Id:</th>
						<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/Id" /></td>
					</tr>
					<tr>
						<th>Rate Group Name:</th>
						<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/Name" /></td>
					</tr>
					<tr>
						<th>Rate Group Description:</th>
						<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/Description" /></td>
					</tr>
					<tr>
						<th>Service Type:</th>
						<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th>Record Type:</th>
						<td><xsl:value-of select="/Response/RateGroupDetails/RateGroup/RecordType/Name" /></td>
					</tr>
					<tr>
						<th>Archive Status:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RateGroupDetails/RateGroup/Archived = 0">
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
		
		<h2>Associated Rates</h2>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Rate Name</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/RateGroupDetails/RateGroupRate/rangeSample/Rate">
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
					<td>
						<a href="#" title="Rate Details" alt="Information about this Rate and its Charges">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, </xsl:text>
								<xsl:text>'rates_rate_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:text>View Rate Details</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
		
		<h2>Plans using this Rate Group</h2>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Plan Name</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/RateGroupDetails/RatePlans/RatePlan">
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
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>rates_plan_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>View Plan Details</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
