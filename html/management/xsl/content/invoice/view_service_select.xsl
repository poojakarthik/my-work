<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!--Page for viewing Invoice Details (1/2) -->
		
		<!--TODO!bash! [  DONE  ]		URGENT! The menu needs to return to View invoices and payments page - Do NOT have a link back to the same page!!!!-->
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
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('CreatedOn')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:call-template name="dt:format-date-time">
							<xsl:with-param name="year"		select="/Response/Invoice/CreatedOn/year" />
							<xsl:with-param name="month"	select="/Response/Invoice/CreatedOn/month" />
							<xsl:with-param name="day"		select="/Response/Invoice/CreatedOn/day" />
							<xsl:with-param name="format"	select="'%d/%m/%Y'"/>
						</xsl:call-template>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="Seperator"></div>
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('Credits')" />
						</xsl:call-template>
					</th>
					<td>
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="/Response/Invoice/Credits" />
							<xsl:with-param name="Decimal" select="number('4')" />
       					</xsl:call-template>
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('Debits')" />
						</xsl:call-template>
					</th>
					<td>
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="/Response/Invoice/Debits" />
							<xsl:with-param name="Decimal" select="number('4')" />
       					</xsl:call-template>
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('Amount')" />
						</xsl:call-template>
					</th>
					<td>
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="/Response/Invoice/Total" />
							<xsl:with-param name="Decimal" select="number('4')" />
       					</xsl:call-template>
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('Tax')" />
						</xsl:call-template>
					</th>
					<td>
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="/Response/Invoice/Tax" />
							<xsl:with-param name="Decimal" select="number('4')" />
       					</xsl:call-template>
					</td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Invoice')" />
							<xsl:with-param name="field" select="string('Balance')" />
						</xsl:call-template>
					</th>
					<td>
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="/Response/Invoice/Balance" />
							<xsl:with-param name="Decimal" select="number('4')" />
       					</xsl:call-template>
					</td>
				</tr>
				<xsl:if test="/Response/Invoice/Disputed != 0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Disputed')" />
							</xsl:call-template>
						</th>
						<td>
							<!--TODO!bash! [  DONE  ]		Make this red when disputed and green when resolved!!!!-->
							<strong>
								<span>
									<xsl:choose>
										<xsl:when test="/Response/Invoice/Status = 102">
											<xsl:attribute name="class">
												<xsl:text>Red</xsl:text>
											</xsl:attribute>
											
											Disputed :
										</xsl:when>
										<xsl:otherwise>
											<xsl:attribute name="class">
												<xsl:text>Green</xsl:text>
											</xsl:attribute>
											
											Resolved :
										</xsl:otherwise>
									</xsl:choose>
									
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/Invoice/Disputed" />
										<xsl:with-param name="Decimal" select="number('2')" />
			       					</xsl:call-template>
			       				</span>
			       			</strong>
						</td>
					</tr>
				</xsl:if>
			</table>
		</div>
		
		
		<div class="SmallSeperator"></div>
		<xsl:choose>
			<xsl:when test="/Response/Invoice/Status = 102">
				<div class="MsgNoticeWide">
					This Invoice is currently <strong class="Red">IN DISPUTE</strong>
				</div>
			</xsl:when>
		</xsl:choose>
		
		<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
			<!-- User needs OPERATOR privileges to edit anything -->
			<div class="LinkEdit">
				<xsl:choose>
					<!--TODO!bash! [  DONE  ]		Urgent - INVOICE_SETTLED = show none -->
					<xsl:when test="/Response/Invoice/Status = 103" />
					
					<!--TODO!bash! [  DONE  ]		Urgent - INVOICE_COMMITTED = show dispute -->
					<xsl:when test="/Response/Invoice/Status = 101">
						<!-- Dispute Invoice -->
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_dispute_apply.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Invoice/Id" />
							</xsl:attribute>
							<xsl:text>Dispute Invoice</xsl:text>
						</a>
					</xsl:when>
					
					<!--TODO!bash! [  DONE  ]		Urgent - INVOICE_DISPUTED = show resolve -->	
					<xsl:when test="/Response/Invoice/Status = 102">
						<!-- Resolve Disputed Invoice -->
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_dispute_resolve.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Invoice/Id" />
							</xsl:attribute>
							<xsl:text>Resolve Dispute</xsl:text>
						</a>
					</xsl:when>
					
					<!--TODO!flame! [NOT DONE]		Urgent - other status show ???? -->
					<xsl:otherwise>
						<xsl:text> ???? </xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<div class="Seperator"></div>

		<!-- TODO!bash! [   ??   ] URGENT!!!!!! NO SERVICES ARE SHOWING UP! (This is occurring on the CDR Page) -->
		<!-- Table of Services -->
		<h2 class="Services">Services</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing"  width="100%">
			<tr class="First">
				<th width="30">#</th>
				<!--TODO!bash! [  DONE  ]		URGENT! Do not use 'Line Number' or anything other than 'Service #' anywhere - this has already been done here-->
				<th width="150">Service #</th>
				<th width="110" class="Currency">Charges</th>
				<th width="110" class="Currency">Credit</th>
				<th width="110" class="Currency">Debit</th>
			</tr>
			<xsl:for-each select="/Response/ServiceTotals/Results/rangeSample/ServiceTotal">
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
					
					<td><xsl:value-of select="/Response/ServiceTotals/Results/rangeStart + position()" />.</td>
					<td>
						<a title="View Service Details">
							<xsl:attribute name="href">
								<xsl:text>invoice_view.php?Invoice=</xsl:text>
								<xsl:value-of select="/Response/Invoice/Id" />
								<xsl:text>&amp;ServiceTotal=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:choose>
								<xsl:when test="./FNN=''">
									<span class="Red"><strong>None</strong></span> 
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="./FNN" /> 
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./TotalCharge" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
					</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Credit" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
					</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Debit" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<div class="Seperator"></div>
	</xsl:template>
</xsl:stylesheet>
