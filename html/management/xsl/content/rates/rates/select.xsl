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
					<xsl:value-of select="/Response/Rate/Name" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ServiceType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Rate/ServiceTypes/ServiceType[@selected='selected']/Id" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="RecordType">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Rate/RecordType/Id" />
				</xsl:attribute>
			</input>
			
			<xsl:if test="/Response/Rate/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Rate/Error = 'Blank'">
							Please enter a Rate Name.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'Exists'">
							The Rate Name you entered already exists.  Please enter a unique Rate Name.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'ServiceType'">
							Please select a valid Service Type
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'Hours'">
							Please enter valid Start/Cease Times.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'StandardUnits'">
							Please enter a valid value for Standard Units.  Standard Units should be a whole number, and should not be zero.
						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'StandardRateSelection'">
							Please select a valid Standard Rate.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'CapCalculationSelection'">
							Please enter a value for your selected Cap Type.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'CapLimitingSelection'">
							Please enter a value for your selected Cap Limits Type.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'ExcessRateSelection'">
							Please select a valid Excess Rate.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'StartTimeInvalid'">
							Please enter a valid Start Time.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'EndTimeInvalid'">
							Please enter a valid Cease Time.

						</xsl:when>
						<xsl:when test="/Response/Rate/Error = 'Weekday'">
							Please select a minimum of one day of the week.

						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Rate/Name" /></td>
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
										<xsl:value-of select="/Response/Rate/Name" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Rate/ServiceTypes/ServiceType[@selected='selected']/Name" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Record Type')" />
									<xsl:with-param name="field" select="string('RecordType')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Rate/RecordType/Name" />
							</td>
						</tr>
					</table>
				</div>
				
				<div class="Clear"></div>
			</div>
				
			<div class="Seperator"></div>
			

			<div class="Wide-Form">
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
				
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
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
				
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
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
			

			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
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
			
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
						<tr>
							<td><input type="radio" name="CapCalculation" value="" checked="checked" /></td>
							<th class="JustifiedWidth">No Cap</th>
							<td></td>
						</tr>
						<tr>
							<td><input type="radio" name="CapCalculation" value="CapUnits" /></td>
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
							<td><input type="radio" name="CapCalculation" value="CapCost" /></td>
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
				
				<div class="Form-Content Left Indent" id="CapLimit">
					<table border="0" cellpadding="1" cellspacing="0">
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
					
					<div class="Form-Content Left Indent" id="Excess">
						<table border="0" cellpadding="1" cellspacing="0">
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
			
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="1" cellspacing="0">
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
