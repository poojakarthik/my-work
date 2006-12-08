<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:import href="../../lib/date-difference/date.difference.function.xsl"/>
	<xsl:import href="../../lib/date-difference/date.difference.template.xsl"/>
	
	<xsl:template name="Content">
		<h1>View Account Details</h1>
		
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2>Account Information</h2>
					<div class="Seperator"></div>
					
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<form method="POST" action="account_edit.php">
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
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('BusinessName')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/BusinessName" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('TradingName')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/TradingName" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('ABN')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/ABN" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('ACN')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/ACN" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Address1')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Address1" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Address2')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Address2" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Suburb')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Suburb" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Postcode')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Postcode" disable-output-escaping="yes" /></td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('State')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/State" disable-output-escaping="yes" /></td>
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
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Archived')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:choose>
												<xsl:when test="/Response/Account/Archived = 0">
													<strong><span class="Green">Currently Available</span></strong>
												</xsl:when>
												<xsl:otherwise>
													<strong><span class="Red">Currently Archived</span></strong>
												</xsl:otherwise>
											</xsl:choose>
										</td>
									</tr>
								</table>
							</form>
						</div>
					</div>
					<div class="Clear"></div>
				</td>
				<td width="30" nowrap="nowrap"></td>
				<td valign="top" width="300">
					<h2>Account Notes</h2>
					<div class="Seperator"></div>
					
					Type new notes for this account in the field below:
					<textarea name="Note" class="input-summary" rows="6" />
					
					<div class="Right">
						<input type="submit" value="Create Note &#0187;" class="input-submit" />
					</div>
					
					<div class="Clear"></div>
					
					<div class="Seperator"></div>
					<h3>Recent Notes</h3>
					<div class="Seperator"></div>
					Listed below are the 10 most recent notes
					that are associated with this Account. To view more
					notes for this account, visit the
					<a>
						<xsl:attribute name="href">
							<xsl:text>notes.php?Account=</xsl:text>
							<xsl:value-of select="/Response/Account/Id" />
						</xsl:attribute>
						<xsl:text>Note Archive</xsl:text>
					</a>.
					<div class="Seperator"></div>
					<xsl:for-each select="/Response/Account/Notes/Results/rangeSample/Note">
						<div class="Note">
							<xsl:attribute name="style">
								<xsl:text>background-color: #</xsl:text><xsl:value-of select="./NoteType/BackgroundColor" /><xsl:text>;</xsl:text>
								<xsl:text>border: solid 1px #</xsl:text><xsl:value-of select="./NoteType/BorderColor" /><xsl:text>;</xsl:text>
							</xsl:attribute>
							
							<!--
							<xsl:value-of select="date:difference('2006-01-01', '2006-12-31')" />
							-->
							
							<xsl:variable name="Difference">
								<xsl:call-template name="date:difference">
									<xsl:with-param name="start" select="./Datetime/timestamp" />
									<xsl:with-param name="end" select="/Response/Now/timestamp" />
								</xsl:call-template>
							</xsl:variable>
							
							<div class="small">
								Created on 
									<strong>
										<xsl:call-template name="dt:format-date-time">
											<xsl:with-param name="year"	select="./Datetime/year" />
											<xsl:with-param name="month"	select="./Datetime/month" />
											<xsl:with-param name="day"		select="./Datetime/day" />
					 						<xsl:with-param name="hour"	select="./Datetime/hour" />
											<xsl:with-param name="minute"	select="./Datetime/minute" />
											<xsl:with-param name="second"	select="./Datetime/second" />
											<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
										</xsl:call-template>
									</strong>
								by
									<strong>
										<xsl:value-of select="./Employee/FirstName" />
										<xsl:text> </xsl:text>
										<xsl:value-of select="./Employee/LastName" />
									</strong>.
							</div>
							<div class="Seperator"></div>
							
							<xsl:value-of select="./Note" />
						</div>
					</xsl:for-each>
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
