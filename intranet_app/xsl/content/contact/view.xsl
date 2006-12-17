<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Contact Details</h1>
		
		<script language="javascript" src="js/notes_popup.js"></script>
		
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<h2 class="Contact">Contact Information</h2>
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Id')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/Id" />
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
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Name')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:if test="/Response/Contact/Title != ''">
											<xsl:value-of select="/Response/Contact/Title" />.
											<xsl:text> </xsl:text>
										</xsl:if>
										<xsl:value-of select="/Response/Contact/FirstName" />
										<xsl:text> </xsl:text>
										<xsl:value-of select="/Response/Contact/LastName" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('JobTitle')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/JobTitle" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('DOB')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:call-template name="dt:format-date-time">
											<xsl:with-param name="year"	select="/Response/Contact/DOB/year" />
											<xsl:with-param name="month"	select="/Response/Contact/DOB/month" />
											<xsl:with-param name="day"		select="/Response/Contact/DOB/day" />
											<xsl:with-param name="format"	select="'%b %d, %Y'"/>
										</xsl:call-template>
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
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Email')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/Email" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Phone')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/Phone" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Mobile')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/Mobile" />
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Fax')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/Fax" />
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
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('UserName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Contact/UserName" />
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
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('CustomerContact')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Contact/CustomerContact">
												<strong><span class="Green">Account Group Contact</span></strong>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Red">Account Contact</span></strong>
											</xsl:otherwise>
										</xsl:choose>
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
											<xsl:when test="/Response/Contact/Archived = 0">
												<strong><span class="Green">Active Contact</span></strong>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Red">Archived Contact</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</td>
				<td width="30" nowrap="nowrap"></td>
				<td width="300" valign="top">























					<h2>Contact Notes</h2>
					<div class="Seperator"></div>
					
					<form method="post" action="note_add.php">
						<input type="hidden" name="AccountGroup">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Account/AccountGroup" />
							</xsl:attribute>
						</input>
						<input type="hidden" name="Contact">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/Id" />
							</xsl:attribute>
						</input>
						Type new notes for this Contact in the field below:
						<textarea name="Note" class="input-summary" rows="6" />
						
						<select name="NoteType">
							<xsl:for-each select="/Response/NoteTypes/NoteType">
								<option>
									<xsl:attribute name="style">
										<xsl:text>background-color: #</xsl:text>
										<xsl:value-of select="./BackgroundColor" />
										<xsl:text>;</xsl:text>
										<xsl:text>border: solid 1px #</xsl:text>
										<xsl:value-of select="./BorderColor" />
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
					Listed below are the 5 most recent notes
					that are associated with this Contact. To view more
					notes for this Contact, visit the
					<a>
						<xsl:attribute name="href">
							<xsl:text>javascript:notes_popup('', '', '', '</xsl:text>
							<xsl:value-of select="/Response/Contact/Id" />
							<xsl:text>')</xsl:text>
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
		
		<div class="Seperator"></div>
		
		<h2 class="Accounts">Account Privileges</h2>
		<p>
			The contact you are currently viewing has access to the following Accounts.
		</p>
		
		<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Account Id</th>
				<th>Business Name</th>
				<th>Trading Name</th>
				<th>Options</th>
			</tr>
			<xsl:for-each select="/Response/Accounts/Results/rangeSample/Account">
				<tr>
					<td><xsl:value-of select="position()" /></td>
					<td><xsl:value-of select="./Id" /></td>
					<td><xsl:value-of select="./BusinessName" /></td>
					<td><xsl:value-of select="./TradingName" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>account_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>View Account</xsl:text>
						</a>
						-
						<a>
							<xsl:attribute name="href">
								<xsl:text>javascript:notes_popup('', '</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>', '', '')</xsl:text>
							</xsl:attribute>
							<xsl:text>View Notes</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
