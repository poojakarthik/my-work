<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
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
					<table border="0" cellpadding="5" cellspacing="0">
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
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/Account/TradingName" /></td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="Seperator"></div>
			
			<h2 class="Contact">Contact Details</h2>
			
			<xsl:if test="/Response/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Title'">
							You must enter a Salutation for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'FirstName'">
							You must enter a First Name for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'LastName'">
							You must enter a Last Name for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'DOB'">
							You must enter a valid Date of Birth for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Email'">
							You must enter an Email Address for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Phones Empty'">
							You must enter a Contact Number for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'UserName Empty'">
							You must enter a Username for the Contact you are creating.
						</xsl:when>
						<xsl:when test="/Response/Error = 'UserName Exists'">
							The Username you selected already exists. Please select another Username and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'PassWord'">
							You must enter a Password for the Contact you are creating.
						</xsl:when>
					</xsl:choose>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
							<td></td>
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
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
									<xsl:with-param name="Selected-Day"		select="/Response/Contact/DOB-day" />
									<xsl:with-param name="Selected-Month"	select="/Response/Contact/DOB-month" />
									<xsl:with-param name="Selected-Year"	select="/Response/Contact/DOB-year" />
								</xsl:call-template>
							</td>
						</tr>
						<tr>
							<td>
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
							<td width="10"><strong><span class="Red"><sup>1</sup></span></strong></td>
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
							<td width="10"><strong><span class="Red"><sup>1</sup></span></strong></td>
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
							<td></td>
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
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
							<td width="10"><strong><span class="Red">*</span></strong></td>
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
										<xsl:value-of select="/Response/Contact/PassWord" />
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
							<td width="10" valign="top"><strong><span class="Red">*</span></strong></td>
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
													<xsl:when test="not(/Response/Contact/CustomerContact) or /Response/Contact/CustomerContact != 1">
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
													<xsl:when test="/Response/Contact/CustomerContact = 1">
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
			<div class="Seperator"></div>
			<input type="submit" value="Create Contact &#0187;" class="input-submit" />
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
