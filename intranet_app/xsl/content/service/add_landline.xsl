<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Land Line Service Address Details</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = ''">
					
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<script type="text/javascript" src="js/ABN.js"></script>
		
		<form method="post" action="service_add.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="FNN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/FNN" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="ServiceType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/ServiceTypes/ServiceType[@selected='selected']/Id" />
				</xsl:attribute>
			</input>
			
			<h2>Indial Options</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<td>
								<input type="checkbox" name="Indial100" id="Service:Indial100" value="1" />
							</td>
							<td>
								<label for="Service:Indial100">
									Yes, mark this number as having an indial 100 range.
								</label>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<h2>Service Address Details</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<!-- Bill Details -->
						<tr>
							<td width="100"><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('BillName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[BillName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/BillName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('BillAddress1')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[BillAddress1]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/BillAddress1" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('BillAddress2')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[BillAddress2]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/BillAddress2" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('BillLocality')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[BillLocality]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/BillLocality" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('BillPostcode')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[BillPostcode]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/BillPostcode" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<!-- End User Details -->
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('EndUserTitle')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceAddress[EndUserTitle]">
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
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('EndUserGivenName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[EndUserGivenName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/EndUserGivenName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('EndUserFamilyName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[EndUserFamilyName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/EndUserFamilyName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('EndUserCompanyName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[EndUserCompanyName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/EndUserCompanyName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('DateOfBirth')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceAddress[DateOfBirth][year]">
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number (1900)" />
										<xsl:with-param name="cease" select="number (1990)" />
										<xsl:with-param name="steps" select="number (1)" />
										<xsl:with-param name="select" select="substring (/Response/Service/ServiceAddress/DateOfBirth, 1, 4)" />
									</xsl:call-template>
								</select> -
								<select name="ServiceAddress[DateOfBirth][month]">
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number (1)" />
										<xsl:with-param name="cease" select="number (12)" />
										<xsl:with-param name="steps" select="number (1)" />
										<xsl:with-param name="select" select="substring (/Response/Service/ServiceAddress/DateOfBirth, 5, 2)" />
									</xsl:call-template>
								</select> -
								<select name="ServiceAddress[DateOfBirth][day]">
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
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('Employer')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[Employer]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/Employer" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('Occupation')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[Occupation]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/Occupation" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<!-- Company Details -->
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ABN]" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ABN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[TradingName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/TradingName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						
						<!-- Service Details -->
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceAddressType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceAddress[ServiceAddressType]">
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
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceAddressTypeNumber')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceAddressTypeNumber]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceAddressTypeNumber" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceAddressTypeSuffix')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceAddressTypeSuffix]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceAddressTypeSuffix" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceStreetNumberStart')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceStreetNumberStart]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetNumberStart" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceStreetNumberEnd')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceStreetNumberEnd]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetNumberEnd" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceStreetNumberSuffix')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceStreetNumberSuffix]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetNumberSuffix" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceStreetName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceStreetName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceStreetName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceStreetType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceAddress[ServiceStreetType]">
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
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceStreetTypeSuffix')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceAddress[ServiceStreetTypeSuffix]">
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
							<td></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServicePropertyName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServicePropertyName]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServicePropertyName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceLocality')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServiceLocality]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServiceLocality" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServiceState')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ServiceAddress[ServiceState]">
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
							<td><strong><span class="Attention">Required</span></strong></td>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service Address')" />
									<xsl:with-param name="field" select="string('ServicePostcode')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ServiceAddress[ServicePostcode]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Service/ServiceAddress/ServicePostcode" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
					</table>
					
					<input type="submit" value="Create Service Address &#0187;" class="input-submit" />
				</div>
			</div>
		</form>
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
