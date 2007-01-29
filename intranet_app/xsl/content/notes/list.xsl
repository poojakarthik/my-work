<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>
			<xsl:choose>
				<xsl:when test="/Response/AccountGroup">
					View Account Group Notes
				</xsl:when>
				<xsl:when test="/Response/Account">
					View Account Notes
				</xsl:when>
				<xsl:when test="/Response/Service">
					View Service Notes
				</xsl:when>
				<xsl:when test="/Response/Contact">
					View Contact Notes
				</xsl:when>
			</xsl:choose>
		</h1>
		
		<xsl:choose>
			<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
				<div class="MsgNoticeNarrow">
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
										<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
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
