<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../../includes/init.xsl" />
	<xsl:import href="../../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!-- Add a Adjustment to a Service -->
		<h1>Add Service Adjustment</h1>
					
		<form method="POST" action="service_charge_add.php">
			<h2 class="Service">Service Adjustment Details</h2>
			
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
					<input type="hidden" name="Service">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
						</xsl:attribute>
					</input>

					<input type="hidden" name="ChargeType">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/ChargeType/Id" />
						</xsl:attribute>
					</input>

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
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('ChargeType')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/ChargeType/ChargeType" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/ChargeType/Description" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Amount')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
								<!--TODO!bash! [  DONE  ]		only allowed valid $ value - this crashes & dies if its entered wrong-->
									<xsl:when test="/Response/ChargeType/Fixed = 0">
										<input type="text" name="Amount" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
								       			<xsl:call-template name="Currency">
								       				<xsl:with-param name="Number" select="/Response/ui-values/Amount" />
													<xsl:with-param name="Decimal" select="number('2')" />
						       					</xsl:call-template>
											</xsl:attribute>
										</input>	
									</xsl:when>
									<xsl:otherwise>
						       			<xsl:call-template name="Currency">
						       				<xsl:with-param name="Number" select="/Response/ChargeType/Amount" />
											<xsl:with-param name="Decimal" select="number('2')" />
				       					</xsl:call-template>
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Nature')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/ChargeType/Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="/Response/ChargeType/Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
							</td>
						</tr>
					</table>
				</div>
				
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" name="Confirm" value="Add Adjustment &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
