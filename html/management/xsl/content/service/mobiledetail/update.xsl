<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">	
		<h1>Mobile Provisioning</h1>
		<script language="javascript" src="js/note_add.js"></script>
		
		<table border="0" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<!--Service Details -->
					<h2 class="Service">Service Details</h2>
					<div class="Narrow-Form">
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
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('Id')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Service/Id" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('FNN')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Service/FNN" /></td>
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
						</table>
					</div>
					
					<div class="Seperator"></div>
						
					<form method="post" action="service_mobile_details.php">
						<input type="hidden" name="Service">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Service/Id" />
							</xsl:attribute>
						</input>
						
						<h2 class="Service">Mobile Details</h2>
						
						<!--TODO!bash! [  DONE  ]		this is showing up even when there are details!!!-->
						<xsl:if test="not(/Response/MobileDetail)">
							<div class="MsgNoticeNarrow">
								<strong><span class="Attention">Notice</span> :</strong>
								No Mobile Details found.
							</div>
						</xsl:if>
						
						<div class="Narrow-Form">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Mobile')" />
											<xsl:with-param name="field" select="string('SimPUK')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="SimPUK" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ui-values/SimPUK" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Mobile')" />
											<xsl:with-param name="field" select="string('SimESN')" />
										</xsl:call-template>
									</th>
									<td>
										<input type="text" name="SimESN" class="input-string">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="/Response/ui-values/SimESN" />
											</xsl:attribute>
										</input>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Mobile')" />
											<xsl:with-param name="field" select="string('SimState')" />
										</xsl:call-template>
									</th>
									<!-- TODO!flame! (originally Bash) You need to base state and date of birth on contact details-->
									<!-- TODO!flame! 
										Recommendation: Because this is the information stored on the service's server (eg. UNITEL),
										I dont' know if this is since a good idea.
									-->
									<td>
										<select name="SimState">
											<xsl:for-each select="/Response/ServiceStateTypes/ServiceStateType">
												<option>
													<xsl:attribute name="value">
														<xsl:text></xsl:text>
														<xsl:value-of select="./Id" />
													</xsl:attribute>
													<xsl:if test="./Id = /Response/ui-values/SimState">
														<xsl:attribute name="selected">
															<xsl:text>selected</xsl:text>
														</xsl:attribute>
													</xsl:if>
													<xsl:text></xsl:text>
													<xsl:value-of select="./Name" />
												</option>
											</xsl:for-each>
										</select>
									</td>
								</tr>
								<!--TODO!bash! Urgent - do not show dates which allow the person to be <18-->
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Mobile')" />
											<xsl:with-param name="field" select="string('DOB')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:call-template name="DOB">
											<xsl:with-param name="Name-Day"			select="string('DOB[day]')" />
											<xsl:with-param name="Name-Month"		select="string('DOB[month]')" />
											<xsl:with-param name="Name-Year"		select="string('DOB[year]')" />
											<xsl:with-param name="Selected-Day"		select="/Response/ui-values/DOB-day" />
											<xsl:with-param name="Selected-Month"	select="/Response/ui-values/DOB-month" />
											<xsl:with-param name="Selected-Year"	select="/Response/ui-values/DOB-year" />
											<xsl:with-param name="Now"				select="/Response/Now" />
										</xsl:call-template>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth" valign="top">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Service Mobile')" />
											<xsl:with-param name="field" select="string('Comments')" />
										</xsl:call-template>
									</th>
									<td>
										<textarea name="Comments" class="input-summary">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/ui-values/Comments" />
										</textarea>
									</td>
								</tr>
							</table>
						</div>
						<div class="SmallSeperator"></div>
						<div class="Right">
							<input type="submit" value="Apply Changes &#0187;" class="input-submit" />
						</div>
					</form>
				</td>
				
				<td class="ColumnSpacer"></td>
				
				<td valign="top">
					
					<!-- Notes -->
					<h2 class="Notes">Service Notes</h2>
					
					<form method="post" action="note_add.php" onsubmit="return noteAdd (this)" name="NoteAdd">
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
						Type new note for this service in the field below:
						<textarea name="Note" class="input-summary" rows="6" />
						
						<div>
							<input type="checkbox" name="Account" CHECKED="CHECKED">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
							</input>
							Show this note in Account Notes.
						</div>
						
						<select class="Left" name="NoteType">
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
									<xsl:value-of select="./TypeLabel" />
								</option>
							</xsl:for-each>
						</select>
						
						<div class="Right">
							<input type="submit" value="Add Note &#0187;" class="input-submit-disabled" disabled="disabled" />
						</div>
						<div class="Clear"></div>
					</form>
					
					<!-- Recent Notes -->
					<h3>Recent Notes</h3>
					<xsl:choose>
						<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
							There are no notes currently attached to this Service.
						</xsl:when>
						<xsl:otherwise>
							The 5 most recent notes are listed below:
							<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
								<xsl:variable name="Note" select="." />
								<div class="SmallSeperator"></div>
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
							</xsl:for-each>
							<div class="Right">
								<a href="#" title="Service Notes" alt="Notes for this Service">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, 'note_list.php?Service=</xsl:text>
										<xsl:value-of select="/Response/Service/Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View All Service Notes</xsl:text>
								</a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					<div class="Seperator"></div>
				</td>
			</tr>
		</table>
		<div class="Clear"></div>
	</xsl:template>
	
	
	
	<xsl:template name="Date_Loop">
		<xsl:param name="start">1</xsl:param>
		<xsl:param name="cease">0</xsl:param>
		<xsl:param name="steps">1</xsl:param>
		<xsl:param name="count">0</xsl:param>
		
		<xsl:param name="select">0</xsl:param>
		
		<xsl:if test="number($start) + number($count) &lt;= number($cease)">
			<option>
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="$start + $count" />
				</xsl:attribute>
				
				<xsl:choose>
					<xsl:when test="$select = $start + $count">
						<xsl:attribute name="selected">
							<xsl:text>selected</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:value-of select="$start + $count" />
			</option>
			<xsl:call-template name="Date_Loop">
				<xsl:with-param name="start" select="$start" />
				<xsl:with-param name="cease" select="$cease" />
				<xsl:with-param name="steps" select="$steps" />
				<xsl:with-param name="count" select="$count + $steps" />
				<xsl:with-param name="select" select="$select" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
