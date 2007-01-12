<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:import href="../../lib/date-difference/date.difference.function.xsl"/>
	<xsl:import href="../../lib/date-difference/date.difference.template.xsl"/>
	
	<xsl:template name="Content">
		<h1>Account Ledger</h1>
		<div class="Seperator"></div>
		
		<h2 class="Account">Account Information</h2>
		<div class="Seperator"></div>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
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
		</div>
		<div class="Seperator"></div>
		
		<h2>Invoices</h2>
		<div class="Seperator"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Invoice Number</th>
				<th class="Currency" width="120">Overdue</th>
				<th class="Currency" width="120">Credits</th>
				<th class="Currency" width="120">Debits</th>
				<th class="Currency" width="120">Total</th>
				<th class="Currency" width="120">Balance</th>
				<th class="Currency" width="120">Disputed</th>
			</tr>
			<xsl:for-each select="/Response/Invoices/Results/rangeSample/Invoice">
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
								<xsl:text>invoice_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							Invoice #<xsl:value-of select="./Id" />
						</a>
					</td>
					<td class="Currency"><xsl:value-of select="./AccountBalance" /></td>
					<td class="Currency"><xsl:value-of select="./Credits" /></td>
					<td class="Currency"><xsl:value-of select="./Debits" /></td>
					<td class="Currency"><xsl:value-of select="./Total" /></td>
					<td class="Currency">
						<strong>
							<span>
								<xsl:attribute name="class">
									<xsl:choose>
										<xsl:when test="./Balance != '$0.00'">
											<xsl:text>Red</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>Green</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
								<xsl:value-of select="./Balance" />
							</span>
						</strong>
					</td>
					<td class="Currency">
						<xsl:choose>
							<xsl:when test="./Disputed != '$0.00'">
								<strong><span class="Red"><xsl:value-of select="./Disputed" /></span></strong>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="./Disputed" />
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
		
		<h2>PDF Files</h2>
		<div class="Seperator"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="50%">
			<tr class="First">
				<th width="30">#</th>
				<th>Bill Month</th>
				<th>Bill Year</th>
				<th>Bill Name</th>
				<th>Options</th>
			</tr>
			<xsl:for-each select="/Response/Invoices-PDFs/Invoice-PDF">
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
					
					<td><xsl:value-of select="position()" /></td>
					<td>
						<xsl:value-of select="./Month" />
						(<xsl:choose>
							<xsl:when test="./Month = 1"><xsl:text>January</xsl:text></xsl:when>
							<xsl:when test="./Month = 2"><xsl:text>February</xsl:text></xsl:when>
							<xsl:when test="./Month = 3"><xsl:text>March</xsl:text></xsl:when>
							<xsl:when test="./Month = 4"><xsl:text>April</xsl:text></xsl:when>
							<xsl:when test="./Month = 5"><xsl:text>May</xsl:text></xsl:when>
							<xsl:when test="./Month = 6"><xsl:text>June</xsl:text></xsl:when>
							<xsl:when test="./Month = 7"><xsl:text>July</xsl:text></xsl:when>
							<xsl:when test="./Month = 8"><xsl:text>August</xsl:text></xsl:when>
							<xsl:when test="./Month = 9"><xsl:text>September</xsl:text></xsl:when>
							<xsl:when test="./Month = 10"><xsl:text>October</xsl:text></xsl:when>
							<xsl:when test="./Month = 11"><xsl:text>November</xsl:text></xsl:when>
							<xsl:when test="./Month = 12"><xsl:text>December</xsl:text></xsl:when>
						</xsl:choose>)
					</td>
					<td><xsl:value-of select="./Year" /></td>
					<td><xsl:value-of select="./Name" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_pdf.php</xsl:text>
								<xsl:text>?Account=</xsl:text><xsl:value-of select="./Account" />
								<xsl:text>&amp;Year=</xsl:text><xsl:value-of select="./Year" />
								<xsl:text>&amp;Month=</xsl:text><xsl:value-of select="./Month" />
							</xsl:attribute>
							View PDF
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
