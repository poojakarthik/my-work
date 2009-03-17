<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Unbilled Charges</h1>
		
		<!-- Service Details -->
		<h2 class="Service">Service Details</h2>
		
		<div class="Wide-Form">
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Service')" />
							<xsl:with-param name="field" select="string('FNN')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Service/FNN" />
					</td>
				</tr>
			</table>
		</div>
		<div class="Seperator"></div>
		
		<!--TODO!bash! [  DONE  ]		URGENT - Add a table for Adjustment -->
		<!--TODO!bash! [  DONE  ]		Show all added sevice charges with status CHARGE_APPROVED(Green) OR CHARGE_WAITING. -->
		<!--TODO!bash! [  DONE  ]		Do NOT show CHARGE_DECLINED or CHARGE_INVOICED -->

		<!-- Unbilled Charges -->
		<xsl:if test="/Response/Charges-Unbilled">
			<h2 class="Adjustment">Unbilled Adjustments</h2>
			<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
				<tr class="First">
					<th width="30">#</th>
					<th>Adjustment Code</th>
					<th>Description</th>
					<th>Created On</th>
					<th>Created By</th>
					<th>Status</th>
					<th class="Currency">Charge</th>
					<th>Nature</th>
					<th>Details</th>
				</tr>
				<xsl:for-each select="/Response/Charges-Unbilled/Results/rangeSample/Charge">
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
						<td><xsl:value-of select="./ChargeType" /></td>
						<td><xsl:value-of select="./Description" /></td>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"		select="./CreatedOn/year" />
								<xsl:with-param name="month"	select="./CreatedOn/month" />
								<xsl:with-param name="day"		select="./CreatedOn/day" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
							</xsl:call-template>
						</td>
						<td>
							<xsl:value-of select="$CreatedBy/FirstName" />
							<xsl:text> </xsl:text>
							<xsl:value-of select="$CreatedBy/LastName" />
						</td>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="./Status = '100'">
										<span class="Red">Unapproved</span>
									</xsl:when>
									<xsl:otherwise>
										<span class="Green">Approved</span>
									</xsl:otherwise>
								</xsl:choose>
							</strong>
						</td>
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="./Amount" />
								<xsl:with-param name="Decimal" select="number('4')" />
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
							<a href="#" title="Adjustment Details" alt="View Information about an Adjustment">
								<xsl:attribute name="onclick">
									<xsl:text>return ModalExternal (this, </xsl:text>
										<xsl:text>'charges_charge_details.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>'</xsl:text>
									<xsl:text>)</xsl:text>
								</xsl:attribute>
								<xsl:text>Details</xsl:text>
							</a>
						</td>
					</tr>
				</xsl:for-each>
			</table>
			<!--TODO!bash! [  DONE  ]		URGENT - if there are no charges or credits: -->
			<!--TODO!bash! [  DONE  ]		msgnotice === "There are no Charges or Credits associated with this Service." -->
			<xsl:if test="/Response/Charges-Unbilled/Results/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Charges or Credits associated with this Service.
				</div>
			</xsl:if>
			
			<div class="Seperator"></div>
		</xsl:if>
		
		<!-- Unbilled Calls -->
		<h2 class="Adjustment">Unbilled Calls</h2>
		
		<form method="get" action="service_unbilled.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
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
										<xsl:if test="./Id = /Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value">
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
			<xsl:for-each select="/Response/CDRs-Unbilled/Results/rangeSample/CDR">
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
					
					<td><xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeStart + position()" />.</td>
					<td><xsl:value-of select="/Response/RecordTypes/Results/rangeSample/RecordType[./Id = $CDR/RecordType]/Name" /></td>
					
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
							<xsl:with-param name="Decimal" select="number('4')" />
							<xsl:with-param name="IsCredit" select="./Credit" />
       					</xsl:call-template>
					</td>
					<td class="Currency">
						<a href="#" title="CDR Information" alt="View CDR Record Information">
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
			<xsl:when test="/Response/CDRs-Unbilled/Results/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Unbilled Calls associated with this Service.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/CDRs-Unbilled/Results/rangeSample/CDR) = 0">
				<div class="MsgErrorWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
		</xsl:choose>
		<div class="Seperator"></div>
		
		<xsl:if test="/Response/CDRs-Unbilled/Results/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/Results/rangePage != 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_unbilled.php</xsl:text>
											
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=1</xsl:text>

											<xsl:if test="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value" />
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
								<xsl:when test="/Response/CDRs-Unbilled/Results/rangePage &gt; 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_unbilled.php</xsl:text>
											
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangePage - 1" />
											
											<xsl:if test="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value" />
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
							Page <xsl:value-of select="/Response/CDRs-Unbilled/Results/rangePage" />
							of <xsl:value-of select="/Response/CDRs-Unbilled/Results/rangePages" /><br />
							Showing <xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeStart + 1" />
							to
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/Results/rangeLength + /Response/CDRs-Unbilled/Results/rangeStart &gt; /Response/CDRs-Unbilled/Results/collationLength">
									<xsl:value-of select="/Response/CDRs-Unbilled/Results/collationLength" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeStart + /Response/CDRs-Unbilled/Results/rangeLength" />
								</xsl:otherwise>
							</xsl:choose>
							of <xsl:value-of select="/Response/CDRs-Unbilled/Results/collationLength" />
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CDRs-Unbilled/Results/rangePage &lt; /Response/CDRs-Unbilled/Results/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_unbilled.php</xsl:text>
											
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangePage + 1" />
											
											<xsl:if test="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value" />
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
								<xsl:when test="/Response/CDRs-Unbilled/Results/rangePage != /Response/CDRs-Unbilled/Results/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_unbilled.php</xsl:text>
											
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CDRs-Unbilled/Results/rangePages" />
											
											<xsl:if test="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value">
												<xsl:text>&amp;RecordType=</xsl:text>
												<xsl:value-of select="/Response/CDRs-Unbilled/Constraints/Constraint[./Name='RecordType']/Value" />
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
