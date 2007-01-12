<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>Recurring Charge Details</h1>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('FNN')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/RecurringCharge/Service/FNN" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('ChargeType')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/RecurringCharge/ChargeType" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('Description')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/RecurringCharge/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('CreatedOn')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"		select="/Response/RecurringCharge/CreatedOn/year" />
								<xsl:with-param name="month"	select="/Response/RecurringCharge/CreatedOn/month" />
								<xsl:with-param name="day"		select="/Response/RecurringCharge/CreatedOn/day" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
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
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('RecursionCharge')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/RecurringCharge/RecursionCharge" />
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('RecurringFrequency')" />
							</xsl:call-template>
						</th>
						<td>
							Every <xsl:value-of select="/Response/RecurringCharge/RecurringFreq" />
							<xsl:text> </xsl:text>
							<xsl:value-of select="/Response/RecurringCharge/BillingFreqTypes/BillingFreqType[@selected='selected']/Name" />(s)
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('MinCharge')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/RecurringCharge/MinCharge" />
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
								<xsl:with-param name="field" select="string('CancellationFee')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/RecurringCharge/CancellationFee" />
						</td>
					</tr>
				</table>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
