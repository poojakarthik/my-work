<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add New Rate</h1>
		
		<form method="GET" action="rates_rate_list.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="constraint[Name][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Rates/Constraints/Constraint[Name=string('Name')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="constraint[Description][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Rates/Constraints/Constraint[Name=string('Description')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
					<!--
	RecordType
	ServiceType
	StdUnits
	StdRatePerUnit
	StdFlagfall
	StdPercentage
	StdMarkup
	StdMinCharge
	ExsUnits
	ExsRatePerUnit
	ExsFlagfall
	ExsPercentage
	ExsMarkup
	StartTime
	EndTime
	Monday
	Tuesday
	Wednesday
	Thursday
	Friday
	Saturday
	Sunday
	Destination
	CapUnits
	CapCost
	CapUsage
	CapLimit
	Prorate
	Fleet
	Uncapped
	-->
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
				
				<table border="0" cellpadding="5" cellspacing="0">
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
				
				<div class="Clear"></div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
