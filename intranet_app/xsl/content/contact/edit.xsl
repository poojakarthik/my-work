<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Contact</h1>
		
		<form method="POST" action="contact_edit.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Contact/Id" disable-output-escaping="yes" />
				</xsl:attribute>
			</input>
			
			<h2 class="Contact">Contact Information</h2>
			
			<xsl:if test="/Response/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Email'">
							You must enter an Email Address for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'UserName'">
							The UserName you requested to change to already exists on an
							Active Contact. Please choose another UserName.
						</xsl:when>
						<xsl:otherwise>
							An unknown error has occurred.
						</xsl:otherwise>
					</xsl:choose>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Title')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Title" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/Title" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FirstName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/FirstName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('LastName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="LastName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/LastName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('JobTitle')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="JobTitle" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/JobTitle" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('DOB')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="DOB[year]">
									<option value="">CCYY</option>
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number('1900')" />
										<xsl:with-param name="cease" select="number('1990')" />
										<xsl:with-param name="select" select="/Response/Contact/DOB/year" />
									</xsl:call-template>
								</select> -
								<select name="DOB[month]">
									<option value="">MM</option>
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number('1')" />
										<xsl:with-param name="cease" select="number('12')" />
										<xsl:with-param name="select" select="/Response/Contact/DOB/month" />
									</xsl:call-template>
								</select> -
								<select name="DOB[day]">
									<option value="">DD</option>
									<xsl:call-template name="Date_Loop">
										<xsl:with-param name="start" select="number('1')" />
										<xsl:with-param name="cease" select="number('31')" />
										<xsl:with-param name="select" select="/Response/Contact/DOB/day" />
									</xsl:call-template>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Email')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Email" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/Email" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Phone')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Phone" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/Phone" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Mobile')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Mobile" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/Mobile" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Fax')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Fax" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/Fax" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('UserName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="UserName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/UserName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('PassWord')" />
								</xsl:call-template>
							</th>
							<td><input type="text" name="PassWord" class="input-string" /></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<strong><span class="Attention">Attention</span> :</strong> Leave this password field
								blank if you do not want to change the password.
							</td>
						</tr>
						<tr>
							<td>
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('CustomerContact')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="4" cellspacing="0">
									<tr>
										<td>
											<input type="radio" name="CustomerContact" value="1" id="CustomerContact:TRUE">
												<xsl:choose>
													<xsl:when test="/Response/Contact/CustomerContact = 1">
														<xsl:attribute name="checked">
															<xsl:text>checked</xsl:text>
														</xsl:attribute>
													</xsl:when>
												</xsl:choose>
											</input>
										</td>
										<td><label for="CustomerContact:TRUE">Yes, allow this Contact access to associated Accounts</label></td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="CustomerContact" value="0" id="CustomerContact:FALSE">
												<xsl:choose>
													<xsl:when test="/Response/Contact/CustomerContact != 1">
														<xsl:attribute name="checked">
															<xsl:text>checked</xsl:text>
														</xsl:attribute>
													</xsl:when>
												</xsl:choose>
											</input>
										</td>
										<td><label for="CustomerContact:FALSE">No, only allow this Contact access to this Account</label></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<h2>Archive Status</h2>
			<div class="Seperator"></div>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/Contact/Archived = 0">
							This Contact is <strong><span class="Green">Currently Available</span></strong>.
						</xsl:when>
						<xsl:otherwise>
							This Contact is <strong><span class="Red">Currently Archived</span></strong>.
						</xsl:otherwise>
					</xsl:choose>
					
					If you would like to change the Archive Status of this Contact, please click the button below:
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<xsl:choose>
							<xsl:when test="/Response/Contact/Archived = 1">
								<tr>
									<td><input type="checkbox" name="Archived" value="0" id="Archive:FALSE" /></td>
									<td>
										<label for="Archive:FALSE">
											Make this Contact <strong><span class="Green">Available</span></strong> and active
										</label>
									</td>
								</tr>
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<td><input type="checkbox" name="Archived" value="1" id="Archive:TRUE" /></td>
									<td>
										<label for="Archive:TRUE">
											Make this Contact <strong><span class="Red">Archived</span></strong> and unavailable
										</label>
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
						<tr>
							<td></td>
							<td>
								<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
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
