<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View/Add Adjustments</h1>
		
		<!-- Service Details -->
		<h2 class="Account">Account Details</h2>
		
		<div class="Wide-Form">
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
		<div class="Seperator"></div>
		
		<!-- Add Adjustment -->
		<h2 class="Adjustment">Add Adjustment</h2>
		
		<div class="Wide-Form">
			<xsl:choose>
				<xsl:when test="count(/Response/ChargeTypes/Results/rangeSample/ChargeType) = 0">
					No adjustments are available.
				</xsl:when>
				<xsl:otherwise>
					<table border="0" cellpadding="3" cellspacing="0">
						<xsl:if test="count(/Response/ChargeTypes/Results/rangeSample/ChargeType[./Nature='CR']) != 0">
							<form method="post" action="charges_charge_assign.php">
								<input type="hidden" name="Account">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
								</input>
								<tr>
									<th class="JustifiedWidth">Credit Adjustment :</th>
									<td>
										<select name="ChargeType">
											<xsl:for-each select="/Response/ChargeTypes/Results/rangeSample/ChargeType[./Nature='CR']">
												<option>
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="./Id" />
													</xsl:attribute>
													<xsl:value-of select="./Description" />
												</option>
											</xsl:for-each>
										</select>
									</td>
									<td>
										<input type="submit" value="Add &#0187;" class="input-submit" />
									</td>
								</tr>
							</form>
						</xsl:if>
						<xsl:if test="count(/Response/ChargeTypes/Results/rangeSample/ChargeType[./Nature='DR']) != 0">
							<form method="post" action="charges_charge_assign.php">
								<input type="hidden" name="Account">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
								</input>
								<tr>
									<th class="JustifiedWidth">Debit Adjustment :</th>
									<td>
										<select name="ChargeType">
											<xsl:for-each select="/Response/ChargeTypes/Results/rangeSample/ChargeType[./Nature='DR']">
												<option>
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="./Id" />
													</xsl:attribute>
													<xsl:value-of select="./Description" />
												</option>
											</xsl:for-each>
										</select>
									</td>
									<td>
										<input type="submit" value="Add &#0187;" class="input-submit" />
									</td>
								</tr>
							</form>
						</xsl:if>
					</table>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		
		<div class="Seperator"></div>
		
		<!-- Unbilled Adjustments -->
		<xsl:if test="/Response/Charges-Unbilled">
			<h2 class="Adjustment">Unbilled Adjustments</h2>
			<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
				<tr class="First">
					<th width="30">#</th>
					<th>Adjustment Code</th>
					<th>Description</th>
					<th>Service</th>
					<th>Created On</th>
					<th>Created By</th>
					<th>Status</th>
					<th class="Currency">Charge</th>
					<th>Nature</th>
					<th>Details</th>
				</tr>
				<xsl:for-each select="/Response/Charges-Unbilled/Results/rangeSample/Charge">
					<xsl:variable name="Charge" select="." />
					<xsl:variable name="CreatedBy" select="/Response/Employees/Employee[./Id=$Charge/CreatedBy]" />
					
					<tr>
						<xsl:attribute name="class">
							<xsl:choose>
								<xsl:when test="position() mod 2 = 1">
									<xsl:text>Odd</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>Even</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
						
						<td><xsl:value-of select="/Response/Charges-Unbilled/Results/rangeStart + position()" />.</td>
						<td><xsl:value-of select="./ChargeType" /></td>
						<td><xsl:value-of select="./Description" /></td>
						<td>
							<xsl:choose>
								<xsl:when test="./Service/Id">
									<a target="_blank">
										<xsl:attribute name="href">
											<xsl:text>service_view.php?Id=</xsl:text>
											<xsl:value-of select="./Service/Id" />
										</xsl:attribute>
										<xsl:value-of select="./Service/Id" />
									</a>
								</xsl:when>
								<xsl:otherwise>
									<strong><span class="Attention">No Service</span></strong>
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"		select="./CreatedOn/year" />
								<xsl:with-param name="month"	select="./CreatedOn/month" />
								<xsl:with-param name="day"		select="./CreatedOn/day" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
							</xsl:call-template>
						</td>
						<td>
							<xsl:value-of select="$CreatedBy/FirstName" />
							<xsl:text> </xsl:text>
							<xsl:value-of select="$CreatedBy/LastName" />
						</td>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="./Status = '100'">
										<span class="Red">Unapproved</span>
									</xsl:when>
									<xsl:otherwise>
										<span class="Green">Approved</span>
									</xsl:otherwise>
								</xsl:choose>
							</strong>
						</td>
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="./Amount" />
								<xsl:with-param name="Decimal" select="number('4')" />
	       					</xsl:call-template>
						</td>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="./Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="./Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
							</strong>
						</td>
						<td>
							<a href="#" title="Adjustment Details" alt="View Information about an Adjustment">
								<xsl:attribute name="onclick">
									<xsl:text>return ModalExternal (this, </xsl:text>
										<xsl:text>'charges_charge_details.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>'</xsl:text>
									<xsl:text>)</xsl:text>
								</xsl:attribute>
								<xsl:text>Details</xsl:text>
							</a>
						</td>
					</tr>
				</xsl:for-each>
			</table>
			<!--TODO!bash! [  DONE  ]		URGENT - if there are no charges or credits: -->
			<!--TODO!bash! [  DONE  ]		msgnotice === "There are no Charges or Credits associated with this Service." -->
			<xsl:if test="/Response/Charges-Unbilled/Results/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Adjustments associated with this Account.
				</div>
			</xsl:if>
			
			<div class="Seperator"></div>
		</xsl:if>
		
		<p>
			<table border="0" cellpadding="3" cellspacing="0" width="100%">
				<tr>
					<td width="10%" align="left">
						<xsl:choose>
							<xsl:when test="/Response/Charges-Unbilled/Results/rangePage != 1">
								<a>
									<xsl:attribute name="href">
										<xsl:text>account_charges_unbilled.php</xsl:text>
										
										<xsl:text>?rangeLength=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangeLength" />
										
										<xsl:text>&amp;rangePage=1</xsl:text>
										
										<xsl:text>&amp;Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
									<xsl:text>&#124;&lt;- First</xsl:text>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<!-- &#124;&lt;- First -->
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td width="10%" align="left">
						<xsl:choose>
							<xsl:when test="/Response/Charges-Unbilled/Results/rangePage &gt; 1">
								<a>
									<xsl:attribute name="href">
										<xsl:text>account_charges_unbilled.php</xsl:text>
										
										<xsl:text>?rangeLength=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangePage - 1" />
										
										<xsl:text>&amp;Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
									<xsl:text>&lt;- Prev</xsl:text>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<!-- &lt;- Prev -->
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td width="60%" align="center">
						Page <xsl:value-of select="/Response/Charges-Unbilled/Results/rangePage" />
						of <xsl:value-of select="/Response/Charges-Unbilled/Results/rangePages" /><br />
						Showing  
						<xsl:value-of select="/Response/Charges-Unbilled/Results/rangeStart + 1" />
						to
						<xsl:choose>
							<xsl:when test="/Response/Charges-Unbilled/Results/rangeLength + /Response/Charges-Unbilled/Results/rangeStart &gt; /Response/Charges-Unbilled/Results/collationLength">
								<xsl:value-of select="/Response/Charges-Unbilled/Results/collationLength" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="/Response/Charges-Unbilled/Results/rangeStart + /Response/Charges-Unbilled/Results/rangeLength" />
							</xsl:otherwise>
						</xsl:choose>
						of
						<xsl:value-of select="/Response/Charges-Unbilled/Results/collationLength" />
					</td>
					<td width="10%" align="right">
						<xsl:choose>
							<xsl:when test="/Response/Charges-Unbilled/Results/rangePage &lt; /Response/Charges-Unbilled/Results/rangePages">
								<a>
									<xsl:attribute name="href">
										<xsl:text>account_charges_unbilled.php</xsl:text>
										
										<xsl:text>?rangeLength=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangePage + 1" />
										
										<xsl:text>&amp;Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
									<xsl:text>Next -&gt;</xsl:text>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<!-- Next -&gt; -->
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td width="10%" align="right">
						<xsl:choose>
							<xsl:when test="/Response/Charges-Unbilled/Results/rangePage &lt; /Response/Charges-Unbilled/Results/rangePages">
								<a>
									<xsl:attribute name="href">
										<xsl:text>account_charges_unbilled.php</xsl:text>
										
										<xsl:text>?rangeLength=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangeLength" />
										
										<xsl:text>&amp;rangePage=</xsl:text>
										<xsl:value-of select="/Response/Charges-Unbilled/Results/rangePages" />
										
										<xsl:text>&amp;Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
									<xsl:text>Last -&gt;&#124;</xsl:text>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<!-- Last -&gt;&#124; -->
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</table>
		</p>
	</xsl:template>
</xsl:stylesheet>
