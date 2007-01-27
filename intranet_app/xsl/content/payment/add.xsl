<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Make Payment</h1>
		
		<form method="post" action="payment_add.php">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Amount'">
							You did not enter a valid Payment Amount. Please try again.
						</xsl:when>
					</xsl:choose>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			
			
			<h2 class="Invoice">Payment Direction</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaymentApplication')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="/Response/Accounts/Results/rangeLength = 1">
										<xsl:value-of select="/Response/Accounts/Results/rangeSample/Account[1]/BusinessName" />
										<input type="hidden" name="Account">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangeSample/Account[1]/Id" />
											</xsl:attribute>
										</input>
									</xsl:when>
									<xsl:otherwise>
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
														Apply this Payment to the oldest Invoice in all of the Accounts.
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
														Apply this Payment to the Account in the list below:
													</label>
												</th>
											</tr>
											<tr>
												<td></td>
												<td>
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
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Payment">Payment Details</h2>
			<xsl:if test="/Response/AccountGroup">
				<input type="hidden" name="AccountGroup">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/AccountGroup/Id" />
					</xsl:attribute>
				</input>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
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
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
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
										<xsl:value-of select="/Response/ui-values/Amount" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
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
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Payment &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
