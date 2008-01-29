<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
	
		<!-- Page for viewing details of plans -->
		<h1>View Rate Plan Details</h1>
		
		<!--TODO!bash! [  DONE  ]		URGENT - the 'More Details...' link does not work-->
		<!--TODO!bash! [  DONE  ]		URGENT - this page also needs to  link to View Rate Details -->
		
		<!-- Plan Details -->
		<h2 class="Plan">Plan Details</h2>
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Rate Plan Name :</th>
						<td><xsl:value-of select="/Response/RatePlan/Name" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Rate Plan Description :</th>
						<td><xsl:value-of select="/Response/RatePlan/Description" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Service Type :</th>
						<td><xsl:value-of select="/Response/RatePlan/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Archive Status :</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RatePlan/Archived = 0">
									<strong><span class="Green">Currently Available</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<strong><span class="Red">Currently Archived</span></strong>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Charge Cap :</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/RatePlan/ChargeCap" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Usage Cap :</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/RatePlan/UsageCap" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Minimum Monthly :</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/RatePlan/MinMonthly" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Shared Cap :</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RatePlan/Shared = 1">
									<strong><span class="Green">Shared Plan</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<strong><span class="Blue">Non-Shared Plan</span></strong>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<!-- TODO!bash! [  DONE  ]		Shared? MinMontly? ChargeCap? UsageCap?  where are they? they need to be shown here-->
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<!-- Charges -->
		<h2 class="Invoice">Charges</h2>
		<!--TODO!bash! [  DONE  ]		URGENT! Why does this say more details when there is <5 rates? -->
		<table border="0" width="100%" cellpadding="3" cellspacing="0" style="font-family: monospace; font-size: 9pt;" class="Listing">
			<xsl:for-each select="/Response/RecordTypes/Results/rangeSample/RecordType">
				<xsl:variable name="RecordType" select="." />
				<xsl:variable name="RateGroup" select="/Response/RateGroups/RateGroup[RecordType=$RecordType]" />
			
				<xsl:if test="$RateGroup">
					<tr class="First">
						<th colspan="3">
							<xsl:value-of select="$RecordType/Name" />
							<!-- TODO!bash! [  DONE  ]		link this to view rate group details -->
							(
								<a title="Rate Group Details">
									<xsl:attribute name="href">
										<xsl:text>rates_group_details.php?Id=</xsl:text>
										<xsl:value-of select="$RateGroup/Id" />
									</xsl:attribute>
									<xsl:value-of select="$RateGroup/Name" />
									</a>
							)
															
						</th>
					</tr>
					<xsl:for-each select="/Response/RateGroupRates/RateGroupRate[RateGroup=$RateGroup/Id]/Rates/Rate">
						<xsl:variable name="RateId" select="." />
						<xsl:variable name="Rate" select="/Response/Rates/Rate[Id=$RateId]" />
						
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
							
							<td>
								<!-- TODO!bash! [  DONE  ]		link this to view rate details -->
								<a href="#" title="Rate Details" alt="Information about this Rate and its Charges">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, </xsl:text>
										<xsl:text>'rates_rate_view.php?Id=</xsl:text>
										<xsl:value-of select="$Rate/Id" />
										<xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:value-of select="$Rate/Description" />	
								</a>
							</td>
							<td>
								<table border="0" cellpadding="3" cellspacing="0">
									<tr>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Monday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Mo</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Tuesday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Tu</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Wednesday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											We</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Thursday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Th</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Friday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Fr</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Saturday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Sa</span></strong>
										</td>
										<td>
											<strong><span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="$Rate/Sunday = 1">Green</xsl:when>
													<xsl:otherwise>Red</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											Su</span></strong>
										</td>
									</tr>
								</table>
							</td>
							<td>
								<xsl:call-template name="dt:format-date-time">
			 						<xsl:with-param name="hour"		select="$Rate/StartTime/hour" />
									<xsl:with-param name="minute"	select="$Rate/StartTime/minute" />
									<xsl:with-param name="second"	select="$Rate/StartTime/second" />
									<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
								</xsl:call-template>
								to
								<xsl:call-template name="dt:format-date-time">
			 						<xsl:with-param name="hour"		select="$Rate/EndTime/hour" />
									<xsl:with-param name="minute"	select="$Rate/EndTime/minute" />
									<xsl:with-param name="second"	select="$Rate/EndTime/second" />
									<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
								</xsl:call-template>
							</td>
						</tr>
					</xsl:for-each>
					<xsl:if test="count(/Response/RateGroupRates/RateGroupRate[RateGroup=$RateGroup/Id]/Rates/Rate) = 5">
						<tr>
							<td colspan="4" align="right">
								<a>
									<xsl:attribute name="href">
										<xsl:text>rates_group_details.php?Id=</xsl:text>
										<xsl:value-of select="$RateGroup/Id" />
									</xsl:attribute>
									<xsl:text>More Details...</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:if>
				</xsl:if>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
		
		<!-- TODO!bash! [  DONE  ]		add in a section for Recurring Charges -->
		<h2 class="Charge">Recurring Charges</h2>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Code</th>
				<th>Description</th>
			</tr>
			<xsl:for-each select="/Response/RecurringChargeTypes/RecurringChargeType">
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
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="count(/Response/RecurringChargeTypes/RecurringChargeType) = 0">
				<div class="MsgNoticeWide">
					There are no Recurring Charges associated with this Rate Plan.
				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
