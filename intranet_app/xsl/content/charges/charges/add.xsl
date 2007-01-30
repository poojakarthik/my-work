<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">

	
	
		<h1>Add New Charge Type</h1>
		
		<form method="POST" action="charges_charge_add.php">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'CType-Blank'">
							Please enter a Charge Code.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Descr-Blank'">
							Please enter a Charge Description
						</xsl:when>
						<xsl:when test="/Response/Error = 'CType-Exists'">
							The Charge Code you entered already exists.  Please enter a unique Charge Code.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Nature'">
							Please select a valid Nature.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Amount Invalid'">
							Please enter a valid Amount.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('ChargeType')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ChargeType" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ChargeType/ChargeType" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Description" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ChargeType/Description" />
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
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Amount')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Amount" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ChargeType/Amount" />
									</xsl:attribute>
								</input>
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
								<select name="Nature">
									<xsl:for-each select="/Response/ChargeType/Natures/Nature">
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
									<xsl:with-param name="entity" select="string('Charge Type')" />
									<xsl:with-param name="field" select="string('Fixed')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="checkbox" name="Fixed" class="input-string" value="1" id="ChargeType:Fixed">
												<xsl:if test="/Response/ChargeType/Fixed = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="ChargeType:Fixed">
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
			
			<input type="submit" value="Create Charge &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
