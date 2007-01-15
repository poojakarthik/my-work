<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Service Details</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/notes_popup.js"></script>
		<script language="javascript" src="js/provisioning_popup.js"></script>
		
		<div class="Seperator"></div>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2 class="Account">Account Details</h2>
					<div class="Filter-Form">
						
						
						<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
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
						
						<div class="Clear"></div>
					</div>
					<div class="Seperator"></div>
					
					<h2 class="Service">Service Details</h2>
					
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
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
									<td colspan="2"><div class="Seperator"></div></td>
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
													<strong><span class="Green">Indial 100 Support</span></strong>
												</xsl:when>
												<xsl:otherwise>
													<strong><span class="Red">No Indial 100 Support</span></strong>
												</xsl:otherwise>
											</xsl:choose>
										</td>
									</tr>
								</xsl:if>
								<tr>
									<td colspan="2"><div class="Seperator"></div></td>
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
												<strong><span class="Green">No Close Pending</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
								<tr>
									<td colspan="2"><div class="Seperator"></div></td>
								</tr>
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('UnbilledCharges')" />
										</xsl:call-template>
									</th>
									<td><xsl:value-of select="/Response/Service/UnbilledCharges-Cost-Current" /></td>
								</tr>
								<tr>
									<th></th>
									<td>
										<a>
											<xsl:attribute name="href">
												<xsl:text>service_unbilled.php?Id=</xsl:text>
												<xsl:value-of select="/Response/Service/Id" />
											</xsl:attribute>
											<xsl:text>View Charges</xsl:text>
										</a>
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
								<tr>
									<th></th>
									<td>
										<a>
											<xsl:attribute name="href">
												<xsl:text>service_plan.php?Service=</xsl:text>
												<xsl:value-of select="/Response/Service/Id" />
											</xsl:attribute>
											<xsl:text>Plan Details</xsl:text>
										</a>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="LinkEdit">
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_edit.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
							<xsl:attribute name="onclick">
								<xsl:text>return openPopup('service_edit.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
								<xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:text>Edit Service</xsl:text>
						</a>
					</div>
					
					<div class="Clear"></div>
					<div class="Seperator"></div>
					
					<xsl:if test="/Response/Service/ServiceType = 102">
						<h2 class="Provisioning">Provisioning</h2>
						
						<form method="POST" action="provisioning_request.php">
							<input type="hidden" name="Service">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
							</input>
							<div class="Filter-Form">
								<div class="Filter-Form-Content">
									... <a>
										<xsl:attribute name="href">
											<xsl:text>javascript:provisioning_popup_history ('</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											<xsl:text>')</xsl:text>
										</xsl:attribute>
										<xsl:text>View Provisioning History</xsl:text>
									</a><br />
									... <a>
										<xsl:attribute name="href">
											<xsl:text>javascript:provisioning_popup_requests ('</xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
											<xsl:text>')</xsl:text>
										</xsl:attribute>
										<xsl:text>View Provisioning Requests</xsl:text>
									</a><br />
								</div>
							</div>
							
							<div class="Seperator"></div>
							
							<div class="Filter-Form">
								<div class="Filter-Form-Content">
									<table border="0" cellpadding="1" cellspacing="0">
										<tr>
											<th class="JustifiedWidth">
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Carrier')" />
													<xsl:with-param name="field" select="string('CarrierName')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="Carrier">
													<option value=""></option>
													<xsl:for-each select="/Response/Carriers/Carrier">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
															<xsl:value-of select="./Name" />
														</option>
													</xsl:for-each>
												</select>
											</td>
										</tr>
										<tr>
											<th class="JustifiedWidth">
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Provisioning')" />
													<xsl:with-param name="field" select="string('ProvisioningRequestType')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="RequestType">
													<option value=""></option>
													<xsl:for-each select="/Response/ProvisioningRequestTypes/ProvisioningRequestType">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
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
											<th></th>
											<td>
												<input type="submit" value="Perform Request &#0187;" class="input-submit" />
											</td>
										</tr>
									</table>
								</div>
							</div>
						</form>
						
						<div class="Seperator"></div>
						
						<h2 class="Address">Service Address Details</h2>
						
						<div class="Filter-Form">
							<div class="Filter-Form-Content">
								<xsl:if test="not(/Response/Service/ServiceAddress)">
									<strong><span class="Attention">Attention</span> :</strong>
									This service does not have any Service Address details 
									associated with it.
								
									<div class="Clear"></div>
									<div class="Seperator"></div>
								</xsl:if>
								
								<form method="post" action="service_address_apply.php">
									<input type="hidden" name="Service">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/Service/Id" />
										</xsl:attribute>
									</input>
									
									<table border="0" cellpadding="1" cellspacing="0">
										<!-- Bill Information -->
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('BillName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="BillName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/BillName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('BillAddress1')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="BillAddress1" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/BillAddress1" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('BillAddress2')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="BillAddress2" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/BillAddress2" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('BillLocality')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="BillLocality" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/BillLocality" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('BillPostcode')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="BillPostcode" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/BillPostcode" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div class="Seperator"></div>
											</td>
										</tr>
										
										<!-- End User Information -->
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('EndUserTitle')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="EndUserTitle">
													<option></option>
													<xsl:for-each select="/Response/Service/ServiceAddress/ServiceEndUserTitleTypes/ServiceEndUserTitleType">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
															<xsl:if test="@selected='selected'">
																<xsl:attribute name="selected">
																	<xsl:text>selected</xsl:text>
																</xsl:attribute>
															</xsl:if>
															<xsl:text></xsl:text>
															<xsl:value-of select="./Name" />
														</option>
													</xsl:for-each>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('EndUserGivenName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="EndUserGivenName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/EndUserGivenName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('EndUserFamilyName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="EndUserFamilyName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/EndUserFamilyName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('EndUserCompanyName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="EndUserCompanyName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/EndUserCompanyName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('DateOfBirth')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="DateOfBirth[year]">
													<xsl:call-template name="Date_Loop">
														<xsl:with-param name="start" select="number (1900)" />
														<xsl:with-param name="cease" select="number (1990)" />
														<xsl:with-param name="steps" select="number (1)" />
														<xsl:with-param name="select" select="substring (/Response/Service/ServiceAddress/DateOfBirth, 1, 4)" />
													</xsl:call-template>
												</select> -
												<select name="DateOfBirth[month]">
													<xsl:call-template name="Date_Loop">
														<xsl:with-param name="start" select="number (1)" />
														<xsl:with-param name="cease" select="number (12)" />
														<xsl:with-param name="steps" select="number (1)" />
														<xsl:with-param name="select" select="substring (/Response/Service/ServiceAddress/DateOfBirth, 5, 2)" />
													</xsl:call-template>
												</select> -
												<select name="DateOfBirth[day]">
													<xsl:call-template name="Date_Loop">
														<xsl:with-param name="start" select="number (1)" />
														<xsl:with-param name="cease" select="number (31)" />
														<xsl:with-param name="steps" select="number (1)" />
														<xsl:with-param name="select" select="substring (/Response/Service/ServiceAddress/DateOfBirth, 7, 2)" />
													</xsl:call-template>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('Employer')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="Employer" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/Employer" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('Occupation')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="Occupation" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/Occupation" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div class="Seperator"></div>
											</td>
										</tr>
										
										<!-- Company Information -->
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ABN')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ABN" class="input-ABN">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ABN" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('TradingName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="TradingName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/TradingName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div class="Seperator"></div>
											</td>
										</tr>
										
										<!-- Service Information -->
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceAddressType')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="ServiceAddressType">
													<option></option>
													<xsl:for-each select="/Response/Service/ServiceAddress/ServiceAddressTypes/ServiceAddressType">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
															<xsl:if test="@selected='selected'">
																<xsl:attribute name="selected">
																	<xsl:text>selected</xsl:text>
																</xsl:attribute>
															</xsl:if>
															<xsl:text></xsl:text>
															<xsl:value-of select="./Name" />
														</option>
													</xsl:for-each>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceAddressTypeNumber')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceAddressTypeNumber" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceAddressTypeNumber" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceAddressTypeSuffix')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceAddressTypeSuffix" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceAddressTypeSuffix" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceStreetNumberStart')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceStreetNumberStart" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetNumberStart" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceStreetNumberEnd')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceStreetNumberEnd" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetNumberEnd" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceStreetNumberSuffix')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceStreetNumberSuffix" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetNumberSuffix" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceStreetName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceStreetName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceStreetType')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="ServiceStreetType">
													<option></option>
													<xsl:for-each select="/Response/Service/ServiceAddress/ServiceStreetTypes/ServiceStreetType">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
															<xsl:if test="@selected='selected'">
																<xsl:attribute name="selected">
																	<xsl:text>selected</xsl:text>
																</xsl:attribute>
															</xsl:if>
															<xsl:text></xsl:text>
															<xsl:value-of select="./Name" />
														</option>
													</xsl:for-each>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceStreetTypeSuffix')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="ServiceStreetTypeSuffix">
													<option></option>
													<xsl:for-each select="/Response/Service/ServiceAddress/ServiceStreetSuffixTypes/ServiceStreetSuffixType">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
															<xsl:if test="@selected='selected'">
																<xsl:attribute name="selected">
																	<xsl:text>selected</xsl:text>
																</xsl:attribute>
															</xsl:if>
															<xsl:text></xsl:text>
															<xsl:value-of select="./Name" />
														</option>
													</xsl:for-each>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServicePropertyName')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServicePropertyName" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServicePropertyName" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceLocality')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServiceLocality" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServiceLocality" />
													</xsl:attribute>
												</input>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServiceState')" />
												</xsl:call-template>
											</th>
											<td>
												<select name="ServiceState">
													<option></option>
													<xsl:for-each select="/Response/Service/ServiceAddress/ServiceStateTypes/ServiceStateType">
														<option>
															<xsl:attribute name="value">
																<xsl:text></xsl:text>
																<xsl:value-of select="./Id" />
															</xsl:attribute>
															<xsl:if test="@selected='selected'">
																<xsl:attribute name="selected">
																	<xsl:text>selected</xsl:text>
																</xsl:attribute>
															</xsl:if>
															<xsl:text></xsl:text>
															<xsl:value-of select="./Name" />
														</option>
													</xsl:for-each>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Service Address')" />
													<xsl:with-param name="field" select="string('ServicePostcode')" />
												</xsl:call-template>
											</th>
											<td>
												<input type="text" name="ServicePostcode" class="input-string">
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="/Response/Service/ServiceAddress/ServicePostcode" />
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
											<td></td>
											<td>
												<input type="submit" value="Change Details &#0187;" class="input-submit" />
											</td>
										</tr>
									</table>
								</form>
							</div>
						</div>
					</xsl:if>
				</td>
				<td width="30" nowrap="nowrap"></td>
				<td valign="top" width="300">
				
					<h2 class="Options">Service Options</h2>
					<ul>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_view.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View Account</xsl:text>
							</a>
						</li>
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
									<xsl:text>service_plan.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:text>View Plan Details</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_lessee.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:attribute name="onclick">
									<xsl:text>return openPopup('service_lessee.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
									<xsl:text>', 450, 650)</xsl:text>
								</xsl:attribute>
								<xsl:text>Change of Lessee</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>javascript:provisioning_popup_history ('</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
									<xsl:text>')</xsl:text>
								</xsl:attribute>
								<xsl:text>View Provisioning History</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>javascript:provisioning_popup_requests ('</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
									<xsl:text>')</xsl:text>
								</xsl:attribute>
								<xsl:text>View Provisioning Requests</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_edit.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:attribute name="onclick">
									<xsl:text>return openPopup('service_edit.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
									<xsl:text>')</xsl:text>
								</xsl:attribute>
								<xsl:text>Edit Service</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_lessee.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
								</xsl:attribute>
								<xsl:attribute name="onclick">
									<xsl:text>return openPopup('service_lessee.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" />
									<xsl:text>', 450, 650)</xsl:text>
								</xsl:attribute>
								<xsl:text>Change Lessee</xsl:text>
							</a>
						</li>
					</ul>
					
					<div class="Seperator"></div>
				
				
					<h2 class="Notes">Service Notes</h2>
					
					<form method="post" action="note_add.php">
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
							<input type="submit" value="Create Note &#0187;" class="input-submit" />
						</div>
					</form>
					
					<div class="Clear"></div>
					
					<div class="Seperator"></div>
					<h3>Recent Notes</h3>
					<xsl:choose>
						<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
							There are no notes currently attached to this Service.
						</xsl:when>
						<xsl:otherwise>
							The 5 most recent notes are listed below:
							<div class="Right">
								<a>
									<xsl:attribute name="href">
										<xsl:text>javascript:notes_popup('', '', '</xsl:text>
										<xsl:value-of select="/Response/Service/Id" />
										<xsl:text>', '')</xsl:text>
									</xsl:attribute>
									<xsl:text>View All Service Notes</xsl:text>
								</a>
							</div>
							<div class="Seperator"></div>
							<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
								<xsl:variable name="Note" select="." />
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
													<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
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
								<div class="Seperator"></div>
							</xsl:for-each>
						</xsl:otherwise>
					</xsl:choose>
					<div class="Seperator"></div>

					<h2 class="Charge">Add Charges</h2>
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<h2>Single Charge</h2>
							<xsl:choose>
								<xsl:when test="count(/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType) = 0">
									No charges are available.
								</xsl:when>
								<xsl:otherwise>
									<form method="post" action="service_charge_add.php">
										<input type="hidden" name="Service">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/Service/Id" />
											</xsl:attribute>
										</input>
										
										<table border="0" cellpadding="5" cellspacing="0">
											<tr>
												<td>
													<select name="ChargeType">
														<xsl:for-each select="/Response/TemplateChargeTypes/ChargeTypes/Results/rangeSample/ChargeType">
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
											</tr>
											<tr>
												<td>
													<input type="submit" value="Assign Charge &#0187;" class="input-submit" />
												</td>
											</tr>
										</table>
									</form>
								</xsl:otherwise>
							</xsl:choose>
						</div>
						<br />
						
						<div class="Seperator"></div>
						
						<div class="Filter-Form-Content">
							<h2>Recurring Charge</h2>
							<xsl:choose>
								<xsl:when test="count(/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType) = 0">
									No recurring charges are available.
								</xsl:when>
								<xsl:otherwise>
									<form method="post" action="service_recurringcharge_add.php">
										<input type="hidden" name="Service">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/Service/Id" />
											</xsl:attribute>
										</input>
										
										<table border="0" cellpadding="5" cellspacing="0">
											<tr>
												<td>
													<select name="RecurringChargeType">
														<xsl:for-each select="/Response/TemplateChargeTypes/RecurringChargeTypes/Results/rangeSample/RecurringChargeType">
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
											</tr>
											<tr>
												<td>
													<input type="submit" value="Assign Recurring Charge &#0187;" class="input-submit" />
												</td>
											</tr>
										</table>
									</form>
								</xsl:otherwise>
							</xsl:choose>
						</div>
						
						<div class="Clear"></div>
					</div>
					
					<div class="Seperator"></div>
				</td>
			</tr>
		</table>
		<div class="Clear"></div>
	</xsl:template>
	
	
	
	<xsl:template name="Date_Loop">
		<xsl:param name="start">1</xsl:param>
		<xsl:param name="cease">0</xsl:param>
		<xsl:param name="steps">1</xsl:param>
		<xsl:param name="count">0</xsl:param>
		
		<xsl:param name="select">0</xsl:param>
		
		<xsl:if test="number($start) + number($count) &lt;= number($cease)">
			<option>
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="$start + $count" />
				</xsl:attribute>
				
				<xsl:choose>
					<xsl:when test="$select = $start + $count">
						<xsl:attribute name="selected">
							<xsl:text>selected</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:value-of select="$start + $count" />
			</option>
			<xsl:call-template name="Date_Loop">
				<xsl:with-param name="start" select="$start" />
				<xsl:with-param name="cease" select="$cease" />
				<xsl:with-param name="steps" select="$steps" />
				<xsl:with-param name="count" select="$count + $steps" />
				<xsl:with-param name="select" select="$select" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
