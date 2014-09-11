<?xml version="1.0" encoding="utf-8"?>
<!-- TODO!bash! [  DONE  ]		does not keep value of select when page reloads due to error -->
<!-- TODO!bash! [  DONE  ]		mark all fields as mandatory... use class=Required -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Make Payment</h1>
		
		<form method="post" action="../admin/flex.php/Account/InvoicesAndPayments/?Account.Id=">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Amount'">
							Please enter a valid Amount.
						</xsl:when>
						<!--TODO!bash! [  DONE  ]		Reference number should be required!!! add error saying EXACTLY: "Please enter a valid Reference #." -->
						<xsl:when test="/Response/Error = 'TXNReference'">
							Please enter a valid Reference #.
						</xsl:when>
					</xsl:choose>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			
			
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<xsl:choose>
							<xsl:when test="/Response/Accounts/Results/rangeLength = 1">
								<input type="hidden" name="Account-Use" value="1" />
								<input type="hidden" name="Account">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Accounts/Results/rangeSample/Account[1]/Id" />
									</xsl:attribute>
								</input>
								<tr>
									<th class="JustifiedWidth" valign="top">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Account')" />
											<xsl:with-param name="field" select="string('Id')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Accounts/Results/rangeSample/Account[1]/Id" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth" valign="top">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Account')" />
											<xsl:with-param name="field" select="string('BusinessName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Accounts/Results/rangeSample/Account[1]/BusinessName" />
									</td>
								</tr>
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<th class="JustifiedWidth" valign="top">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Payment')" />
											<xsl:with-param name="field" select="string('PaymentApplication')" />
										</xsl:call-template>
									</th>
									<td>
										<table border="0" cellpadding="3" cellspacing="0">
											<tr>
												<td>
													<input type="radio" id="Account-Use:FALSE" name="Account-Use" value="0">
														<xsl:choose>
															<xsl:when test="/Response/ui-values/Account-Use = 0">
																<xsl:attribute name="checked">
																	<xsl:text>checked</xsl:text>
																</xsl:attribute>
															</xsl:when>
														</xsl:choose>
													</input>
												</td>
												<th>
													<label for="Account-Use:FALSE">
														Apply this Payment against all Accounts.
													</label>
												</th>
											</tr>
											<tr>
												<td>
													<input type="radio" id="Account-Use:TRUE" name="Account-Use" value="1">
														<xsl:choose>
															<xsl:when test="/Response/ui-values/Account-Use = 1">
																<xsl:attribute name="checked">
																	<xsl:text>checked</xsl:text>
																</xsl:attribute>
															</xsl:when>
														</xsl:choose>
													</input>
												</td>
												<th>
													<label for="Account-Use:TRUE">
														Apply this Payment against the following Account:
													</label>
												</th>
											</tr>
											<tr>
												<td></td>
												<td>
												<!--TODO!bash! URGENT! this allows you to select a blank account!! -->
													<select name="Account">
														<option></option>
														<xsl:for-each select="/Response/Accounts/Results/rangeSample/Account">
															<option>
																<xsl:attribute name="value">
																	<xsl:text></xsl:text>
																	<xsl:value-of select="./Id" />
																</xsl:attribute>
																<xsl:choose>
																	<xsl:when test="./Id = /Response/Account">
																		<xsl:attribute name="selected">
																			<xsl:text>selected</xsl:text>
																		</xsl:attribute>
																	</xsl:when>
																</xsl:choose>
																<xsl:value-of select="./Id" />
																<xsl:text>: </xsl:text>
																<xsl:value-of select="./BusinessName" />
															</option>
														</xsl:for-each>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Payment">Payment Details</h2>
			<xsl:if test="/Response/Contact">
				<input type="hidden" name="Contact">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/Contact/Id" />
					</xsl:attribute>
				</input>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaymentType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="PaymentType">
									<xsl:for-each select="/Response/PaymentTypes/PaymentType">
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
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('Amount')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Amount" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:choose>
											<xsl:when test="/Response/ui-values/Amount = ''">
								       			<xsl:call-template name="Currency">
								       				<xsl:with-param name="Number" select="number('0')" />
													<xsl:with-param name="Decimal" select="number('2')" />
						       					</xsl:call-template>
						       				</xsl:when>
						       				<xsl:otherwise>
							       				<xsl:value-of select="/Response/ui-values/Amount" />
						       				</xsl:otherwise>
						       			</xsl:choose>
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('TXNReference')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="TXNReference" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/TXNReference" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Make Payment &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
