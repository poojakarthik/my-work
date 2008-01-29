<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<script language="javascript" src="js/recurring_charge.js" onload="Init()"></script>
		<!-- Add a Adjustment to an Account -->
		<!--TODO!T/N/S! Only make inputs changeable if the adjustment is not fixed -->
		<h1>Add Adjustment</h1>
					
		<form method="POST" action="recurringcharge_assign.php">
			<h2 class="Service">Adjustment Details</h2>
			
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
					<input type="hidden" name="Account">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Account/Id" />
						</xsl:attribute>
					</input>

					<input type="hidden" name="RecurringChargeType">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/RecurringChargeType/Id" />
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
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('ChargeType')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/RecurringChargeType/ChargeType" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
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
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Amount')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Fixed = 0">
										<input type="text" name="Amount" class="input-string" id="Amount" onkeyup="AmountChanged()" autocomplete="off">
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
										<input type="hidden" name="Amount" id="Amount">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
								       			<xsl:call-template name="Currency">
								       				<xsl:with-param name="Number" select="/Response/ui-values/RecursionCharge" />
													<xsl:with-param name="Decimal" select="number('2')" />
						       					</xsl:call-template>
											</xsl:attribute>
										</input>									
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
									<xsl:with-param name="entity" select="string('Charge Type')" />
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
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('MinCharge')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Fixed = 0">
										<input type="text" name="MinCharge" class="input-string" id="MinCharge" onkeyup="TimesChargedChanged()" autocomplete="off">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:call-template name="Currency">
													<xsl:with-param name="Number" select="/Response/RecurringChargeType/MinCharge" />
													<xsl:with-param name="Decimal" select="number('2')" />
												</xsl:call-template>
											</xsl:attribute>
										</input>
									</xsl:when>
									<xsl:otherwise>
										<input type="hidden" name="MinCharge" id="MinCharge">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:call-template name="Currency">
													<xsl:with-param name="Number" select="/Response/RecurringChargeType/MinCharge" />
													<xsl:with-param name="Decimal" select="number('2')" />
												</xsl:call-template>
											</xsl:attribute>
										</input>
										<xsl:call-template name="Currency">
											<xsl:with-param name="Number" select="/Response/RecurringChargeType/MinCharge" />
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
									<xsl:with-param name="field" select="string('CancellationFee')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="Currency">
						       		<xsl:with-param name="Number" select="/Response/RecurringChargeType/CancellationFee" />
									<xsl:with-param name="Decimal" select="number('2')" />
				       			</xsl:call-template></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('RecurringFrequency')" />
								</xsl:call-template>
							</th>
							<td id="recurringfrequency">
								<xsl:value-of select="/Response/RecurringChargeType/RecurringFreq" />
								<xsl:text> </xsl:text>
								<xsl:for-each select="/Response/RecurringChargeType/BillingFreqTypes/BillingFreqType">
									<xsl:if test="./Id=/Response/RecurringChargeType/RecurringFreqType">
										<xsl:value-of select="./Name"/>(s)</xsl:if>
								</xsl:for-each>
							
								<!--
									<input type="text" name="RecurringFreq" class="input-string2" size="1" id="recurringfrequency">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/RecurringChargeType/RecurringFreq" />
										</xsl:attribute>
									</input>
									<xsl:text> </xsl:text>
									<select name="RecurringFreqType" id="recurringfrequencytype">
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
									-->
								</td>							
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('NumberOfTimes')" />
								</xsl:call-template>
								Times Charged
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Fixed = 0">
										<input type="text" name="NumOfCharges" class="input-string" id="NumOfCharges" onkeyup="TimesChargedChanged()" autocomplete="off">
											<xsl:attribute name="value">
												<xsl:text>0</xsl:text>
												<!-- add in the post data-->
												
											</xsl:attribute>
										</input>
									</xsl:when>
									<xsl:otherwise>
						       			<div id="NumOfChargesFixed">0</div>
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Recurring Charge Type')" />
									<xsl:with-param name="field" select="string('LastChargedDate')" />
								</xsl:call-template>
								End Date
							</th>
							<td id="EndDate">
							<!--
								<xsl:call-template name="NearFuture">								
									<xsl:with-param name="Name-Day"			select="string('LastChargedDay')" />
									<xsl:with-param name="Name-Month"		select="string('LastChargedMonth')" />
									<xsl:with-param name="Name-Year"		select="string('LastChargedYear')" />
				       			</xsl:call-template>
								-->
								<xsl:text>
									Invalid data
								</xsl:text>
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
