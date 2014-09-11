<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Account Details</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'BusinessName'">
						Please enter a Business Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ABN-ACN'">
						Please enter a ABN or ACN.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ABN Invalid'">
						Please enter a valid ABN.
					</xsl:when>
					<xsl:when test="/Response/Error = 'ACN Invalid'">
						Please enter a valid ACN.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Address'">
						Please enter an Address.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Suburb'">
						Please enter a Suburb.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Postcode'">
						Please enter a Postcode.
					</xsl:when>
					<xsl:when test="/Response/Error = 'State'">
						Please enter a State.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Customer Group'">
						Please enter a valid Customer Group.
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
								<!-- TODO!bash! [  DONE  ]		Account Id is not displaying -->
								<xsl:value-of select="/Response/Account/Id" />
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
								<input type="text" name="BusinessName" class="input-string" maxlength="255">
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
								<input type="text" name="TradingName" class="input-string" maxlength="255">
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
								<!-- TODO!bash! [  DONE  ]		the yellow color is useless... just make it stay white until valid. same with ACN -->
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
								<input type="text" name="Address1" class="input-string" maxlength="255">
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
								<input type="text" name="Address2" class="input-string" maxlength="255">
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
								<input type="text" name="Suburb" class="input-string" maxlength="255">
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
							<!--TODO!bash! [  DONE  ]		URGENT - verify - only 4 digit number -->
							<td>
								<input type="text" name="Postcode" class="input-string" maxlength="4">
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
							<select name="State">
								<xsl:for-each select="/Response/ServiceStateTypes/ServiceStateType">
									<option>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:if test="/Response/ui-values/State = ./Id">
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
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Country')" />
								</xsl:call-template>
							</th>
							<td>
								<!-- TODO!bash! [  DONE  ]		Country is not displaying -->
								<xsl:value-of select="/Response/Account/Country" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Billing')" />
									<xsl:with-param name="field" select="string('BillingMethod')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="BillingMethod">
									<xsl:for-each select="/Response/BillingMethods/BillingMethod">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/ui-values/BillingMethod = ./Id">
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
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('CustomerGroup')" />
									<xsl:with-param name="field" select="string('CustomerGroup')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="CustomerGroup">
									<xsl:for-each select="/Response/CustomerGroups/CustomerGroup">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/ui-values/CustomerGroup = ./Id">
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
							<td colspan="3">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('DisableDDR')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="checkbox" name="DisableDDR" id="DisableDDR:TRUE" value="1">
									<xsl:if test="/Response/ui-values/DisableDDR = 1">
										<xsl:attribute name="checked">
											<xsl:text>checked</xsl:text>
										</xsl:attribute>
									</xsl:if>
								</input>
								
								<label for="DisableDDR:TRUE">
									Do NOT charge an admin fee (non direct debit fee)
								</label>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('DisableLatePayment')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="3" cellspacing="0">
									<tr>
										<td>
											<input type="radio" name="DisableLatePayment" id="DisableLatePayment:FALSE" value="0">
												<xsl:if test="/Response/ui-values/DisableLatePayment = 0">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="DisableLatePayment:FALSE">
												Charge a late payment fee
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="DisableLatePayment" id="DisableLatePayment:DONKEY" value="-1">
												<xsl:if test="/Response/ui-values/DisableLatePayment = -1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="DisableLatePayment:DONKEY">
												Don't charge a late payment fee on the next invoice
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="DisableLatePayment" id="DisableLatePayment:TRUE" value="1">
												<xsl:if test="/Response/ui-values/DisableLatePayment = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="DisableLatePayment:TRUE">
												Never charge a late payment fee
											</label>
										</td>
									</tr>
								</table>
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
						<xsl:when test="/Response/Account/Archived = 0">
							This Account is <strong><span class="Green">Currently Available</span></strong>.
						</xsl:when>
						<xsl:otherwise>
							This Account is <strong><span class="Red">Currently Archived</span></strong>.
						</xsl:otherwise>
					</xsl:choose>
					
					<div class="MicroSeperator"></div>
					<!--TODO!bash! [  DONE  ]		URGENT! This is not working - view account details page shows status as 'archived' but this page still says 'this account is currently available'/'archive this account' and the account cannot be unarchived-->
					<table border="0" cellpadding="3" cellspacing="0">
						<xsl:choose>
							<xsl:when test="/Response/Account/Archived = 1">
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
