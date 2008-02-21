<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../../includes/init.xsl" />
	<xsl:import href="../../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Add Recurring Service Adjustment</h1>

		<!--TODO!bash! URgent! This page needs a menu-->
		<h2 class="Adjustment"> Recurring Adjustment Details</h2>
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
			
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Invalid Amount'">
							You did not enter a valid Amount.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
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
								<div class="MicroSeperator"></div>
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
								<div class="MicroSeperator"></div>
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
									<!--TODO!bash! [  DONE  ]		URGENT only allow a valid amount to be entered as the recursion charge - at the moment this crashes and dies if it's entered wrong -->
									<xsl:when test="/Response/RecurringChargeType/Fixed = 0">
										<input type="text" name="Amount" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
								       			<xsl:call-template name="Currency">
								       				<xsl:with-param name="Number" select="/Response/ui-values/RecursionCharge" />
													<xsl:with-param name="Decimal" select="number('2')" />
						       					</xsl:call-template>
											</xsl:attribute>
										</input>	
									</xsl:when>
									<xsl:otherwise>
						       			<xsl:call-template name="Currency">
						       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/RecursionCharge" />
											<xsl:with-param name="Decimal" select="number('2')" />
				       					</xsl:call-template>
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
							<td>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="/Response/RecurringChargeType/Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="MicroSeperator"></div>
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
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/MinCharge" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
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
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/CancellationFee" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="MicroSeperator"></div>
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
								Every <xsl:value-of select="/Response/RecurringChargeType/RecurringFreq" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="/Response/RecurringChargeType/BillingFreqTypes/BillingFreqType[@selected='selected']/Name" />(s)
							</td>
						</tr>
						<tr>

						</tr>
					</table>
				</div>
			</div>
			<div class = "SmallSeperator"></div>
			<div class = "Right">
					<input type="submit" name="Confirm" value="Add Recurring Adjustment &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
