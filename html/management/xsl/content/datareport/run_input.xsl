<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Run Data Report</h1>
		
		<form method="post" action="datareport_run.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/DataReport/Id" />
				</xsl:attribute>
			</input>
			
			<xsl:if test="/Response/NoResults">
				<div class = "MsgNoticeWide">
					There were no results for your requested Data Report.  Please try a different set of constraints.
				</div>
			</xsl:if>
			
			<h2>Report Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Report')" />
								<xsl:with-param name="field" select="string('Name')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/DataReport/Name" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Report')" />
								<xsl:with-param name="field" select="string('Summary')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/DataReport/Summary" /></td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2>Report Select Options</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<xsl:for-each select="/Response/Selects/Select">
						<tr>
							<td>
								<input type="checkbox" name="select[]" checked="checked">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="./Name" />
									</xsl:attribute>
								</input>
							</td>
							<th><xsl:value-of select="./Name" /></th>
						</tr>
					</xsl:for-each>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<xsl:if test="count(/Response/Inputs/Input) != 0">
				<h2>Report Constraint Input</h2>
				<div class="Wide-Form">
					<table border="0" cellpadding="3" cellspacing="0">
						<xsl:for-each select="/Response/Inputs/Input">
							<tr>
								<th class="JustifiedWidth" valign="top">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="./Documentation-Entity" />
										<xsl:with-param name="field" select="./Documentation-Field" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:choose>
										<!-- dataBoolean -->
										<xsl:when test="./Type = 'dataBoolean'">
											<table border="0" cellpadding="3" cellspacing="0">
												<tr>
													<td>
														<input type="radio" value="1">
															<xsl:attribute name="name">
																<xsl:text>input[</xsl:text>
																<xsl:value-of select="./Name" />
																<xsl:text>]</xsl:text>
															</xsl:attribute>
														</input>
													</td>
													<th>Yes</th>
												</tr>
												<tr>
													<td>
														<input type="radio" value="0">
															<xsl:attribute name="name">
																<xsl:text>input[</xsl:text>
																<xsl:value-of select="./Name" />
																<xsl:text>]</xsl:text>
															</xsl:attribute>
														</input>
													</td>
													<th>No</th>
												</tr>
											</table>
										</xsl:when>
										<!-- dataString -->
										<xsl:when test="./Type = 'dataString'">
											<input type="text" class="input-string">
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>]</xsl:text>
												</xsl:attribute>
											</input>
										</xsl:when>
										<!-- dataInteger -->
										<xsl:when test="./Type = 'dataInteger'">
											<input type="text" class="input-string">
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>]</xsl:text>
												</xsl:attribute>
											</input>
										</xsl:when>
										<!-- dataDate -->
										<xsl:when test="./Type = 'dataDate'">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][day]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('1')" />
													<xsl:with-param name="cease" select="number('31')" />
												</xsl:call-template>
											</select> /
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][month]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('1')" />
													<xsl:with-param name="cease" select="number('12')" />
												</xsl:call-template>
											</select> /
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][year]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('2000')" />
													<xsl:with-param name="cease" select="number('2010')" />
												</xsl:call-template>
											</select>
										</xsl:when>
										<!-- dataDatetime -->
										<xsl:when test="./Type = 'dataDatetime'">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][day]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('1')" />
													<xsl:with-param name="cease" select="number('31')" />
												</xsl:call-template>
											</select> /
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][month]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('1')" />
													<xsl:with-param name="cease" select="number('12')" />
												</xsl:call-template>
											</select> /
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][year]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('2000')" />
													<xsl:with-param name="cease" select="number('2010')" />
												</xsl:call-template>
											</select>
											<xsl:text> </xsl:text>
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][hour]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('0')" />
													<xsl:with-param name="cease" select="number('24')" />
												</xsl:call-template>
											</select> :
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][minute]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('0')" />
													<xsl:with-param name="cease" select="number('59')" />
												</xsl:call-template>
											</select> :
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][second]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('0')" />
													<xsl:with-param name="cease" select="number('59')" />
												</xsl:call-template>
											</select>
										</xsl:when>
										<!-- StatementSelect -->
										<xsl:when test="./Type = 'StatementSelect'">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>]</xsl:text>
												</xsl:attribute>
												
												<xsl:for-each select="./Options/Option">
													<option>
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:value-of select="./@Label" />
													</option>
												</xsl:for-each>
											</select>
										</xsl:when>
										<!-- Query -->
										<xsl:when test="./Type = 'Query'">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>]</xsl:text>
												</xsl:attribute>
												
												<xsl:for-each select="./Options/Option">
													<option>
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:value-of select="./@Label" />
													</option>
												</xsl:for-each>
											</select>
										</xsl:when>
										<!-- NoteTypes -->
										<xsl:when test="./Type = 'NoteTypes'">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>]</xsl:text>
												</xsl:attribute>
												
												<xsl:for-each select="./Value/NoteTypes/NoteType">
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
										</xsl:when>
										<!-- dataEnumerative -->
										<xsl:when test="count(./Value[/*/Results]) = 0">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>]</xsl:text>
												</xsl:attribute>
												
												<xsl:for-each select="./Value/ServiceTypes/ServiceType">
													<option>
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="./Id" />
														</xsl:attribute>
														<xsl:value-of select="./Name" />
													</option>
												</xsl:for-each>
											</select>
										</xsl:when>
									</xsl:choose>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			
			<h2>Report Limit</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Maximum Results :</th>
						<td>
							<input type="text" name="limit" class="input-string" />
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2>Output Format</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth" valign="top">Output Format :</th>
						<td>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="radio" value="0" name="outputcsv">
											<xsl:if test="/Response/ForceRenderTarget = 0 or not(string(/Response/ForceRenderTarget))">
												<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:if test="string(/Response/ForceRenderTarget) and /Response/ForceRenderTarget = 1">
												<xsl:attribute name="disabled" />
											</xsl:if>
										</input>
									</td>
									<th>Excel 5 (XLS)</th>
								</tr>
								<tr>
									<td>
										<input type="radio" value="1" name="outputcsv">
											<xsl:if test="/Response/ForceRenderTarget = 1">
												<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:if test="string(/Response/ForceRenderTarget) and /Response/ForceRenderTarget = 0">
												<xsl:attribute name="disabled" />
											</xsl:if>
										</input>
									</td>
									<th>CSV</th>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<div class="Right">
				<input type="submit" class="input-submit" name="Confirm" value="Run Report &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
