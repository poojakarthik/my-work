<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Rate Group</h1>
		
		<script type="text/javascript" src="js/rates_group_add.js"></script>
		<form method="POST" action="rates_group_add.php">
			<xsl:attribute name="onsubmit">
				<xsl:text>selIt ()</xsl:text>
			</xsl:attribute>
			
			<input type="hidden" name="Name">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RateGroup/Name" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ServiceType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RateGroup/ServiceTypes/ServiceType[@selected='selected']/Id" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="RecordType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RateGroup/RecordType/Id" />
				</xsl:attribute>
			</input>
			
			<xsl:if test="/Response/RateGroup/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/RateGroup/Error = 'RateNotFound'">
							One of the Rates that you selected was not valid. Please
							select Rates from the list below and try resubmitting.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Group')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/RateGroup/Name" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/RateGroup/ServiceTypes/ServiceType[@selected='selected']/Name" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Record Type')" />
									<xsl:with-param name="field" select="string('RecordType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/RateGroup/RecordType/Name" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Group')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Description" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/RateGroup/Name" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
			</div>
			
			<div class="Seperator"></div>
					
			<div class="Wide-Form">
				<div class="Form-Content Left">
					Select multiple rates by holding the CTRL key while you click options from
					either of the lists.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="1" cellspacing="0">
						<tr>
							<th>Available Rates :</th>
							<td></td>
							<th>Selected Rates :</th>
						</tr>
						<tr>
							<td>
								<select id="AvailableRates" name="AvailableRates[]" size="20" class="LargeSelection" multiple="multiple">
									<xsl:for-each select="/Response/RateGroup/Rates/Rate">
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
							<td>
								<div>
									<input type="button" value="&#0187;">
										<xsl:attribute name="onclick">
											<xsl:text>addIt ()</xsl:text>
										</xsl:attribute>
									</input>
								</div>
								<div class="Seperator"></div>
								<div>
									<input type="button" value="&#0171;">
										<xsl:attribute name="onclick">
											<xsl:text>delIt ()</xsl:text>
										</xsl:attribute>
									</input>
								</div>
							</td>
							<td>
								<select id="SelectedRates" name="SelectedRates[]" size="20" class="LargeSelection" multiple="multiple" />
							</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td style="text-align: right">
								<input type="button" value="Preview Rate Summary &#0187;" class="input-submit">
									<xsl:attribute name="onclick">
										<xsl:text>showSelectedRatesTable ()</xsl:text>
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
			</div>
			
			<div class="Seperator"></div>
			
			<div>
				<input type="submit" value="Create Rate Group &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
