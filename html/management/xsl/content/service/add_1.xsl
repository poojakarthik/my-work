<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<!--TODO!bash! [  DONE  ]		URGENT! This page needs a menu!-->
	<xsl:template name="Content">
	
		<!--This page is used to add a service to an account (Part 1/2)-->
		
		<h1>Add Service</h1>
		<script language="javascript" src="js/service_add_input.js" onload="Init()"></script>
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/provisioning.js"></script>
		<script language="javascript" src="js/ajax.js"></script>
		
		<form method="POST" action="service_addbulk.php" onsubmit="return Validate()">
			<input type="hidden" name="Account" id="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<!-- Account Details-->
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
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
				</div>
			</div>
			<div class="Seperator"></div>
			<!-- Service Details -->
			<h2 class="Service">Service Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0" id="thetable">
					<select style="display:none" id="hiddenCostCentres">
						<option value=""></option>
							<xsl:for-each select="/Response/CostCentres/Record/Id">
								<xsl:sort select="./Name" />						
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="../Id" />
										</xsl:attribute>
										<xsl:value-of select="../Name" />
									</option>
							</xsl:for-each>
					</select>
					<select style="display:none" id="hiddenPlans100">
						<option value=""></option>
								<xsl:for-each select="/Response/RatePlans/Results/rangeSample/RatePlan/ServiceTypes/ServiceType">
									<xsl:if test="Id='100' and attribute::selected='selected'">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="../../Id" />
										</xsl:attribute>
										<xsl:value-of select="../../Name" />
									</option>
									</xsl:if>
								</xsl:for-each>
					</select>
					<select style="display:none" id="hiddenPlans101">
						<option value=""></option>
							<xsl:for-each select="/Response/RatePlans/Results/rangeSample/RatePlan/ServiceTypes/ServiceType">
								<xsl:if test="Id='101' and attribute::selected='selected'">
								<option>
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="../../Id" />
									</xsl:attribute>
									<xsl:value-of select="../../Name" />
								</option>
								</xsl:if>
							</xsl:for-each>
					</select>
					<select style="display:none" id="hiddenPlans102">
						<option value=""></option>
							<xsl:for-each select="/Response/RatePlans/Results/rangeSample/RatePlan/ServiceTypes/ServiceType">
								<xsl:if test="Id='102' and attribute::selected='selected'">
								<option>
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="../../Id" />
									</xsl:attribute>
									<xsl:value-of select="../../Name" />
								</option>
								</xsl:if>
							</xsl:for-each>
					</select>
					<select style="display:none" id="hiddenPlans103">
						<option value=""></option>
							<xsl:for-each select="/Response/RatePlans/Results/rangeSample/RatePlan/ServiceTypes/ServiceType">
								<xsl:if test="Id='103' and attribute::selected='selected'">
								<option>
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="../../Id" />
									</xsl:attribute>
									<xsl:value-of select="../../Name" />
								</option>
								</xsl:if>
							</xsl:for-each>
					</select>
					<tbody id="inputs">
						<tr>
						<td></td>
						<th class="JustifiedWidth" style='width:120px'>
							<strong><span class="Red">*</span></strong>
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('FNN')" />
							</xsl:call-template>
						</th>
						<th class="JustifiedWidth" style='width:120px'>
						<strong><span class="Red">*</span></strong>
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('RepeatFNN')" />
							</xsl:call-template>
						</th>
						<th id='servicetype'></th>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('CostCentre')" />
							</xsl:call-template>
						</th>
						<th class="JustifiedWidth">
						<strong><span class="Red">*</span></strong>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Plan')" />
									<xsl:with-param name="field" select="string('SelectPlan')" />
								</xsl:call-template>
							</th>
						<th class="JustifiedWidth">
						<strong><span class="Red"></span></strong>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('Indial100')" />
								</xsl:call-template>
							</th>
						<th class="JustifiedWidth">
						<strong><span class="Red"></span></strong>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ELB')" />
								</xsl:call-template>
							</th>
						</tr>
					</tbody>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			
			<div class="Right">
				<!-- <input type="button" value="Fill Provisioning Data" class="input-submit" onclick="Test();"/> -->
				<input type="button" value="More" class="input-submit" onclick="AddManyInput(1);"/>
				<input type="button" value="Submit &#0187;" class="input-submit" onclick="Submit();"/>
			</div>
		
		<div class="Seperator"></div>
		<div class="Seperator"></div>
		
		<!-- Service Address Details -->	
		
		<div id="provisioningDetails">
			<input type="hidden" name="Service">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
			</input>
			
			<h2 class="Address">Service Address Details</h2>
		
			<div class="Narrow-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr id="Residential">
						<td class="Required" valign="top"></td>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('Residential')" />
							</xsl:call-template>
						</th>
						<td>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="radio" name="Residential" id="Residential:FALSE" value="0" onclick="ShowBusiness()">
											<xsl:if test="/Response/ServiceAddress/Residential = 0">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:if test="not(boolean(/Response/ServiceAddress/Residential))">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="Residential:FALSE">
											Business
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="Residential" id="Residential:TRUE" value="1" onclick="ShowResidential()">
											<xsl:if test="/Response/ServiceAddress/Residential = 1">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="Residential:TRUE">
											Residential
										</label>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="SmallSeperator"></div>
			
			<div class="Narrow-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>

						<td class="Required"><span class="Red"><strong>*</strong></span></td>

						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('BillName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="BillName" id="BillName" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/BillName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>

						<td class="Required"><span class="Red"><strong>*</strong></span></td>

						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('BillAddress1')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="BillAddress1" id="BillAddress1" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/BillAddress1" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>

						<td class="Required"></td>

						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('BillAddress2')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="BillAddress2" id="BillAddress2" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/BillAddress2" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>

						<td class="Required"><span class="Red"><strong>*</strong></span></td>

						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('BillLocality')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="BillLocality" id="BillLocality" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/BillLocality" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<!--TODO!bash! URGENT verify postcode - only 4 digit number-->
					<tr>

						<td class="Required"><span class="Red"><strong>*</strong></span></td>

						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('BillPostcode')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="BillPostcode" id="BillPostcode" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/BillPostcode" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="SmallSeperator"></div>
			
			<div class="Narrow-Form" id="ResidentialSpecific">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('EndUserTitle')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="EndUserTitle" id="EndUserTitle">
								<option></option>
								<xsl:for-each select="/Response/TitleTypes/TitleType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ServiceAddress/EndUserTitle = ./Id">
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
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('EndUserGivenName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="EndUserGivenName" id="EndUserGivenName" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/EndUserGivenName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('EndUserFamilyName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="EndUserFamilyName" id="EndUserFamilyName" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/EndUserFamilyName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<!--TODO!bash! Urgent! do not show dates which allow the person to be <18-->
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('DateOfBirth')" />
							</xsl:call-template>
						</th>
						<td id="DOB">
							<xsl:call-template name="DOB">
								<xsl:with-param name="Name-Day"			select="string('DateOfBirth[day]')" />
								<xsl:with-param name="Name-Month"		select="string('DateOfBirth[month]')" />
								<xsl:with-param name="Name-Year"		select="string('DateOfBirth[year]')" />
								<xsl:with-param name="Selected-Day"		select="substring (/Response/ServiceAddress/DateOfBirth, 7, 2)" />
								<xsl:with-param name="Selected-Month"	select="substring (/Response/ServiceAddress/DateOfBirth, 5, 2)" />
								<xsl:with-param name="Selected-Year"	select="substring (/Response/ServiceAddress/DateOfBirth, 1, 4)" />
								<xsl:with-param name="Now"				select="/Response/Now" />
								<xsl:with-param name="Minimum-Age"		select="18" />
							</xsl:call-template>
						</td>
					</tr>
					<tr>
						<td class="Required"></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('Employer')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Employer" id="Employer" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/Employer" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required"></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('Occupation')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="Occupation" id="Occupation" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/Occupation" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			
			<!-- <div class="SmallSeperator"></div> -->
			
			<div class="Narrow-Form" id="BusinessSpecific">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ABN')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ABN" id="ABN" class="input-ABN">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ABN" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('EndUserCompanyName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="EndUserCompanyName" id="EndUserCompanyName" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/EndUserCompanyName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required"></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('TradingName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="TradingName" id="TradingName" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/TradingName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="SmallSeperator"></div>
			
			<div class="Narrow-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td class="Required" id="ServiceAddressTypeMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceAddressType')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="ServiceAddressType" id="ServiceAddressType" onchange="UpdateServiceAddress()">
								<option></option>
								<xsl:for-each select="/Response/ServiceAddressTypes/ServiceAddressType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ServiceAddress/ServiceAddressType = ./Id">
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
						<td class="Required" id="ServiceAddressTypeNumberMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceAddressTypeNumber')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceAddressTypeNumber" id="ServiceAddressTypeNumber" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceAddressTypeNumber" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" id="ServiceAddressTypeSuffixMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceAddressTypeSuffix')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceAddressTypeSuffix" id="ServiceAddressTypeSuffix" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceAddressTypeSuffix" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" id="ServiceStreetNumberStartMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceStreetNumberStart')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceStreetNumberStart" id="ServiceStreetNumberStart" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceStreetNumberStart" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" id="ServiceStreetNumberEndMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceStreetNumberEnd')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceStreetNumberEnd" id="ServiceStreetNumberEnd" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceStreetNumberEnd" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" id="ServiceStreetNumberSuffixMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceStreetNumberSuffix')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceStreetNumberSuffix" id="ServiceStreetNumberSuffix" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceStreetNumberSuffix" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" id="ServiceStreetNameMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceStreetName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceStreetName" id="ServiceStreetName" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceStreetName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required" id="ServiceStreetTypeMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceStreetType')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="ServiceStreetType" id="ServiceStreetType" onchange="UpdateServiceAddress()">
								<option></option>
								<xsl:for-each select="/Response/ServiceStreetTypes/ServiceStreetType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ServiceAddress/ServiceStreetType = ./Id">
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
						<td class="Required" id="ServiceStreetTypeSuffixMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceStreetTypeSuffix')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="ServiceStreetTypeSuffix" id="ServiceStreetTypeSuffix" onchange="UpdateServiceAddress()">
								<option></option>
								<xsl:for-each select="/Response/ServiceStreetSuffixTypes/ServiceStreetSuffixType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ServiceAddress/ServiceStreetTypeSuffix = ./Id">
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
						<td class="Required" id="ServicePropertyNameMandatory"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServicePropertyName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServicePropertyName" id="ServicePropertyName" class="input-string" onchange="UpdateServiceAddress()">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServicePropertyName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceLocality')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServiceLocality" id="ServiceLocality" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServiceLocality" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServiceState')" />
							</xsl:call-template>
						</th>
						<td>
							<select name="ServiceState" id="ServiceState">
								<option></option>
								<xsl:for-each select="/Response/ServiceStateTypes/ServiceStateType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ServiceAddress/ServiceState = ./Id">
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
					<!--TODO!bash! URGENT! when you're asked to fix one postcode, you should probably fix the other one too.  Don't show 0000 if no postcode is entered!!-->
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service Address')" />
								<xsl:with-param name="field" select="string('ServicePostcode')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="ServicePostcode" id="ServicePostcode" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ServiceAddress/ServicePostcode" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Mandatory field<br/>
			</div>
			<div class="Right">
				<input type="button" value="Submit &#0187;" class="input-submit" onclick="Submit();"/>
			</div>
		</div>
		</form>
				
	</xsl:template>
</xsl:stylesheet>
