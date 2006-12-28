<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Recurring Charge Type</h1>
		
		<form method="POST" action="charges_recurringcharge_add.php">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'CType-Blank'">
							Your Recurring Charge Code must not be Blank. Please try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Descr-Blank'">
							Your Recurring Charge Description must not be Blank. Please try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'CType-Exists'">
							Your Recurring Charge Code already exists in the Database for an Unarchived Item. Please try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Frequency'">
							Invalid selection of a Recurring Frequency.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Nature'">
							You did not select a valid nature. Please select a valid nature and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'RecursionCharge Invalid'">
							You did not select a valid value for your Recursion Charge. Please check this and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'MinCharge Invalid'">
							You did not select a valid value for your Minimum Charge. Please check this and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'CancellationFee Invalid'">
							You did not select a valid value for your Cancellation Fee. Please check this and try again.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
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
								</input>
							</td>
						</tr>
						<tr>
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
								<input type="text" name="RecursionCharge" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RecurringChargeType/RecursionCharge" />
									</xsl:attribute>
								</input>
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
							<td colspan="2">
								<div class="Seperator"></div>
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
								<xsl:text>Every </xsl:text>
								<input type="text" name="RecurringDate" class="input-string" size="1">
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
							<td>
								<input type="text" name="MinCharge" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RecurringChargeType/MinCharge" />
									</xsl:attribute>
								</input>
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
								<input type="text" name="CancellationFee" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RecurringChargeType/CancellationFee" />
									</xsl:attribute>
								</input>
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
									<xsl:with-param name="field" select="string('Continuable')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="checkbox" name="Continuable" class="input-string" value="1" id="RecurringChargeType:Continuable">
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
											<input type="checkbox" name="Fixed" class="input-string" value="1" id="RecurringChargeType:Fixed">
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
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Recurring Charge &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
