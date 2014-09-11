<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--This page changes the Lessee of a Service (Part 1) -->
		
		<h1>Change of Lessee</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Date Invalid'">
						Please enter a valid Date .  You must enter a date that is either today or in the future.
					</xsl:when>
				</xsl:choose>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<form method="POST" action="service_lessee.php">
			<input type="hidden" name="Service">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account-Receiving/Account/Id" />
				</xsl:attribute>
			</input>
			
			<!-- Service Details -->
			<h2 class="Service">Service Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
							<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Service/Id" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Service/FNN" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<!--Current Account Details -->
			<h2 class="Account">Current Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account-Original/Account/Id" />
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
								<xsl:value-of select="/Response/Account-Original/Account/BusinessName" />
							</td>
						</tr>
						<!--Check for Trading Name-->
						<xsl:choose>
							<xsl:when test="/Response/Account-Original/Account/TradingName = ''">
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
										<xsl:value-of select="/Response/Account-Original/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<!--Recieving Account Details -->
			<h2 class="Account">Receiving Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account-Receiving/Account/Id" />
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
								<xsl:value-of select="/Response/Account-Receiving/Account/BusinessName" />
							</td>
						</tr>
						<!--Check for Trading Name-->
						<xsl:choose>
							<xsl:when test="/Response/Account-Receiving/Account/TradingName = ''">
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
										<xsl:value-of select="/Response/Account-Receiving/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			<h2 class="Date">Date of Change</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
					<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Lessee')" />
								<xsl:with-param name="field" select="string('ChangeDate')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="NearFuture">
								<xsl:with-param name="Name-Day"			select="string('Date[day]')" />
								<xsl:with-param name="Name-Month"		select="string('Date[month]')" />
								<xsl:with-param name="Name-Year"		select="string('Date[year]')" />
								<xsl:with-param name="Selected-Month"	select="/Response/ui-values/Date-day" />
								<xsl:with-param name="Selected-Month"	select="/Response/ui-values/Date-month" />
								<xsl:with-param name="Selected-Month"	select="/Response/ui-values/Date-year" />
							</xsl:call-template>
						</td>
					</tr>
					<tr>
					<td></td>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Lessee')" />
								<xsl:with-param name="field" select="string('Unbilled')" />
							</xsl:call-template>
						</th>
						<td>
						<input type="checkbox" name="Unbilled" value="1" id="unbilledCharges"/>
							<label for="unbilledCharges">Transfer all
								<strong><span class="Red">unbilled</span></strong> charges.
							</label>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Change Lessee &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
