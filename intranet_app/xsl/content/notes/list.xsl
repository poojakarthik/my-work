<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>View Notes</h1>
		

		
		<xsl:if test="/Response/AccountGroup">
			<h2 class="Accounts">Account Details</h2>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account Group')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/AccountGroup/Id" /></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<xsl:if test="/Response/Account">
			<h2 class="Account">Account Details</h2>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/TradingName" /></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<xsl:if test="/Response/Service">
			<h2 class="Service">Service Details</h2>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Service/FNN" /></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<xsl:if test="/Response/Contact">
			<h2 class="Contact">Contact Details</h2>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Contact/FirstName" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="/Response/Contact/LastName" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<form method="get" action="note_list.php">
			<input type="hidden" name="AccountGroup">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/AccountGroup/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="Service">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="Contact">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Contact/Id" />
				</xsl:attribute>
			</input>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Note')" />
									<xsl:with-param name="field" select="string('NoteType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="NoteType">
									<option value="">Show All</option>
									<xsl:for-each select="/Response/NoteTypes/NoteType">
										<option>
											<xsl:attribute name="style">
												<xsl:text>background-color: #</xsl:text>
												<xsl:value-of select="./BackgroundColor" />
												<xsl:text>;</xsl:text>
												
												<xsl:text>border: solid 1px #</xsl:text>
												<xsl:value-of select="./BorderColor" />
												<xsl:text>;</xsl:text>
												
												<xsl:text>color: #</xsl:text>
												<xsl:value-of select="./TextColor" />
												<xsl:text>;</xsl:text>
											</xsl:attribute>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/Notes/Constraints/Constraint[Name='NoteType']/Value = ./Id">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="./TypeLabel" />
										</option>
									</xsl:for-each>
								</select>
								
								<input type="submit" value="Apply Filter &#0187;" class="input-submit" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
		
		<div class="Seperator"></div>
		
		<h2 class="Notes">Notes</h2>
		
		<xsl:if test="/Response/Notes/Constraints/Constraint[Name='NoteType']">
			<p>
				Currently only showing notes of type:
				<strong>
					<xsl:value-of select="/Response/NoteTypes/NoteType[Id=/Response/Notes/Constraints/Constraint[Name='NoteType']/Value]/TypeLabel" />
				</strong>
			</p>
		</xsl:if>
		
		<div class="Seperator"></div>
		
		<xsl:choose>
			<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
				<div class="MsgNotice">
					<xsl:choose>
						<xsl:when test="/Response/Service">
							No notes were found for the Service that you requested.
						</xsl:when>
						<xsl:when test="/Response/Contact">
							No notes were found for the Contact that you requested.
						</xsl:when>
						<xsl:when test="/Response/Account">
							No notes were found for the Account that you requested.
						</xsl:when>
						<xsl:when test="/Response/AccountGroup">
							No notes were found for the Account Group that you requested.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
					<xsl:variable name="Note" select="." />
					<div class="Note">
						<xsl:attribute name="style">
							<xsl:text>background-color: #</xsl:text>
							<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BackgroundColor" />
							<xsl:text>;</xsl:text>
							
							<xsl:text>border: solid 1px #</xsl:text>
							<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BorderColor" />
							<xsl:text>;</xsl:text>
							
							<xsl:text>color: #</xsl:text>
							<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/TextColor" />
							<xsl:text>;</xsl:text>
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
						
						<xsl:value-of select="./Note" disable-output-escaping="yes" />
					</div>
					
					<div class="Seperator"></div>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
