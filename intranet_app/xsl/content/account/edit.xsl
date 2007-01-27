<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Account</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'BusinessName'">
						You did not enter a Business Name. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ABN-ACN'">
						You did not enter either an ABN# or ACN#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ABN Invalid'">
						You did not enter a valid ABN#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ACN Invalid'">
						You did not enter a valid ACN#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Address'">
						You did not enter an Address. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Suburb'">
						You did not enter a Suburb. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Postcode'">
						You did not enter a Postcode. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'State'">
						You did not enter a State. Please try again.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="account_edit.php">
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Account/Id" />
						</xsl:attribute>
					</input>
					
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<!-- TODO!bash! Account Id is not displaying -->
								<xsl:value-of select="/Response/ui-values/Id" />
							</td>
						</tr>
						<tr>
							<td><div class="MicroSeperator"></div></td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
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
										<xsl:value-of select="/Response/ui-values/BusinessName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
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
										<xsl:value-of select="/Response/ui-values/TradingName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><div class="MicroSeperator"></div></td>
						</tr>
						<tr>

							<td class="Required"><strong><span class="Red"><sup>1</sup></span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<!-- TODO!bash! the yellow color is useless... just make it stay white until valid. same with ACN -->
								<input type="text" name="ABN" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/ABN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red"><sup>1</sup></span></strong></td>
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
										<xsl:value-of select="/Response/ui-values/ACN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td><div class="MicroSeperator"></div></td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
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
										<xsl:value-of select="/Response/ui-values/Address1" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
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
										<xsl:value-of select="/Response/ui-values/Address2" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
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
										<xsl:value-of select="/Response/ui-values/Suburb" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
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
										<xsl:value-of select="/Response/ui-values/Postcode" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
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
										<xsl:value-of select="/Response/ui-values/State" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Country')" />
								</xsl:call-template>
							</th>
							<td>
								<!-- TODO!bash! Country is not displaying -->
								<xsl:value-of select="/Response/ui-values/Country" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2 class="Archive">Archive Status</h2>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/ui-values/Archived = 0">
							This Account is <strong><span class="Green">Currently Available</span></strong>.
						</xsl:when>
						<xsl:otherwise>
							This Account is <strong><span class="Red">Currently Archived</span></strong>.
						</xsl:otherwise>
					</xsl:choose>
					
					<div class="MicroSeperator"></div>
					
					<table border="0" cellpadding="3" cellspacing="0">
						<xsl:choose>
							<xsl:when test="/Response/ui-values/Archived = 1">
								<tr>
									<td><input type="checkbox" name="Archived" value="0" id="Archive:FALSE" /></td>
									<td>
										<label for="Archive:FALSE">
											<strong><span class="Green">Re-Activate</span></strong> this Account.
										</label>
									</td>
								</tr>
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<td><input type="checkbox" name="Archived" value="1" id="Archive:TRUE" /></td>
									<td>
										<label for="Archive:TRUE">
											<strong><span class="Red">Archive</span></strong> this Account.
										</label>
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
				<strong><span class="Red"><sup>1</sup> </span></strong>: One or both fields required<br/>
			</div>
			<div class="Right">
				<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
