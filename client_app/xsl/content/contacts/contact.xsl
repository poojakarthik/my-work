<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h2 class="Contact">Viewing Contact Information</h2>
		
		<h3>Contact Details</h3>
		<p>
			Details about this particular contact.
		</p>
		
		<form method="post" action="contact_profile.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Contact/Id" />
				</xsl:attribute>
			</input>
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<th>Title:</th>
					<td>
						<input type="text" name="Title" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/Title" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>First Name:</th>
					<td>
						<input type="text" name="FirstName" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/FirstName" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Last Name:</th>
					<td>
						<input type="text" name="LastName" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/LastName" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Date of Birth:</th>
					<td>
						<select name="DOB_day">
							<xsl:call-template name="Date_Loop">
								<xsl:with-param name="start" select="number (1)" />
								<xsl:with-param name="cease" select="number (31)" />
								<xsl:with-param name="steps" select="number (1)" />
								<xsl:with-param name="select" select="/Response/Contact/DOB/day" />
							</xsl:call-template>
						</select> -
						<select name="DOB_month">
							<xsl:call-template name="Date_Loop">
								<xsl:with-param name="start" select="number (1)" />
								<xsl:with-param name="cease" select="number (12)" />
								<xsl:with-param name="steps" select="number (1)" />
								<xsl:with-param name="select" select="/Response/Contact/DOB/month" />
							</xsl:call-template>
						</select> -
						<select name="DOB_year">
							<xsl:call-template name="Date_Loop">
								<xsl:with-param name="start" select="number (1901)" />
								<xsl:with-param name="cease" select="number (1990)" />
								<xsl:with-param name="steps" select="number (1)" />
								<xsl:with-param name="select" select="/Response/Contact/DOB/year" />
							</xsl:call-template>
						</select>
					</td>
				</tr>
				<tr>
					<th>Job Title:</th>
					<td>
						<input type="text" name="JobTitle" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/JobTitle" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Email Address:</th>
					<td>
						<input type="text" name="Email" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/Email" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Phone:</th>
					<td>
						<input type="text" name="Phone" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/Phone" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Mobile:</th>
					<td>
						<input type="text" name="Mobile" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/Mobile" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Fax:</th>
					<td>
						<input type="text" name="Fax" class="text">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Contact/Fax" />
							</xsl:attribute>
						</input>
					</td>
				</tr>
			</table>
			
			<p>
				<input type="submit" value="Change Profile &#0187;" class="button" />
			</p>
		</form>
		
		<hr style="margin-top: 20px; margin-bottom: 20px;" />
		
		<h3>Change Password</h3>
		<p>
			Change the password you use to log into your account.
		</p>
		
		<form method="post" action="contact_password.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Contact/Id" />
				</xsl:attribute>
			</input>
			
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<th>UserName:</th>
					<td><xsl:value-of select="/Response/Contact/UserName" /></td>
				</tr>
				<tr>
					<th>* Your Password:</th>
					<td><input type="password" name="My_PassWord" class="text" /></td>
				</tr>
				<tr>
					<th>New Password:</th>
					<td><input type="password" name="New_PassWord[0]" class="text" /></td>
				</tr>
				<tr>
					<th>Repeat Password:</th>
					<td><input type="password" name="New_PassWord[1]" class="text" /></td>
				</tr>
			</table>
			
			<p>* - Your current password you use to login to TecloBlue.</p>
			
			<p>
				<input type="submit" value="Change Password &#0187;" class="button" />
			</p>
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
