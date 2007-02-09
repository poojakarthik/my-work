<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!--Page for adding a new contact -->
		<h1>Add Contact</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>

		<form method="POST" action="contact_add.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/Id" /></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
						</tr>
						<!--Check for Trading Name-->
						<xsl:choose>
							<xsl:when test="/Response/Account/TradingName = ''">
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Account')" />
											<xsl:with-param name="field" select="string('TradingName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2 class="Contact">Contact Details</h2>
			
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Title'">
							Please enter a Title.
						</xsl:when>
						<xsl:when test="/Response/Error = 'FirstName'">
							Please enter a First Name.
						</xsl:when>
						<xsl:when test="/Response/Error = 'LastName'">
							Please enter a Last Name.
						</xsl:when>
						<xsl:when test="/Response/Error = 'DOB'">
							Please enter a valid Date of Birth.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Email'">
							Please enter an Email Address.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Email Invalid'">
							Please enter a valid Email Address.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Phones Empty'">
							Please enter a Contact Number.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Phone Invalid'">
							Please enter a valid Phone Number.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Mobile Invalid'">
							Please enter a valid Mobile Number.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Fax Invalid'">
							Please enter a valid Fax Number.
						</xsl:when>
						<xsl:when test="/Response/Error = 'UserName Empty'">
							Please enter a Username.
						</xsl:when>
						<xsl:when test="/Response/Error = 'UserName Exists'">
							The Username you entered already exists. Please enter a unique Username.
						</xsl:when>
						<xsl:when test="/Response/Error = 'PassWord'">
							Please enter a Password.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
					<!--TODO!bash! [  DONE  ]		URGENT! Make title a drop down box! -->
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Title')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Title">
									<option value=""></option>
									<xsl:for-each select="/Response/TitleTypes/TitleType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="./Id = /Response/ui-values/Title">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
											<xsl:text></xsl:text>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
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
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
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
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('JobTitle')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="JobTitle" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/JobTitle" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<!--TODO!bash! [  DONE  ]		URGENT! Only show dates which allow contact to be > 18 - do not allow ages < 18 -->
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('DOB')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="DOB">
									<xsl:with-param name="Name-Day"			select="string('DOB[day]')" />
									<xsl:with-param name="Name-Month"		select="string('DOB[month]')" />
									<xsl:with-param name="Name-Year"		select="string('DOB[year]')" />
									<xsl:with-param name="Selected-Day"		select="/Response/ui-values/DOB-day" />
									<xsl:with-param name="Selected-Month"	select="/Response/ui-values/DOB-month" />
									<xsl:with-param name="Selected-Year"	select="/Response/ui-values/DOB-year" />
								</xsl:call-template>
							</td>
						</tr>
						<tr>
							<td>
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<!--TODO!bash! [  DONE  ]		URGENT! verify email - needs to include '@' symbol -->
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
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
						<!--TODO!bash! [  DONE  ]		URGENT! verify ph number - numeric only-->
						<tr>
							<td class="Required"><strong><span class="Red"><sup>1</sup></span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
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
						<!--TODO!bash! [  DONE  ]		URGENT! verify ph number - numeric only-->
						<tr>
							<td class="Required"><strong><span class="Red"><sup>1</sup></span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
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
						<!--TODO!bash! [  DONE  ]		URGENT! verify ph number - numeric only-->
						<tr>
							<td></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Fax')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Fax" class="input-string" maxlength="25">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Fax" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
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
							<td class="Required"><strong><span class="Red">*</span></strong></td>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('PassWord')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="PassWord" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/PassWord" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<td class="Required" valign="top"><strong><span class="Red">*</span></strong></td>
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
											<input type="radio" name="CustomerContact" value="0" id="CustomerContact:FALSE">
												<xsl:choose>
													<xsl:when test="not(/Response/ui-values/CustomerContact) or /Response/ui-values/CustomerContact != 1">
														<xsl:attribute name="checked">
															<xsl:text>checked</xsl:text>
														</xsl:attribute>
													</xsl:when>
												</xsl:choose>
											</input>
										</td>
										<td><label for="CustomerContact:FALSE">Allow access to this Account only</label></td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="CustomerContact" value="1" id="CustomerContact:TRUE">
												<xsl:choose>
													<xsl:when test="/Response/ui-values/CustomerContact = 1">
														<xsl:attribute name="checked">
															<xsl:text>checked</xsl:text>
														</xsl:attribute>
													</xsl:when>
												</xsl:choose>
											</input>
										</td>
										<td><label for="CustomerContact:TRUE">Allow access to all Associated Accounts</label></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
				<strong><span class="Red"><sup>1</sup> </span></strong>: One or both fields required<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Add Contact &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
