<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Employee</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Password Mismatch'">
						Your passwords mismatched. Please try again.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="employee_edit.php">
			<h2>Personal Details</h2>
			<div class="Seperator"></div>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Employee/Id" />
						</xsl:attribute>
					</input>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FirstName" class="input-string">
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
								<input type="text" name="LastName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/LastName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<h2>Employee Authentication</h2>
			<div class="Seperator"></div>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Employee')" />
									<xsl:with-param name="field" select="string('UserName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="UserName" class="input-string">
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
						</tr>
						<tr>
							<th></th>
							<td>
								<div style="width: 350px">
									<strong><span class="Attention">Attention</span> :</strong>
									Leave these Password fields blank if you don't wish to 
									change the Password of this Employee.
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2>Archive Status</h2>
			<div class="Seperator"></div>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<xsl:choose>
						<xsl:when test="/Response/Employee/Archived = 0">
							This Employee is <strong><span class="Green">Currently Available</span></strong>.
							If you would like to make this Employee Archived, please click the button below:
							<div class="Seperator"></div>
							
							<table border="0" cellpadding="5" cellspacing="0">
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
							
							<table border="0" cellpadding="5" cellspacing="0">
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
			
			<div class="Seperator"></div>
			
			<input type="submit" class="input-submit" value="Apply Changes &#0187;" />
		</form>
	</xsl:template>
</xsl:stylesheet>
