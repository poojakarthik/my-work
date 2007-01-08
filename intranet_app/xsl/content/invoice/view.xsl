<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:import href="../../lib/date-difference/date.difference.function.xsl"/>
	<xsl:import href="../../lib/date-difference/date.difference.template.xsl"/>
	
	<xsl:template name="Content">
		<h1>View Invoice Details</h1>
		<div class="Seperator"></div>
		
		<h2>Invoice Overview</h2>
		<div class="Seperator"></div>
		<div class="Filter-Form">
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('Id')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Invoice/Id" />
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Account')" />
							<xsl:with-param name="field" select="string('BusinessName')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Account/BusinessName" />
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Account')" />
							<xsl:with-param name="field" select="string('TradingName')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Account/TradingName" />
					</td>
				</tr>
			</table>
		</div>
		<div class="Seperator"></div>
		
		<xsl:if test="/Response/Charges">
			<h2>Credits + Debits</h2>
			<div class="Seperator"></div>
			
			<xsl:choose>
				<xsl:when test="/Response/Charges/Results/collationLength = 0">
					<div class="MsgNotice">
						No charges were applied to this particular invoice.
					</div>
				</xsl:when>
				<xsl:otherwise>
					<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="100%">
						<tr class="First">
							<th>Id</th>
							<th>Code</th>
							<th>Description</th>
							<th>Service</th>
							<th>Charge Date</th>
							<th>Amount</th>
							<th>Nature</th>
						</tr>
						<xsl:for-each select="/Response/Charges/Results/rangeSample/Charge">
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
								
								<td><xsl:value-of select="./Id" /></td>
								<td><xsl:value-of select="./ChargeType" /></td>
								<td><xsl:value-of select="./Description" /></td>
								<td>
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_view.php?Id=</xsl:text>
											<xsl:value-of select="./Service/Id" />
										</xsl:attribute>
										<xsl:value-of select="./Service/FNN" />
									</a>
								</td>
								<td>
									<xsl:call-template name="dt:format-date-time">
										<xsl:with-param name="year"	select="./ChargedOn/year" />
										<xsl:with-param name="month"	select="./ChargedOn/month" />
										<xsl:with-param name="day"		select="./ChargedOn/day" />
										<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
									</xsl:call-template>
								</td>
								<td><xsl:value-of select="./Amount" /></td>
								<td>
									<strong>
										<span>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="./Nature = 'CR'">
														<xsl:text>Blue</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>Green</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											<xsl:value-of select="./Nature" />
										</span>
									</strong>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<div class="Seperator"></div>
		
		<h2>Charges</h2>
		<div class="Seperator"></div>
		
		<xsl:choose>
			<xsl:when test="/Response/CDRs/Results/collationLength = 0">
				<div class="MsgNotice">
					No CDR records were found for this particular Invoice
				</div>
			</xsl:when>
			<xsl:otherwise>
				<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="100%">
					<tr class="First">
						<th>Id</th>
						<th>Code</th>
						<th>Description</th>
						<th>Service</th>
						<th>Charge Date</th>
						<th>Amount</th>
						<th>Nature</th>
					</tr>
					<xsl:for-each select="/Response/CDRs/rangeSample/CDR">
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
							
							<td><xsl:value-of select="./Id" /></td>
							<td><xsl:value-of select="./ChargeType" /></td>
							<td><xsl:value-of select="./Description" /></td>
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:text>service_view.php?Id=</xsl:text>
										<xsl:value-of select="./Service/Id" />
									</xsl:attribute>
									<xsl:value-of select="./Service/FNN" />
								</a>
							</td>
							<td>
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"	select="./ChargedOn/year" />
									<xsl:with-param name="month"	select="./ChargedOn/month" />
									<xsl:with-param name="day"		select="./ChargedOn/day" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
								</xsl:call-template>
							</td>
							<td><xsl:value-of select="./Amount" /></td>
							<td>
								<strong>
									<span>
										<xsl:attribute name="class">
											<xsl:choose>
												<xsl:when test="./Nature = 'CR'">
													<xsl:text>Blue</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>Green</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										<xsl:value-of select="./Nature" />
									</span>
								</strong>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
