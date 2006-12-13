<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Service Details</h1>
		
		<script language="javascript" src="js/notes_popup.js"></script>
		
		<div class="MsgNotice">
			<h2>Account Information</h2>
			
			<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Account')" />
							<xsl:with-param name="field" select="string('Id')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Account/Id" />
						[<a>
							<xsl:attribute name="href">
								<xsl:text>account_view.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Account/Id" />
							</xsl:attribute>
							<xsl:text>View Account</xsl:text>
						</a>]
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
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Account')" />
							<xsl:with-param name="field" select="string('TradingName')" />
						</xsl:call-template>
					</th>
					<td>
						<xsl:value-of select="/Response/Account/TradingName" />
					</td>
				</tr>
			</table>
			
			<div class="Clear"></div>
		</div>
		
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2>Service Information</h2>
					<div class="Seperator"></div>
					
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('Id')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Service/Id" disable-output-escaping="yes" />
									</td>
								</tr>
								<tr>
									<td colspan="2"><div class="Seperator"></div></td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('FNN')" />
										</xsl:call-template>
									</th>
									<td><xsl:value-of select="/Response/Service/FNN" disable-output-escaping="yes" /></td>
								</tr>
								<tr>
									<td colspan="2"><div class="Seperator"></div></td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('ServiceType')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Service/ServiceTypes/ServiceType[@selected='selected']/Name" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('Indial100')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Service/Indial100 = 1">
												<strong><span class="Green">Indial 100 Support</span></strong>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Red">No Indial 100 Support</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
								<tr>
									<td colspan="2"><div class="Seperator"></div></td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service')" />
											<xsl:with-param name="field" select="string('CreatedOn')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:call-template name="dt:format-date-time">
											<xsl:with-param name="year"	select="/Response/Service/CreatedOn/year" />
											<xsl:with-param name="month"	select="/Response/Service/CreatedOn/month" />
											<xsl:with-param name="day"		select="/Response/Service/CreatedOn/day" />
											<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
										</xsl:call-template>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Archive')" />
											<xsl:with-param name="field" select="string('Archived')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Service/Archived = 0">
												<strong><span class="Green">Currently Available</span></strong>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Red">Currently Archived</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="Clear"></div>
				</td>
				<td width="30" nowrap="nowrap"></td>
				<td valign="top" width="300">
					<h2>Service Notes</h2>
					<div class="Seperator"></div>
					
					<form method="post" action="note_add.php">
						<input type="hidden" name="AccountGroup">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/AccountGroup" />
							</xsl:attribute>
						</input>
						<input type="hidden" name="Service">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
						</input>
						Type new notes for this service in the field below:
						<textarea name="Note" class="input-summary" rows="6" />
						
						<div>
							<input type="checkbox" name="Account">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
							</input>
							Associate this Service note with this Account
						</div>
						
						<select name="NoteType">
							<xsl:for-each select="/Response/NoteTypes/NoteType">
								<xsl:variable name="NoteType" select="." />
								<option>
									<xsl:attribute name="style">
										<xsl:text>background-color: #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$NoteType/Id]/BackgroundColor" />
										<xsl:text>;</xsl:text>
										<xsl:text>border: solid 1px #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$NoteType/Id]/BorderColor" />
										<xsl:text>;</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:value-of select="./TypeLabel" />
								</option>
							</xsl:for-each>
						</select>
						
						<div class="Right">
							<input type="submit" value="Create Note &#0187;" class="input-submit" />
						</div>
					</form>
					
					<div class="Clear"></div>
					
					<div class="Seperator"></div>
					<h3>Recent Notes</h3>
					<div class="Seperator"></div>
					Listed below are the 10 most recent notes
					that are associated with this Service. To view more
					notes for this Service, visit the
					<a>
						<xsl:attribute name="href">
							<xsl:text>javascript:notes_popup('', '', '</xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
							<xsl:text>', '')</xsl:text>
						</xsl:attribute>
						<xsl:text>Note Archive</xsl:text>
					</a>.
					<div class="Seperator"></div>
					<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
						<xsl:variable name="Note" select="." />
						<div class="Note">
							<xsl:attribute name="style">
								<xsl:text>background-color: #</xsl:text><xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BackgroundColor" /><xsl:text>;</xsl:text>
								<xsl:text>border: solid 1px #</xsl:text><xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BorderColor" /><xsl:text>;</xsl:text>
							</xsl:attribute>
							
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
						<div class="Seperator"></div>
					</xsl:for-each>
				</td>
			</tr>
		</table>
		<div class="Clear"></div>
		<div class="Seperator"></div>
		<div class="Seperator"></div>
	</xsl:template>
</xsl:stylesheet>
