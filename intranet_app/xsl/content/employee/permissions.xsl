<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Edit Employee Permissions</h1>
		
		<script type="text/javascript" src="js/employee_permission_select.js"></script>
		
		<h2 class="Contact">Employee</h2>
		<div class="Wide-Form">
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Employee')" />
							<xsl:with-param name="field" select="string('FirstName')" />
						</xsl:call-template>
					</th>
					<td><xsl:value-of select="/Response/Employee/FirstName" /></td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Employee')" />
							<xsl:with-param name="field" select="string('LastName')" />
						</xsl:call-template>
					</th>
					<td><xsl:value-of select="/Response/Employee/LastName" /></td>
				</tr>
				<tr>
					<th class="JustifiedWidth">
						<xsl:call-template name="Label">
							<xsl:with-param name="entity" select="string('Employee')" />
							<xsl:with-param name="field" select="string('UserName')" />
						</xsl:call-template>
					</th>
					<td><xsl:value-of select="/Response/Employee/UserName" /></td>
				</tr>
			</table>
		</div>
		<div class="Seperator"></div>
		
		<form method="POST" action="employee_permissions.php">
			<xsl:attribute name="onsubmit">
				<xsl:text>return selIt ()</xsl:text>
			</xsl:attribute>
			
			<h2 class="Admin"> Permissions</h2>
			<div class="Wide-Form">
				<input type="hidden" name="Id">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/Employee/Id" />
					</xsl:attribute>
				</input>
				
				Select multiple Permissions by holding the CTRL key while you click options from
				either of the lists.
				
				<div class="SmallSeperator"></div>
				
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th>Available Permissions :</th>
						<th></th>
						<th>Selected Permissions :</th>
					</tr>
					<tr>
						<td>
							<select id="AvailablePermissions" name="AvailablePermissions[]" size="20" class="LargeSelection" multiple="multiple">
								<xsl:for-each select="/Response/Permissions/Permission">
									<xsl:sort select="./Name" />
									
									<xsl:variable name="Permission" select="." />
									
									<xsl:if test="not(/Response/PermissionList/Permission[Id=$Permission/Id])">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:if>
								</xsl:for-each>
							</select>
						</td>
						<!--TODO!bash! [  DONE  ]		URGENT make these stay the same height -they change height!!! -->
						<!--TODO!bash! [  DONE  ]		URGENT why can i select empty gaps and change their list? --> 
						<!--TODO!bash! [  DONE  ]		and why do options appear half way down the list when i change their list? -->
						<td>
							<div>
								<input type="button" value="&#0187;">
									<xsl:attribute name="onclick">
										<xsl:text>addIt ()</xsl:text>
									</xsl:attribute>
								</input>
							</div>
							<div class="Seperator"></div>
							<div>
								<input type="button" value="&#0171;">
									<xsl:attribute name="onclick">
										<xsl:text>delIt ()</xsl:text>
									</xsl:attribute>
								</input>
							</div>
						</td>
						<td>
							<select id="SelectedPermissions" name="SelectedPermissions[]" size="20" class="LargeSelection" multiple="multiple">
								<xsl:for-each select="/Response/Permissions/Permission">
									<xsl:sort select="./Name" />
									
									<xsl:variable name="Permission" select="." />
									
									<xsl:if test="/Response/PermissionList/Permission[Id=$Permission/Id]">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:if>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			<div class="Right">
				<input type="submit" class="input-submit" name="Confirm" value="Apply Changes &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
