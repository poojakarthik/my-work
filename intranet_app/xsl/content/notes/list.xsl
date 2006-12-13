<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time"
	xmlns:func="http://exslt.org/functions" xmlns:date="http://exslt.org/dates-and-times" extension-element-prefixes="date">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	
	<xsl:import href="../../lib/date-difference/date.difference.function.xsl"/>
	<xsl:import href="../../lib/date-difference/date.difference.template.xsl"/>
	
	<xsl:template name="Content">
		<h1>Note Listing</h1>
		<div class="Seperator"></div>
		
		<xsl:if test="/Response/AccountGroup">
			<h2>Account Group Information</h2>
			<div class="Seperator"></div>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account Group')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/AccountGroup/Id" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<xsl:if test="/Response/Account">
			<h2>Account Information</h2>
			<div class="Seperator"></div>
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th>
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
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
		</xsl:if>
		
		<h2>Notes</h2>
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
	</xsl:template>
</xsl:stylesheet>
