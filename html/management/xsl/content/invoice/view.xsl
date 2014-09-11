<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">	
		<!-- View invoice Details (Breakdown) (2/2) -->
		<h1>View Invoice Details</h1>

		<!-- Invoice Details -->
		<h2 class="Invoice">Invoice Details</h2>
		<div class="Wide-Form">
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Account')" />
							<xsl:with-param name="field" select="string('Id')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Account/Id" />
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
				<!--Check for Trading Name-->
				<xsl:choose>
					<xsl:when test="/Response/Account/TradingName = ''">
					</xsl:when>
					<xsl:otherwise>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/TradingName" />
							</td>
						</tr>
					</xsl:otherwise>
				</xsl:choose>
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
							<xsl:with-param name="entity" select="string('Service')" />
							<xsl:with-param name="field" select="string('FNN')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/ServiceTotal/FNN" />
					</td>
				</tr>
			</table>
		</div>
		<div class="Seperator"></div>
		
		<!-- Credits & Debits -->
		<xsl:if test="/Response/Charges">
			<h2 class="Adjustment">Adjustments</h2>
			
					<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
						<tr class="First">
							<th>Id</th>
							<th>Code</th>
							<th>Description</th>
							<th>Service</th>
							<th>Adjustment Date</th>
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
								<td>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="./Amount" />
										<xsl:with-param name="Decimal" select="number('2')" />
			       					</xsl:call-template>
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="./Nature = 'DR'">
											<span class="Blue">Debit</span>
										</xsl:when>
										<xsl:when test="./Nature = 'CR'">
											<span class="Green">Credit</span>
										</xsl:when>
									</xsl:choose>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				<xsl:choose>
				<xsl:when test="/Response/Charges/Results/collationLength = 0">
					<div class="MsgNoticeWide">
						There are no Adjustments associated with this Invoice.
					</div>
				</xsl:when>
			</xsl:choose>
		</xsl:if>
		<div class="Seperator"></div>
		
		<!--Usage Charges -->
		<h2 class="Invoice">Usage Charges</h2>
		
		<form method="get" action="invoice_view.php">
			<input type="hidden" name="Invoice">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="ServiceTotal">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ServiceTotal/Id" />
				</xsl:attribute>
			</input>
			
			<!-- Record Type Filter -->
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('RecordType')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="RecordType">
								<option value="">All</option>
								<xsl:for-each select="/Response/RecordTypes/Results/rangeSample/RecordType">
									<xsl:sort select="./Name" />
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="./Id = /Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="./Name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<div class="Right">
				<input type="submit" value="Filter &#0187;" class="input-submit" />
			</div>
			<div class="Clear"></div>
		</form>
		
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Record Type</th>
				<th>Start Date/Time</th>
				<th>Calling Party</th>
				<th>Duration</th>
				<th class="Currency">Charge</th>
				<th class="Currency">Actions</th>
			</tr>
			<xsl:for-each select="/Response/CDRs-Invoiced/Results/rangeSample/CDR">
				<xsl:variable name="CDR" select="." />
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
						<xsl:value-of select="/Response/RecordTypes/Results/rangeSample/RecordType[./Id = $CDR/RecordType]/Name" />
					</td>
					
					
					<xsl:choose>
						<xsl:when test="/Response/RecordTypes/Results/rangeSample/RecordType[./Id = $CDR/RecordType]/DisplayType = 1">
							<td>
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"	select="./StartDatetime/year" />
									<xsl:with-param name="month"	select="./StartDatetime/month" />
									<xsl:with-param name="day"		select="./StartDatetime/day" />
			 						<xsl:with-param name="hour"	select="./StartDatetime/hour" />
									<xsl:with-param name="minute"	select="./StartDatetime/minute" />
									<xsl:with-param name="second"	select="./StartDatetime/second" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
								</xsl:call-template>
							</td>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/Service/ServiceType = 103">
										<xsl:value-of select="./Source" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="./Destination" />
									</xsl:otherwise>
								</xsl:choose>
							</td>
							<td>
								<xsl:value-of select="./Duration" />
							</td>
						</xsl:when>
						
						<xsl:when test="/Response/RecordTypes/Results/rangeSample/RecordType[./Id = $CDR/RecordType]/DisplayType = 2">
							<td colspan="3">
								<xsl:value-of select="./Description" />
							</td>
						</xsl:when>
						
						<xsl:when test="/Response/RecordTypes/Results/rangeSample/RecordType[./Id = $CDR/RecordType]/DisplayType = 3">
							<td colspan="3">
								GPRS Data
							</td>
						</xsl:when>
						
						<xsl:when test="/Response/RecordTypes/Results/rangeSample/RecordType[./Id = $CDR/RecordType]/DisplayType = 4">
							<td>
								<xsl:call-template name="dt:format-date-time">
									<xsl:with-param name="year"		select="./StartDatetime/year" />
									<xsl:with-param name="month"	select="./StartDatetime/month" />
									<xsl:with-param name="day"		select="./StartDatetime/day" />
			 						<xsl:with-param name="hour"		select="./StartDatetime/hour" />
									<xsl:with-param name="minute"	select="./StartDatetime/minute" />
									<xsl:with-param name="second"	select="./StartDatetime/second" />
									<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
								</xsl:call-template>
							</td>
							<td>
								<xsl:value-of select="./Destination" />
							</td>
							<td>
								SMS
							</td>
						</xsl:when>
					</xsl:choose>
					
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Charge" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
					</td>
					<td class="Currency">
						<a href="#" title="CDR Information" alt="Client Data Record Information">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, </xsl:text>
									<xsl:text>'cdr_view.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>'</xsl:text>
								<xsl:text>)</xsl:text>
							</xsl:attribute>
							View CDR 
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/CDRs-Invoiced/Results/collationLength = 0">
				<div class="MsgErrorWide">
					No CDR Records matched the Search which you Requested.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/CDRs-Invoiced/Results/rangeSample/CDR) = 0">
				<div class="MsgNoticeWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<xsl:if test="/Response/CDRs-Invoiced/Results/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Invoiced/Results/rangePage != 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>invoice_view.php</xsl:text>
											
											<xsl:text>?Invoice=</xsl:text>
											<xsl:value-of select="/Response/Invoice/Id" />
											
											<xsl:text>&amp;ServiceTotal=</xsl:text>
											<xsl:value-of select="/Response/ServiceTotal/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=1</xsl:text>
											
											<xsl:if test="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value" />
											</xsl:if>
										</xsl:attribute>
										<xsl:text>&#124;&lt;- First</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- &#124;&lt;- First -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Invoiced/Results/rangePage &gt; 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>invoice_view.php</xsl:text>
											
											<xsl:text>?Invoice=</xsl:text>
											<xsl:value-of select="/Response/Invoice/Id" />
											
											<xsl:text>&amp;ServiceTotal=</xsl:text>
											<xsl:value-of select="/Response/ServiceTotal/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePage - 1" />
											
											<xsl:if test="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value" />
											</xsl:if>
										</xsl:attribute>
										<xsl:text>&lt;- Prev</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- &lt;- Prev -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="60%" align="center">
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
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Invoiced/Results/rangePage &lt; /Response/CDRs-Invoiced/Results/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>invoice_view.php</xsl:text>
											
											<xsl:text>?Invoice=</xsl:text>
											<xsl:value-of select="/Response/Invoice/Id" />
											
											<xsl:text>&amp;ServiceTotal=</xsl:text>
											<xsl:value-of select="/Response/ServiceTotal/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePage + 1" />

											<xsl:if test="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value" />
											</xsl:if>
										</xsl:attribute>
										<xsl:text>Next -&gt;</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- Next -&gt; -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Invoiced/Results/rangePage != /Response/CDRs-Invoiced/Results/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>invoice_view.php</xsl:text>
											
											<xsl:text>?Invoice=</xsl:text>
											<xsl:value-of select="/Response/Invoice/Id" />
											
											<xsl:text>&amp;ServiceTotal=</xsl:text>
											<xsl:value-of select="/Response/ServiceTotal/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Invoiced/Results/rangePages" />
											
											<xsl:if test="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Invoiced/Constraints/Constraint[./Name='RecordType']/Value" />
											</xsl:if>
										</xsl:attribute>
										<xsl:text>Last -&gt;&#124;</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- Last -&gt;&#124; -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</table>
			</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
