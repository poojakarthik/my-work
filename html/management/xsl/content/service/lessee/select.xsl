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
					<xsl:when test="/Response/Error = 'Empty Account'">
						You did not enter an Account Id#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Same Account'">
						Please enter a different Account ID.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Invalid Account'">
						Please enter a valid Account ID.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
	
		
		<form method="POST" action="service_lessee.php">
			<input type="hidden" name="Service">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
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
			
			<!-- Current Account Details -->
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
			
			<h2 class="Account">Receiving Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
						<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Account" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Account" />
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
				<input type="submit" value="Continue &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
