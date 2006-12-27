<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Rate Plan</h1>
		
		<script language="javascript" src="js/rates_plan_add.js"></script>
		
		<form method="POST" action="rates_plan_add.php">
			<xsl:attribute name="onsubmit">
				<xsl:text>return selIt()</xsl:text>
			</xsl:attribute>
			
			<input type="hidden" name="Name">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RatePlan/Name" disable-output-escaping="yes" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ServiceType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RatePlan/ServiceTypes/ServiceType[@selected='selected']/Id" disable-output-escaping="yes" />
				</xsl:attribute>
			</input>
			
			<xsl:if test="/Response/RatePlan/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/RatePlan/Error = 'Requirements'">
							You did not fill in all the Required Record Type fields. Please
							select all the required Record Types and resubmit.
						</xsl:when>
						<xsl:when test="/Response/RatePlan/Error = 'Mishandled'">
							A system error occurred.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/RatePlan/Name" disable-output-escaping="yes" />
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/RatePlan/ServiceTypes/ServiceType[@selected='selected']/Name" disable-output-escaping="yes" />
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Description" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RatePlan/Name" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
			</div>
			
			<div class="Seperator"></div>
					
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('MinMonthly')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="MinMonthly" class="input-string" />
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('ChargeCap')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ChargeCap" class="input-string" />
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('UsageCap')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="UsageCap" class="input-string" />
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
					
				<div class="Clear"></div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2>Record Types</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<xsl:for-each select="/Response/RatePlan/RecordTypes/Results/rangeSample/RecordType">
							<xsl:variable name="RecordType" select="./Id" />
							<tr>
								<xsl:if test="./Required = 1">
									<xsl:attribute name="class">
										<xsl:text>Required</xsl:text>
									</xsl:attribute>
								</xsl:if>
								
								<th valign="top">
									<xsl:value-of select="./Name" /> :
								</th>
								<td>
									<select>
										<xsl:attribute name="name">
											<xsl:text>RecordType[</xsl:text>
											<xsl:value-of select="./Id" />
											<xsl:text>]</xsl:text>
										</xsl:attribute>
										<option selected="selected" value=""></option>
										<xsl:for-each select="/Response/RatePlan/RateGroups/Results/rangeSample/RateGroup[RecordType/Id=$RecordType]">
											<option>
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="./Id" />
												</xsl:attribute>
												<xsl:value-of select="./Name" />
											</option>
										</xsl:for-each>
									</select>
								</td>
							</tr>
						</xsl:for-each>
					</table>
					
					<div class="Clear"></div>
				</div>
				<div class="Clear"></div>
				<div class="Seperator"></div>
				
				<table border="0" cellpadding="5" cellspacing="0">
					<tr class="Required">
						<th width="30"></th>
						<td>
							<strong>Required Record Types</strong>
						</td>
					</tr>
				</table>
				
				<div class="Clear"></div>
			</div>
			<div class="Seperator"></div>
			
			<h2>Recurring Changes</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					Select multiple Recurring Charges by holding the CTRL key while you click options from
					either of the lists.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th>Available Recurring Charges :</th>
							<td></td>
							<th>Selected Recurring Charges :</th>
						</tr>
						<tr>
							<td>
								<select id="AvailableOptions" name="AvailableRecurringChargeTypes[]" size="20" class="LargeSelection" multiple="multiple">
									<xsl:for-each select="/Response/RatePlan/RecurringChargeTypes/Results/rangeSample/RecurringChargeType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Description" />
											(<xsl:value-of select="./RecursionCharge" />)
										</option>
									</xsl:for-each>
								</select>
							</td>
							<td>
								<div>
									<input type="button" value="&#0187;">
										<xsl:attribute name="onclick">
											<xsl:text>addIt ()</xsl:text>
										</xsl:attribute>
									</input>
								</div>
								<div class="Seperator"></div>
								<div>
									<input type="button" value="&#0171;">
										<xsl:attribute name="onclick">
											<xsl:text>delIt ()</xsl:text>
										</xsl:attribute>
									</input>
								</div>
							</td>
							<td>
								<select id="SelectedOptions" name="SelectedRecurringChargeTypes[]" size="20" class="LargeSelection" multiple="multiple" />
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Plan &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
