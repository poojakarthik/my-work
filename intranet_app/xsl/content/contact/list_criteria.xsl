<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<form method="post" action="contact_list.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					In order for you to take a phone call from a customer, you must
					authenticate the customer over the phone by matching the following
					information in the fields that are listed below.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td colspan="2">
								You must match <strong>at least one</strong> of the following fields:
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Account" class="input-string" />
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ABN" class="input-ABN" />
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ACN" class="input-ACN" />
							</td>
						</tr>
						<tr>
							<td coslpan="2">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								You must match <strong>everyone one</strong> of the following fields<br />
								(Except for the Date of Birth - which must be matched only if it exists):
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FirstName" class="input-string" />
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('LastName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="LastName" class="input-string" />
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('DOB')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="DOB[day]">
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number (1)" />
										<xsl:with-param name="cease" select="number (31)" />
										<xsl:with-param name="steps" select="number (1)" />
									</xsl:call-template>
								</select>
								/
								<select name="DOB[day]">
									<option value="1">(01) JAN</option>
									<option value="2">(02) FEB</option>
									<option value="3">(03) MAR</option>
									<option value="4">(04) APR</option>
									<option value="5">(05) MAY</option>
									<option value="6">(06) JUN</option>
									<option value="7">(07) JUL</option>
									<option value="8">(08) AUG</option>
									<option value="9">(09) SEP</option>
									<option value="10">(10) OCT</option>
									<option value="11">(11) NOV</option>
									<option value="12">(12) DEC</option>
								</select>
								/
								<select name="DOB[day]">
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number (1901)" />
										<xsl:with-param name="cease" select="number (1990)" />
										<xsl:with-param name="steps" select="number (1)" />
									</xsl:call-template>
								</select>
							</td>
						</tr>
						<tr>
							<td coslpan="2">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" class="input-submit" value="Find Contact &#0187;" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</xsl:template>
	
	<xsl:template name="Date_Loop">
		<xsl:param name="start">1</xsl:param>
		<xsl:param name="cease">0</xsl:param>
		<xsl:param name="steps">1</xsl:param>
		<xsl:param name="count">0</xsl:param>
		
		<xsl:if test="number($start) + number($count) &lt;= number($cease)">
			<option>
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="$start + $count" />
				</xsl:attribute>
				
				<xsl:value-of select="$start + $count" />
			</option>
			<xsl:call-template name="Date_Loop">
				<xsl:with-param name="start" select="$start" />
				<xsl:with-param name="cease" select="$cease" />
				<xsl:with-param name="steps" select="$steps" />
				<xsl:with-param name="count" select="$count + $steps" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
