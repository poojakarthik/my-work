<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	
	<!--TODO!bash! [  DONE  ]		Urgent - do NOT show menu links to the same page!!-->
		<h1>View Service Details</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/note_add.js"></script>
		
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2 class="Account">Account Details</h2>
					<div class="Narrow-Form">
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
						</table>
						
						<div class="Clear"></div>
					</div>
					<div class="Seperator"></div>
					
					
					<h2 class="Service">Service Details</h2>
					<div class="Narrow-Form">
						<table border="0" cellpadding="3" cellspacing="0">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('Id')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:value-of select="/Response/Service/Id" />
								</td>
							</tr>
							<tr>
								<td colspan="2"><div class="MicroSeperator"></div></td>
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
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('ServiceType')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:value-of select="/Response/Service/ServiceTypes/ServiceType[@selected='selected']/Name" />
								</td>
							</tr>
							<xsl:if test="/Response/Service/ServiceType = 102">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('Indial100')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Service/Indial100 = 1">
												<strong><span class="Green">Yes</span></strong>
											</xsl:when>
											<xsl:otherwise>
												No
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('ELB')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Service/ELB = 1">
												<strong><span class="Green">Yes</span></strong>
											</xsl:when>
											<xsl:otherwise>
												No
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
							</xsl:if>
							<tr>
								<td colspan="2"><div class="MicroSeperator"></div></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('CreatedOn')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:choose>
										<xsl:when test="/Response/Service/CreatedOn/year">
											<xsl:call-template name="dt:format-date-time">
												<xsl:with-param name="year"		select="/Response/Service/CreatedOn/year" />
												<xsl:with-param name="month"	select="/Response/Service/CreatedOn/month" />
												<xsl:with-param name="day"		select="/Response/Service/CreatedOn/day" />
												<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
											</xsl:call-template>
										</xsl:when>
										<xsl:otherwise>
											<strong><span class="Attention">No Date Specified</span></strong>
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('ClosedOn')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:choose>
										<xsl:when test="/Response/Service/ClosedOn/year">
											<xsl:call-template name="dt:format-date-time">
												<xsl:with-param name="year"	select="/Response/Service/ClosedOn/year" />
												<xsl:with-param name="month"	select="/Response/Service/ClosedOn/month" />
												<xsl:with-param name="day"		select="/Response/Service/ClosedOn/day" />
												<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
											</xsl:call-template>
										</xsl:when>
										<xsl:otherwise>
											No Close Pending
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
							<tr>
								<td colspan="2"><div class="MicroSeperator"></div></td>
							</tr>
							<xsl:if test="/Response/CostCentre">
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('CostCentre')" />
										</xsl:call-template>
									</th>
									<td>
						       			<xsl:value-of select="/Response/CostCentre/Name" />
							       	</td>
								</tr>
								<tr>
									<td colspan="2"><div class="MicroSeperator"></div></td>
								</tr>
							</xsl:if>
							<tr>
								<th>
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('UnbilledCharges')" />
									</xsl:call-template>
								</th>
								<td>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/Service/UnbilledCharges-Cost-Current" />
										<xsl:with-param name="Decimal" select="number('4')" />
			       					</xsl:call-template>
						       	</td>
							</tr>
							<tr>
								<th>
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('Plan')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:choose>
										<xsl:when test="/Response/Service/RatePlan">
											<xsl:value-of select="/Response/Service/RatePlan/Name" />
										</xsl:when>
										<xsl:otherwise>
											<strong><span class="Attention">No Plan Assigned</span></strong>
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
							<xsl:choose>
								<xsl:when test="/Response/InboundDetail">
									<tr>
										<th>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Service Inbound')" />
												<xsl:with-param name="field" select="string('AnswerPoint')" />							
											</xsl:call-template>
										</th>
										<td>
											<xsl:choose>
												<xsl:when test="/Response/InboundDetail/AnswerPoint">
													<xsl:value-of select="/Response/InboundDetail/AnswerPoint" />
												</xsl:when>
											</xsl:choose>
										</td>
									</tr>
									<tr>
										<th>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Service Inbound')" />
												<xsl:with-param name="field" select="string('Config')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:choose>
												<xsl:when test="/Response/InboundDetail/Config">
													<xsl:value-of select="/Response/InboundDetail/Config" />											
												</xsl:when>
												<xsl:otherwise>
													<span>None</span>
												</xsl:otherwise>
											</xsl:choose>
										</td>
									</tr>										
								</xsl:when>
							</xsl:choose>
						</table>
					</div>
					<div class="LinkEdit">
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_edit.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
							Edit Service Details
						</a>
					</div>

					

					<!-- Add Adjustment -->
					<h2 class="Adjustment">Add Adjustment</h2>
					
					<div class="Narrow-Form">
						<xsl:choose>
							<xsl:when test="count(/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType) = 0">
								No adjustments are available.
							</xsl:when>
							<xsl:otherwise>
								<table border="0" cellpadding="3" cellspacing="0">
									<xsl:if test="count(/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType[./Nature='CR']) != 0">
										<form method="post" action="service_charge_add.php">
											<input type="hidden" name="Service">
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/Service/Id" />
												</xsl:attribute>
											</input>
											<tr>
												<th class="JustifiedWidth">Credit Adjustment :</th>
												<td>
													<select name="ChargeType">
														<xsl:for-each select="/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType[./Nature='CR']">
															<option>
																<xsl:attribute name="value">
																	<xsl:text></xsl:text>
																	<xsl:value-of select="./Id" />
																</xsl:attribute>
																<xsl:value-of select="./Description" />
															</option>
														</xsl:for-each>
													</select>
												</td>
												<td>
													<input type="submit" value="Add &#0187;" class="input-submit" />
												</td>
											</tr>
										</form>
									</xsl:if>
									<xsl:if test="count(/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType[./Nature='DR']) != 0">
										<form method="post" action="service_charge_add.php">
											<input type="hidden" name="Service">
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/Service/Id" />
												</xsl:attribute>
											</input>
											<tr>
												<th class="JustifiedWidth">Debit Adjustment :</th>
												<td>
													<select name="ChargeType">
														<xsl:for-each select="/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType[./Nature='DR']">
															<option>
																<xsl:attribute name="value">
																	<xsl:text></xsl:text>
																	<xsl:value-of select="./Id" />
																</xsl:attribute>
																<xsl:value-of select="./Description" />
															</option>
														</xsl:for-each>
													</select>
												</td>
												<td>
													<input type="submit" value="Add &#0187;" class="input-submit" />
												</td>
											</tr>
										</form>
									</xsl:if>
								</table>
							</xsl:otherwise>
						</xsl:choose>
					</div>
					
					<div class="Seperator"></div>
					
					<!--Add Recurring Adjustment -->
					<h2 class="Adjustment">Add Recurring Adjustment</h2>
					
					<div class="Narrow-Form">
						<xsl:choose>
							<xsl:when test="count(/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType) = 0">
								No recurring adjustments are available.
							</xsl:when>
							<xsl:otherwise>
								<table border="0" cellpadding="3" cellspacing="0">
									<xsl:if test="count(/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType[./Nature='CR']) != 0">
										<form method="post" action="service_recurringcharge_add.php">
											<input type="hidden" name="Service">
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/Service/Id" />
												</xsl:attribute>
											</input>
											<tr>
												<th class="JustifiedWidth">
													Credit Adjustment :
												</th>
												<td>
													<select name="RecurringChargeType">
														<xsl:for-each select="/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType[./Nature='CR']">
															<option>
																<xsl:attribute name="value">
																	<xsl:text></xsl:text>
																	<xsl:value-of select="./Id" />
																</xsl:attribute>
																<xsl:value-of select="./Description" />
															</option>
														</xsl:for-each>
													</select>
												</td>
												<td>
													<input type="submit" value="Add &#0187;" class="input-submit" />
												</td>
											</tr>
										</form>
									</xsl:if>
									<xsl:if test="count(/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType[./Nature='DR']) != 0">
										<form method="post" action="service_recurringcharge_add.php">
											<input type="hidden" name="Service">
												<xsl:attribute name="value">
													<xsl:text></xsl:text>
													<xsl:value-of select="/Response/Service/Id" />
												</xsl:attribute>
											</input>
											<tr>
												<th class="JustifiedWidth">
													Debit Adjustment :
												</th>
												<td>
													<select name="RecurringChargeType">
														<xsl:for-each select="/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType[./Nature='DR']">
															<option>
																<xsl:attribute name="value">
																	<xsl:text></xsl:text>
																	<xsl:value-of select="./Id" />
																</xsl:attribute>
																<xsl:value-of select="./Description" />
															</option>
														</xsl:for-each>
													</select>
												</td>
												<td>
													<input type="submit" value="Add &#0187;" class="input-submit" />
												</td>
											</tr>
										</form>
									</xsl:if>
								</table>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</td>
				
				<!-- column spacer -->
				<td class="ColumnSpacer"></td>
				
				<!-- second column -->
				<td valign="top">
				<!--Service Options-->
					<h2 class="Options">Service Options</h2>
					<ul>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_unbilled.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:text>View Unbilled Charges</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>recurring_charge_list.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:text>View Recurring Adjustments</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_edit.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								Edit Service Details
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_plan.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:text>Change Plan</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_lessee.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:text>Change of Lessee</xsl:text>
							</a>
						</li>
						<xsl:if test="/Response/Service/ServiceType = 102">
							<li>
								<a>
									<xsl:attribute name="href">
										<xsl:text>service_address.php?Service=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" />
									</xsl:attribute>
									<xsl:text>Provisioning</xsl:text>
								</a>
							</li>
						</xsl:if>
						
					</ul>
					
					<div class="Seperator"></div>
				
					<!-- notes -->
					<h2 class="Notes">Service Notes</h2>
					
					<form method="post" action="note_add.php" onsubmit="return noteAdd (this)" name="NoteAdd">
						<input type="hidden" name="AccountGroup">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/AccountGroup" />
							</xsl:attribute>
						</input>
						<input type="hidden" name="Service">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
						</input>
						Type new note for this service in the field below:
						<textarea name="Note" class="input-summary" rows="6" />
						
						<div>
							<input type="checkbox" name="Account" CHECKED="CHECKED">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
							</input>
							Show this note in Account Notes.
						</div>
						
						<select class="Left" name="NoteType">
							<xsl:for-each select="/Response/NoteTypes/NoteType">
								<option>
									<xsl:attribute name="style">
										<xsl:text>background-color: #</xsl:text>
										<xsl:value-of select="./BackgroundColor" />
										<xsl:text>;</xsl:text>
										
										<xsl:text>border: solid 1px #</xsl:text>
										<xsl:value-of select="./BorderColor" />
										<xsl:text>;</xsl:text>
										
										<xsl:text>color: #</xsl:text>
										<xsl:value-of select="./TextColor" />
										<xsl:text>;</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:value-of select="./TypeLabel" />
								</option>
							</xsl:for-each>
						</select>
						
						<div class="Right">
							<input type="submit" value="Add Note &#0187;" class="input-submit-disabled" disabled="disabled" />
						</div>
						<div class="Clear"></div>
					</form>
					
					<!-- recent notes -->
					<h3>Recent Notes</h3>
					<xsl:choose>
						<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
							There are no notes currently attached to this Service.
						</xsl:when>
						<xsl:otherwise>
							The 5 most recent notes are listed below:
							<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
								<xsl:variable name="Note" select="." />
								<div class="SmallSeperator"></div>
								<div class="Note">
									<xsl:attribute name="style">
										<xsl:text>background-color: #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BackgroundColor" />
										<xsl:text>;</xsl:text>
										
										<xsl:text>border: solid 1px #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BorderColor" />
										<xsl:text>;</xsl:text>
										
										<xsl:text>color: #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/TextColor" />
										<xsl:text>;</xsl:text>
									</xsl:attribute>
									
									<div class="small">
										Created on 
											<strong>
												<xsl:call-template name="dt:format-date-time">
													<xsl:with-param name="year"	select="./Datetime/year" />
													<xsl:with-param name="month"	select="./Datetime/month" />
													<xsl:with-param name="day"		select="./Datetime/day" />
							 						<xsl:with-param name="hour"	select="./Datetime/hour" />
													<xsl:with-param name="minute"	select="./Datetime/minute" />
													<xsl:with-param name="second"	select="./Datetime/second" />
													<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
												</xsl:call-template>
											</strong>
										by
											<strong>
												<xsl:value-of select="./Employee/FirstName" />
												<xsl:text> </xsl:text>
												<xsl:value-of select="./Employee/LastName" />
											</strong>.
									</div>
									<div class="Seperator"></div>
									
									<xsl:value-of select="./Note" disable-output-escaping="yes" />
								</div>
							</xsl:for-each>
							<div class="Right">
								<a href="#" title="Service Notes" alt="Notes for this Service">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, 'note_list.php?Service=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View All Service Notes</xsl:text>
								</a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					<div class="Seperator"></div>
				</td>
			</tr>
		</table>
		<div class="Clear"></div>
	</xsl:template>
</xsl:stylesheet>
