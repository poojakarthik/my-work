<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Assign a Recurring Charge to a Service</h1>
		
		<form method="post" action="service_recurringcharge_add.php">
			<input type="hidden" name="Service">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="RecurringChargeType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RecurringChargeType/Id" />
				</xsl:attribute>
			</input>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Service/Id" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Service/FNN" /></td>
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
							<td><xsl:value-of select="/Response/RecurringChargeType/ChargeType" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/RecurringChargeType/Description" /></td>
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
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Fixed = 0">
										<input type="text" name="Amount" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/RecurringChargeType/RecursionCharge" />
											</xsl:attribute>
										</input>	
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="/Response/RecurringChargeType/RecursionCharge" />
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('Nature')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/RecurringChargeType/Nature" /></td>
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
									<xsl:with-param name="field" select="string('MinCharge')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/RecurringChargeType/MinCharge" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('CancellationFee')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/RecurringChargeType/CancellationFee" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Frequency')" />
									<xsl:with-param name="field" select="string('BillingFrequency')" />
								</xsl:call-template>
							</th>
							<td>
								Every <xsl:value-of select="/Response/RecurringChargeType/RecurringDate" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="/Response/RecurringChargeType/BillingFreqTypes/BillingFreqType[@selected='selected']/Name" />(s)
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" name="Confirm" value="Assign Charge &#0187;" class="input-submit" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
