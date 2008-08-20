<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!-- Page for Viewing a list of Available Plans -->
		
		<h1>View Available Plans</h1>
		
		<!-- Search for Plan -->
		<h2 class="Plan">Search for a Plan</h2>
		
		<!--TODO!bash! [ANSWERED] Why can I search for an empty field? show an error!-->
		<!--TODO!bash! A: Because you may want to view all Plans of a certain Service Type. -->
		
		<form method="GET" action="../admin/flex.php/Plan/AvailablePlans/">
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<!-- TODO!bash! [  DONE  ]		at some point are we going to have an option other than 'contains' ? -->
							<!-- TODO!bash! [  DONE  ]		if this is not going to happen before we go live then this select box looks stupid -->
							<!-- TODO!bash! [  DONE  ]		remove this if there is only one option -->
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[Name][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[Name][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RatePlans/Constraints/Constraint[Name=string('Name')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td><input type="hidden" name="constraint[ServiceType][Operator]" value="EQUALS" /></td>
							<td>
								<select name="constraint[ServiceType][Value]">
									<xsl:for-each select="/Response/ServiceTypes/ServiceType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/RatePlans/Constraints/Constraint[Name=string('ServiceType')]/Value = ./Id">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
											</xsl:if>
											<xsl:text></xsl:text>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" value="Search" class="input-submit" />
			</div>
			<div class="Clear"></div>
		</form>
		
		<div class="Seperator"></div>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
					<tr class="First">
						<th width="30">#</th>
						<th>Plan Name</th>
						<th>Service Type</th>
						<th>Actions</th>
					</tr>
					<xsl:for-each select="/Response/RatePlans/Results/rangeSample/RatePlan">
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
								<a>
									<xsl:attribute name="href">
										<xsl:text>rates_plan_summary.php?Id=</xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:value-of select="./Name" />
								</a>
							</td>
							<td><xsl:value-of select="./ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:text>rates_plan_summary.php?Id=</xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:text>View Plan Details</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/RatePlans/Results/collationLength = 0">
						<div class="MsgErrorWide">
							There were no results matching your search. Please change your search and try again.

						</div>
					</xsl:when>
					<xsl:when test="count(/Response/RatePlans/Results/rangeSample/RatePlan) = 0">
						<div class="MsgNoticeWide">
							There were no results matching your search. Please change your search and try again.

						</div>
					</xsl:when>
				</xsl:choose>
				

			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
