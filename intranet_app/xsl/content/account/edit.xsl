<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Account</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>

		<form method="POST" action="account_edit.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Account/Id" disable-output-escaping="yes" />
						</xsl:attribute>
					</input>
					<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Id" disable-output-escaping="yes" />
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
								<input type="text" name="BusinessName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/BusinessName" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
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
								<input type="text" name="TradingName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/TradingName" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ABN" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/ABN" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ACN" class="input-ACN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/ACN" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address1')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Address1" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Address1" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address2')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Address2" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Address2" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Suburb')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Suburb" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Suburb" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Postcode')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Postcode" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Postcode" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('State')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="State" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/State" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Country')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Country" disable-output-escaping="yes" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2>Archive Status</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/Account/Archived = 0">
							This Account is <strong><span class="Green">Currently Available</span></strong>.
							If you would like to make this Account Archived, please click the button below:
							
							<div class="Seperator"></div>
							
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<td><input type="checkbox" name="Confirm" value="1" id="Confirm_Archive" /></td>
									<td><label for="Confirm_Archive">Yes, Archive this Account and the Services associated with it.</label></td>
								</tr>
								<tr>
									<td></td>
									<td>
										<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
									</td>
								</tr>
							</table>
						</xsl:when>
						<xsl:otherwise>
							This Account is <strong><span class="Red">Currently Archived</span></strong>.
							If you would like to make this Account Available, please click the button below:
							
							<div class="Seperator"></div>
							
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<td>
										<input type="radio" name="Archived" value="0" id="Archive:FALSE">
											<xsl:choose>
												<xsl:when test="/Response/Account/Archived = 0">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:when>
											</xsl:choose>
										</input>
									</td>
									<td>
										<label for="Archive:FALSE">
											Make this account <strong><span class="Green">Available</span></strong> and active
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="Archived" value="1" id="Archive:TRUE">
											<xsl:choose>
												<xsl:when test="/Response/Account/Archived = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:when>
											</xsl:choose>
										</input>
									</td>
									<td>
										<label for="Archive:TRUE">
											Make this account <strong><span class="Red">Archived</span></strong> and unavailable
										</label>
									</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
									</td>
								</tr>
							</table>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
