<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	<!--TODO!bash! URGENT! The menu needs to return to View invoices and payments page - Do NOT have a link back to the same page!!!!-->
	
		<!--Page for viewing Invoice Details (1/2) -->
		<h1>View Invoice Details</h1>
		
		<!-- Invoice Details -->
		<h2 class="Invoice">Invoice Details</h2>
		<!-- TODO!!!! - show invoice details, TOTALS etc -->
		<!-- TODO!bash! - I won't be able to test this until an billing run is done -->
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
							<xsl:with-param name="field" select="string('Disputed')" />
						</xsl:call-template>
					</th>
					<td>
					
					<!--TODO!bash! Make this red when disputed and green when resolved!!!!-->
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Disputed"/>
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>

					</td>
				</tr>
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
		
		<!--Links-->
		<div class="LinkAdd">
			<a>
				<xsl:attribute name="href">
					<xsl:text>invoice_dispute_apply.php?Id=</xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
				<xsl:text>Dispute Invoice</xsl:text>
			</a>
		</div>
		<div class="LinkEdit">
		<!--TODO!bash! Urgent - why is this link here when the invoice is not in dispute - only show it if it has been disputed, not before, and not after it has been resolved-->
			<a>
				<xsl:attribute name="href">
					<xsl:text>invoice_dispute_resolve.php?Id=</xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
				<xsl:text>Resolve Disputed Invoice</xsl:text>
			</a>
		</div>
		 <!-- TODO!bash! URGENT!!!!!! NO SERVICES ARE SHOWING UP!!!!!!!-->
		<!-- Table of Services -->
		<h2 class="Services">Services</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing"  width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th width="150">Line Number</th>
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
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_view.php</xsl:text>
								<xsl:text>?Invoice=</xsl:text><xsl:value-of select="/Response/Invoice/Id" />
								<xsl:text>&amp;ServiceTotal=</xsl:text><xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./FNN" />
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

		

		
		<div class="Seperator"></div>
	</xsl:template>
</xsl:stylesheet>
