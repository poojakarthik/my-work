<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<!--TODO!bash! [  DONE  ]		URGENT - adding a recurring charge does not work - not being added to db-->		
		<h1>Add Recurring Adjustment Type</h1>
		
		<h2 class= "Adjustment">Adjustment Details</h2>
		<form method="POST" action="charges_recurringcharge_add.php">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'CType-Blank'">
							Please enter an Adjustment Code.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Descr-Blank'">
							Please enter an Adjustment Description.
						</xsl:when>
						<xsl:when test="/Response/Error = 'CType-Exists'">
							The Recurring Adjustment Code you entered already exists.  Please enter a unique Recurring Adjustment Code.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Frequency'">
							Please enter a Recurring Frequency.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Zero Freq'">
							The Recurring Frequency must be more than zero.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Nature'">
							Please select a valid Nature.
						</xsl:when>
						<xsl:when test="/Response/Error = 'RecursionCharge Invalid'">
							Please enter a valid Recursion Charge.
						</xsl:when>
						<xsl:when test="/Response/Error = 'MinCharge Invalid'">
							Please enter a valid Minimum Charge.
						</xsl:when>
						<xsl:when test="/Response/Error = 'CancellationFee Invalid'">
							Please enter a valid Cancellation Charge.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('ChargeType')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ChargeType" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RecurringChargeType/ChargeType" />
									</xsl:attribute>
									<xsl:attribute name='maxlength'>10</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Description" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RecurringChargeType/Description" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('RecursionCharge')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="RecursionCharge" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
						       			<xsl:call-template name="Currency">
						       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/RecursionCharge" />
											<xsl:with-param name="Decimal" select="number('2')" />
				       					</xsl:call-template>
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('Nature')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Nature">
									<xsl:for-each select="/Response/RecurringChargeType/Natures/Nature">
										<option value="DR">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="./@selected='selected'">
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
						<tr>
							<td colspan="3">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
						<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('RecurringFrequency')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="RecurringFreq" class="input-string2" size="1">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RecurringChargeType/RecurringDate" />
									</xsl:attribute>
								</input>
								<xsl:text> </xsl:text>
								<select name="RecurringFreqType">
									<xsl:for-each select="/Response/RecurringChargeType/BillingFreqTypes/BillingFreqType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="./@selected='selected'">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:text></xsl:text>
											<xsl:value-of select="./Name" />(s)
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
						<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('MinCharge')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="MinCharge" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
						       			<xsl:call-template name="Currency">
						       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/MinCharge" />
											<xsl:with-param name="Decimal" select="number('2')" />
				       					</xsl:call-template>
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
						<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('CancellationFee')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="CancellationFee" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
						       			<xsl:call-template name="Currency">
						       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/CancellationFee" />
											<xsl:with-param name="Decimal" select="number('2')" />
				       					</xsl:call-template>
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
						<td></td>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
						<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('Continuable')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="checkbox" name="Continuable" value="1" id="RecurringChargeType:Continuable">
												<xsl:if test="/Response/RecurringChargeType/Continuable = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="RecurringChargeType:Continuable">
												Yes, keep charging for this after the Minimum Charged is reached.
											</label>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
						<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('PlanCharge')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="checkbox" name="PlanCharge" value="1" id="PlanCharge:TRUE">
												<xsl:if test="/Response/RecurringChargeType/PlanCharge = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="PlanCharge:TRUE">
												Yes, this Recurring Adjustment is Specifically for Plans.
											</label>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
						<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('UniqueCharge')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="checkbox" name="UniqueCharge" value="1" id="UniqueCharge:TRUE">
												<xsl:if test="/Response/RecurringChargeType/UniqueCharge = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="UniqueCharge:TRUE">
												Yes, this is a unique Adjustment.
											</label>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
						<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('Fixed')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="checkbox" name="Fixed" value="1" id="RecurringChargeType:Fixed">
												<xsl:if test="/Response/RecurringChargeType/Fixed = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="RecurringChargeType:Fixed">
												Yes, make this fixed so it cannot be changed at application time.
											</label>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<div class="Clear"></div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class = "Right">
			<input type="submit" value="Create Recurring Adjustment &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
