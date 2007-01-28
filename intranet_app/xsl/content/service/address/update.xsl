<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Provisioning</h1>
		<!-- TODO!bash! Rename this page and stick all of the 'Service address' pages in the provisioning folder where they belong -->
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/note_add.js"></script>
		
		<table border="0" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2 class="Service">Service Details</h2>
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
						</table>
					</div>
					
					<div class="Seperator"></div>
						
					<form method="post" action="service_address.php">
						<input type="hidden" name="Service">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
						</input>
						
						<h2 class="Address">Service Address Details</h2>
						<xsl:if test="not(/Response/ServiceAddress)">
						<!-- TODO!bash! DO NOT SET WIDTH INSIDE THE PAGE !!!!!!!!!!!! SET IT IN THE DAMN CLASS !!!!!!!!!!!!!! -->
						<div class="MsgNoticeNarrow">
								<strong><span class="Attention">Notice</span> :</strong>
								No Service Address Details Found
							</div>
						</xsl:if>
						<div class="Narrow-Form">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('BillName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="BillName" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/BillName" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('BillAddress1')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="BillAddress1" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/BillAddress1" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('BillAddress2')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="BillAddress2" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/BillAddress2" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('BillLocality')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="BillLocality" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/BillLocality" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('BillPostcode')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="BillPostcode" class="input-string">
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
						
						<div class="Narrow-Form">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<!-- TODO!bash! don't do things like td width=10 ... do class=Something and stick the width control in CSS -->
									<!-- this has already been done here. -->
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"> </td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('EndUserTitle')" />
										</xsl:call-template>
									</th>
									<td>
										<select name="EndUserTitle">
											<option></option>
											<xsl:for-each select="/Response/ServiceEndUserTitleTypes/ServiceEndUserTitleType">
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
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('EndUserGivenName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="EndUserGivenName" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/EndUserGivenName" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('EndUserFamilyName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="EndUserFamilyName" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/EndUserFamilyName" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('EndUserCompanyName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="EndUserCompanyName" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/EndUserCompanyName" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('DateOfBirth')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:call-template name="DOB">
											<xsl:with-param name="Name-Day"			select="string('DateOfBirth[day]')" />
											<xsl:with-param name="Name-Month"		select="string('DateOfBirth[month]')" />
											<xsl:with-param name="Name-Year"		select="string('DateOfBirth[year]')" />
											<xsl:with-param name="Selected-Day"		select="substring (/Response/ServiceAddress/DateOfBirth, 7, 2)" />
											<xsl:with-param name="Selected-Month"	select="substring (/Response/ServiceAddress/DateOfBirth, 5, 2)" />
											<xsl:with-param name="Selected-Year"	select="substring (/Response/ServiceAddress/DateOfBirth, 1, 4)" />
										</xsl:call-template>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('Employer')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="Employer" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/Employer" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('Occupation')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="Occupation" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/Occupation" />
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
									<td class="Required"></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ABN')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ABN" class="input-ABN">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ABN" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('TradingName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="TradingName" class="input-string">
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
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceAddressType')" />
										</xsl:call-template>
									</th>
									<td>
										<select name="ServiceAddressType">
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
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceAddressTypeNumber')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceAddressTypeNumber" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceAddressTypeNumber" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceAddressTypeSuffix')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceAddressTypeSuffix" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceAddressTypeSuffix" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceStreetNumberStart')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceStreetNumberStart" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceStreetNumberStart" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceStreetNumberEnd')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceStreetNumberEnd" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceStreetNumberEnd" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceStreetNumberSuffix')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceStreetNumberSuffix" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceStreetNumberSuffix" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceStreetName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceStreetName" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceStreetName" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceStreetType')" />
										</xsl:call-template>
									</th>
									<td>
										<select name="ServiceStreetType">
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
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceStreetTypeSuffix')" />
										</xsl:call-template>
									</th>
									<td>
										<select name="ServiceStreetTypeSuffix">
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
									<td class="Required"></td>
									<td class="Required"></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServicePropertyName')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServicePropertyName" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServicePropertyName" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceLocality')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServiceLocality" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ServiceAddress/ServiceLocality" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServiceState')" />
										</xsl:call-template>
									</th>
									<td>
										<select name="ServiceState">
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
								<tr>
									<td class="Required"><strong><span class="Red">R</span></strong></td>
									<td class="Required"><strong><span class="Red">B</span></strong></td>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Address')" />
											<xsl:with-param name="field" select="string('ServicePostcode')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="ServicePostcode" class="input-string">
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
							<strong><span class="Red">R </span></strong>: Required for Residential Services<br/>
							<strong><span class="Red">B </span></strong>: Required for Business Services<br/>
						</div>
						<div class="Right">
							<input type="submit" value="Apply Changes &#0187;" class="input-submit" />
						</div>
					</form>
				</td>
				
				<!-- column spacer -->
				<td class="ColumnSpacer"></td>
				
				<!-- second column -->
				<td valign="top">
					
					<!-- Options -->
					<h2 class="Options">Provisioning Options</h2>
					<ul>
						<li>
							<a href="#" title="Provisioning History" alt="A history Sent and Received Requests">
								<xsl:attribute name="onclick">
									<xsl:text>return ModalExternal (this, 'provisioning_history.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" /><xsl:text>')</xsl:text>
								</xsl:attribute>
								<xsl:text>View Provisioning History</xsl:text>
							</a>
						</li>
						<li>
							<a href="#" title="Provisioning Requests" alt="Requests that have been (or will be) Sent">
								<xsl:attribute name="onclick">
									<xsl:text>return ModalExternal (this, 'provisioning_requests.php?Service=</xsl:text>
									<xsl:value-of select="/Response/Service/Id" /><xsl:text>')</xsl:text>
								</xsl:attribute>
								<xsl:text>View Provisioning Requests</xsl:text>
							</a>
						</li>
					</ul>
					<div class="Seperator"></div>
					
					<!-- Provisioning Request -->
					<h2 class="Provisioning">Provisioning Request</h2>
					
					<form method="POST" action="provisioning_request.php">
						<input type="hidden" name="Service">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
						</input>
						
						<div class="Narrow-Form">
							<table border="0" cellpadding="3" cellspacing="0">
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
							</table>
						</div>
						<div class="SmallSeperator"></div>
						<div class="Right">
							<input type="submit" value="Perform Request &#0187;" class="input-submit" />
						</div>
						<div class="Clear"></div>
					</form>
					<div class="Seperator"></div>
					
					<!-- Notes -->
					<h2 class="Notes">Service Notes</h2>
					
					<form method="post" action="note_add.php" onsubmit="return noteAdd (this)">
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
							<input type="submit" value="Add Note &#0187;" class="input-submit" />
						</div>
						<div class="Clear"></div>
					</form>
					
					<!-- Recent Notes -->
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
