<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Rate</h1>
		
		<form method="POST" action="rates_rate_add.php">
			<input type="hidden" name="Name">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Rate/Name" disable-output-escaping="yes" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ServiceType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Rate/NamedServiceTypes/ServiceType[@selected='selected']/Id" disable-output-escaping="yes" />
				</xsl:attribute>
			</input>
			
			<xsl:if test="/Response/Rate/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/Rate/Error = 'Blank'">
							Your Rate Name and your ServiceType must not be Blank.
							Please try again.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'Exists'">
							The Rate Name that you entered already exists in the System.
							Please choose another Rate Name.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'ServiceType'">
							You have selected an invalid ServiceType. Please select a 
							service type from the list below.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'Hours'">
							You have incorrectly set an Hour value for your Start and Cease
							times. Please correct this and try again.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'StandardUnits'">
							Standard units must be a whole number and not zero. Please check
							this value and try again.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'StandardRateSelection'">
							The Value that you Entered for your Standard Rate Selection
							was Invalid. Please enter an appropriate value for this selection.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'CapCalculationSelection'">
							You must enter a value for your selected Cap type. Please
							fill this feel and resubmit your Rate.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'CapLimitingSelection'">
							You must enter a value for your selected Cap Limits type. Please
							fill this feel and resubmit your Rate.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'ExcessRateSelection'">
							You must select and enter a valid Excess Rate. Please correct this
							and resubmit your Rate.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'StartTimeInvalid'">
							There was an error with your Start time. Please check this value.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'EndTimeInvalid'">
							There was an error with your Cease time. Please check this value.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'Weekday'">
							You must select at least one of the Week Days. Please check this and
							try resubmitting this form.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Rate/Name" disable-output-escaping="yes" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Description" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Rate/Name" disable-output-escaping="yes" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Rate/NamedServiceTypes/ServiceType[@selected='selected']/Name" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('RecordType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="RecordType">
									<xsl:for-each select="/Response/Rate/RecordTypeSearch/Results/rangeSample/RecordType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="@selected='selected'">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
				</div>
				
				<div class="Clear"></div>
			</div>
				
			<div class="Seperator"></div>
			

			<div class="Filter-Form">
				<script src="js/date_selection.js" type="text/javascript"></script>
				
				<div id="weekScheduler_Constraint">
					<div id="weekScheduler_Container">
						<div id="weekScheduler_Meridians" class="Meridian">
							<div>AM</div>
							<div>PM</div>
						</div>
						<div id="weekScheduler_Hours" class="Hour">
							<div>12</div>
							<div>1</div>
							<div>2</div>
							<div>3</div>
							<div>4</div>
							<div>5</div>
							<div>6</div>
							<div>7</div>
							<div>8</div>
							<div>9</div>
							<div>10</div>
							<div>11</div>
							<div>12</div>
							<div>1</div>
							<div>2</div>
							<div>3</div>
							<div>4</div>
							<div>5</div>
							<div>6</div>
							<div>7</div>
							<div>8</div>
							<div>9</div>
							<div>10</div>
							<div>11</div>
						</div>
						<div id="weekScheduler_Content">
							<div id="weekScheduler_12AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_01AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_02AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_03AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_04AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_05AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_06AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_07AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_08AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_09AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_10AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_11AM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_12PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_01PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_02PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_03PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_04PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_05PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_06PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_07PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_08PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_09PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_10PM" class="weekScheduler_SelectableTime"></div>
							<div id="weekScheduler_11PM" class="weekScheduler_SelectableTime"></div>
						</div>
					
						<div class="Clear"></div>
					</div>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Seperator"></div>
				
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StartTime')" />
								</xsl:call-template>
							</th>
							<td><input type="text" id="StartTime" name="StartTime" class="input-label time" readonly="readonly" value="00:00" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('EndTime')" />
								</xsl:call-template>
							</th>
							<td><input type="text" id="EndTime" name="EndTime" class="input-label time" readonly="readonly" value="00:59" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Duration')" />
								</xsl:call-template>
							</th>
							<td><input type="text" id="Duration" name="Duration" class="input-label time" readonly="readonly" value="00:59" /></td>
						</tr>
					</table>
				</div>
				
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('DayOfWeek')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="0" cellspacing="0" class="bevelledHeading">
									<tr>
										<th>MON</th>
										<th>TUE</th>
										<th>WED</th>
										<th>THU</th>
										<th>FRI</th>
										<th>SAT</th>
										<th>SUN</th>
									</tr>
									<tr>
										<td><input type="checkbox" name="Monday" value="1" /></td>
										<td><input type="checkbox" name="Tuesday" value="1" /></td>
										<td><input type="checkbox" name="Wednesday" value="1" /></td>
										<td><input type="checkbox" name="Thursday" value="1" /></td>
										<td><input type="checkbox" name="Friday" value="1" /></td>
										<td><input type="checkbox" name="Saturday" value="1" /></td>
										<td><input type="checkbox" name="Sunday" value="1" /></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				
				<div class="Clear"></div>
			</div>
				
			<div class="Seperator"></div>
			

			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StdUnits')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="StdUnits" class="input-string" />
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="StdChargeType" value="StdRatePerUnit" checked="checked" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StdRatePerUnit')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="StdRatePerUnit" class="input-string" />
								per Standard Unit
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="StdChargeType" value="StdMarkup" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StdMarkup')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="StdMarkup" class="input-string" />
								per Standard Unit
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="StdChargeType" value="StdPercentage" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StdPercentage')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="StdPercentage" class="input-string" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StdMinCharge')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="StdMinCharge" class="input-string" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('StdFlagfall')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="StdFlagfall" class="input-string" />
							</td>
						</tr>
					</table>
				</div>
				<div class="Clear"></div>
			</div>
			
			<div class="Clear"></div>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<td><input type="radio" name="CapCalculation" value="" checked="checked" /></td>
							<th class="JustifiedWidth">No Cap</th>
							<td></td>
						</tr>
						<tr>
							<td><input type="radio" name="CapCalculation" value="StdCapUnits" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('CapUnits')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="CapUnits" class="input-string" />
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="CapCalculation" value="StdCapCost" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('CapCost')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="CapCost" class="input-string" />
							</td>
						</tr>
					</table>
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
				<div class="Seperator"></div>
				
				<div class="Filter-Form-Content Left Indent" id="CapLimit">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<td><input type="radio" name="CapLimiting" value="" checked="checked" /></td>
							<th class="JustifiedWidth">No Cap Limits</th>
							<td></td>
						</tr>
						<tr>
							<td><input type="radio" name="CapLimiting" value="CapLimit" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('CapLimit')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="CapLimit" class="input-string" />
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="CapLimiting" value="CapUsage" /></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('CapUsage')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="CapUsage" class="input-string" />
							</td>
						</tr>
					</table>
					<div class="Clear"></div>
					<div class="Seperator"></div>
					
					<div class="Filter-Form-Content Left Indent" id="Excess">
						<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
							<tr>
								<td></td>
								<th class="JustifiedWidth" valign="top">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Rate')" />
										<xsl:with-param name="field" select="string('ExsUnits')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ExsUnits" class="input-string" />
								</td>
							</tr>
							<tr>
								<td><input type="radio" name="ExsChargeType" value="ExsRatePerUnit" checked="checked" /></td>
								<th class="JustifiedWidth" valign="top">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Rate')" />
										<xsl:with-param name="field" select="string('ExsRatePerUnit')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ExsRatePerUnit" class="input-string" />
									per Standard Unit
								</td>
							</tr>
							<tr>
								<td><input type="radio" name="ExsChargeType" value="ExsMarkup" /></td>
								<th class="JustifiedWidth" valign="top">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Rate')" />
										<xsl:with-param name="field" select="string('ExsMarkup')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ExsMarkup" class="input-string" />
									per Standard Unit
								</td>
							</tr>
							<tr>
								<td><input type="radio" name="ExsChargeType" value="ExsPercentage" /></td>
								<th class="JustifiedWidth" valign="top">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Rate')" />
										<xsl:with-param name="field" select="string('ExsPercentage')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ExsPercentage" class="input-string" />
								</td>
							</tr>
							<tr>
								<td></td>
								<th class="JustifiedWidth" valign="top">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Rate')" />
										<xsl:with-param name="field" select="string('ExsFlagfall')" />
									</xsl:call-template>
								</th>
								<td>
									<input type="text" name="ExsFlagfall" class="input-string" />
								</td>
							</tr>
						</table>
					</div>
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
			</div>
			
			<div class="Clear"></div>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Prorate')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="checkbox" name="Prorate" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Fleet')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="checkbox" name="Fleet" />
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Uncapped')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="checkbox" name="Uncapped" />
							</td>
						</tr>
					</table>
				</div>
				
				
				<div class="Clear"></div>
				<div class="Seperator"></div>
				
				<input type="submit" value="Submit" class="input-submit" />
				
				<div class="Clear"></div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
