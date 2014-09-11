<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!-- Add a Adjustment to a Service -->
		<h1>Add Adjustment</h1>
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Invalid Amount'">
						You did not enter a valid Amount.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
					
		<form method="POST" action="charges_charge_assign.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			<xsl:if test="/Response/Service">
				<input type="hidden" name="Service">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/Service/Id" />
					</xsl:attribute>
				</input>
			</xsl:if>
			
			<h2 class="Adjustment">Adjustment Details</h2>
			
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
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/Id" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/TradingName" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<xsl:if test="/Response/Service">
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
						</xsl:if>
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
								<strong>
									<xsl:choose>
										<xsl:when test="/Response/ChargeType/Nature = 'DR'">
											<span class="Blue">Debit</span>
										</xsl:when>
										<xsl:when test="/Response/ChargeType/Nature = 'CR'">
											<span class="Green">Credit</span>
										</xsl:when>
									</xsl:choose>
								</strong>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Invoice')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Invoice">
									<option value="">No Association</option>
									<xsl:for-each select="/Response/Invoices/Record">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Id" />
											
											<xsl:text> [</xsl:text>
											<!--<xsl:call-template name="dt:format-date-time">
												<xsl:with-param name="year"		select="./CreatedOn/year" />
												<xsl:with-param name="month"	select="./CreatedOn/month" />
												<xsl:with-param name="day"		select="./CreatedOn/day" />
												<xsl:with-param name="format"	select="'%b %d, %Y'"/>
											</xsl:call-template>-->
											<xsl:value-of select="./CreatedOn" />
											<xsl:text>]</xsl:text>
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Notes')" />
								</xsl:call-template>
							</th>
							<td>
								<textarea name="Notes" class="input-summary" rows="6" cols="60"></textarea>
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
