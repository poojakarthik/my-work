<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Employee</h1>
		
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
		
		<form method="POST" action="employee_edit.php">
			<h2 class= "Contact"> Employee Details</h2>
			
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
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
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
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Extension')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Extension" class="input-string" maxlength="25">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Extension" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
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
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('New-PassWord')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="password" name="PassWord[0]" class="input-string" />
							</td>
							<td>
								<strong><span class="Attention">Attention</span> :</strong>
								Leave these Password fields blank if you don't wish to 
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('Repeat-PassWord')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="password" name="PassWord[1]" class="input-string" />
							</td>
							<td>
								change the Password of this Employee.
							</td>
						</tr>
					</table>
				</div>
			</div>


			<div class="Seperator"></div>
			
			<h2 class = "Archive">Archive Status</h2>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/Employee/Archived = 0">
							This Employee is <strong><span class="Green">Currently Available</span></strong>.
							If you would like to make this Employee Archived, please click the button below:
							<div class="Seperator"></div>
							
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="checkbox" name="Archived" value="1" id="Archive:TRUE">
											<xsl:if test="/Response/ui-values/Archived = '1'">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="Archive:TRUE">
											Make this Employee <strong><span class="Red">Archived</span></strong> and unavailable
										</label>
									</td>
								</tr>
							</table>
						</xsl:when>
						<xsl:otherwise>
							This Employee is <strong><span class="Red">Currently Archived</span></strong>.
							If you would like to make this Employee Available, please click the button below:
							<div class="Seperator"></div>
							
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="checkbox" name="Archived" value="0" id="Archive:FALSE">
											<xsl:if test="/Response/ui-values/Archived = '1'">
												<xsl:attribute name="checked">
													<xsl:text>checked</xsl:text>
												</xsl:attribute>
											</xsl:if>
										</input>
									</td>
									<td>
										<label for="Archive:FALSE">
											Make this Employee <strong><span class="Green">Available</span></strong> and active
										</label>
									</td>
								</tr>
							</table>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</div>
			
			<div class="SmallSeperator"></div>
			<div class = "Right">
			<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
