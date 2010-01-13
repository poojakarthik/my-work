<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add Employee</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Email'">
						You did not enter a valid Email. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Phone'">
						You did not enter a valid Phone Number. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Mobile'">
						You did not enter a valid Mobile Number. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Username Empty'">
						You did not fill the required fields. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Password Mismatch'">
						Your Passwords did not match.  Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'Password Empty'">
						You did not enter a valid Password. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'UserName Obtained Elsewhere'">
						The Username you entered is in use on another Employee. Please try again.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="employee_add.php">
			<h2 class ="Contact">Employee Details</h2>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Employee/Id" />
						</xsl:attribute>
					</input>
					
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FirstName" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/FirstName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('LastName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="LastName" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/LastName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Email')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Email" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Email" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Extension')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Extension" class="input-string" maxlength="15">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Extension" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Phone')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Phone" class="input-string" maxlength="25">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Phone" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Mobile')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Mobile" class="input-string" maxlength="25">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Mobile" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('DOB')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="DOB">
									<xsl:with-param name="Name-Day"			select="string('DOB-day')" />
									<xsl:with-param name="Name-Month"		select="string('DOB-month')" />
									<xsl:with-param name="Name-Year"		select="string('DOB-year')" />
									<xsl:with-param name="Selected-Day"		select="/Response/ui-values/DOB/day" />
									<xsl:with-param name="Selected-Month"	select="/Response/ui-values/DOB/month" />
									<xsl:with-param name="Selected-Year"	select="/Response/ui-values/DOB/year" />
									<xsl:with-param name="Now"				select="/Response/Now" />
									<xsl:with-param name="Minimum-Age"		select="14" />
								</xsl:call-template>
							</td>
						</tr>
						<tr>
							<td width="10"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('UserName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="UserName" class="input-string" maxlength="31">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/UserName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td width="10"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('New-PassWord')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="password" name="PassWord[0]" class="input-string" />
							</td>
						</tr>
						<tr>
							<td width="10"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Repeat-PassWord')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="password" name="PassWord[1]" class="input-string" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>

			<div class="Right">
			<input type="submit" class="input-submit" value="Add Employee&#0187;" />
			</div>
		</form>
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
