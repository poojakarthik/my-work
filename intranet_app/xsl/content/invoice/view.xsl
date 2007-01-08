<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
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
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Calling Party</th>
				<th>Start Date/Time</th>
				<th class="Currency">Duration</th>
				<th class="Currency">Amount</th>
				<th class="Currency">Options</th>
			</tr>
			<xsl:for-each select="/Response/CDRs-Invoiced/Results/rangeSample/CDR">
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
					<td><xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeStart + position()" />.</td>
					<td>
						<xsl:choose>
							<xsl:when test="/Response/Service/ServiceType = 103">
								<xsl:value-of select="./Source" disable-output-escaping="yes" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="./Destination" disable-output-escaping="yes" />
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"	select="./StartDatetime/year" />
							<xsl:with-param name="month"	select="./StartDatetime/month" />
							<xsl:with-param name="day"		select="./StartDatetime/day" />
	 						<xsl:with-param name="hour"	select="./StartDatetime/hour" />
							<xsl:with-param name="minute"	select="./StartDatetime/minute" />
							<xsl:with-param name="second"	select="./StartDatetime/second" />
							<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
						</xsl:call-template>
					</td>
					<td class="Currency"><xsl:value-of select="./Units" disable-output-escaping="yes" /></td>
					<td class="Currency"><xsl:value-of select="./Charge" disable-output-escaping="yes" /></td>
					<td class="Currency">
						<a>
							<xsl:attribute name="href">
								<xsl:text>javascript:nohref()</xsl:text>
							</xsl:attribute>
							<xsl:attribute name="onclick">
								<xsl:text>return openPopup(</xsl:text>
									<xsl:text>'cdr_view.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>'</xsl:text>
								<xsl:text>)</xsl:text>
							</xsl:attribute>
							View CDR Record
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/CDRs-Invoiced/Results/collationLength = 0">
				<div class="MsgError">
					There are no CDR Records associated with this service.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/CDRs-Invoiced/Results/rangeSample/CDR) = 0">
				<div class="MsgNotice">
					There are no CDR Records for the Range that you Searched for.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<xsl:if test="/Response/CDRs-Invoiced/Results/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="33%" align="left">
							<xsl:if test="/Response/CDRs-Invoiced/Results/rangePage &gt; 1">
								<a>
									<xsl:attribute name="href">
										<xsl:text>invoice_view.php</xsl:text>
										
										<xsl:text>?Id=</xsl:text>
										<xsl:value-of select="/Response/Invoice/Id" />
										
										<xsl:text>&amp;rangeLength=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePage - 1" />
									</xsl:attribute>
									<xsl:text>- Prev</xsl:text>
								</a>
							</xsl:if>
						</td>
						<td width="34%" align="center">
							Page <xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePage" />
							of <xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePages" /><br />
							Showing  
							<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeStart + 1" />
							to
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Invoiced/Results/rangeLength + /Response/CDRs-Invoiced/Results/rangeStart &gt; /Response/CDRs-Invoiced/Results/collationLength">
									<xsl:value-of select="/Response/CDRs-Invoiced/Results/collationLength" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeStart + /Response/CDRs-Invoiced/Results/rangeLength" />
								</xsl:otherwise>
							</xsl:choose>
							of
							<xsl:value-of select="/Response/CDRs-Invoiced/Results/collationLength" />
						</td>
						<td width="33%" align="right">
							<xsl:if test="/Response/CDRs-Invoiced/Results/rangePage &lt; /Response/CDRs-Invoiced/Results/rangePages">
								<a>
									<xsl:attribute name="href">
										<xsl:text>invoice_view.php</xsl:text>
										
										<xsl:text>?Id=</xsl:text>
										<xsl:value-of select="/Response/Invoice/Id" />
										
										<xsl:text>&amp;rangeLength=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePage + 1" />
									</xsl:attribute>
									<xsl:text>Next -</xsl:text>
								</a>
							</xsl:if>
						</td>
					</tr>
				</table>
			</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
