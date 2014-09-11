<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--TODO!bash! [  DONE  ]		URGENT - Do not have menu options that return to the same page!!!-->
		
		<!-- Page for viewing contact Details -->
		<h1>View Contact Details</h1>
		
		<script language="javascript" src="js/note_add.js"></script>
		
		<!--Column 1 -->
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
				
					<!--Contact Details -->
					<h2 class="Contact">Contact Details</h2>

					<div class="Narrow-Form">
						<div class="Form-Content">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Name')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/TitleTypes/TitleType[./Id=/Response/Contact/Title]/Name" />
										<xsl:text> </xsl:text>
										<xsl:value-of select="/Response/Contact/FirstName" />
										<xsl:text> </xsl:text>
										<xsl:value-of select="/Response/Contact/LastName" />
									</td>
								</tr>
									<!--Check for Job Title-->
									<xsl:choose>
										<xsl:when test="/Response/Contact/JobTitle = ''">
										</xsl:when>
										<xsl:otherwise>
											<tr>
												<th>
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Contact')" />
														<xsl:with-param name="field" select="string('JobTitle')" />
													</xsl:call-template>
												</th>
												<td>
													<xsl:value-of select="/Response/Contact/JobTitle" />
												</td>
											</tr>
										</xsl:otherwise>
									</xsl:choose>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('DOB')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Contact/DOB/year">
												<xsl:call-template name="dt:format-date-time">
													<xsl:with-param name="year"		select="/Response/Contact/DOB/year" />
													<xsl:with-param name="month"	select="/Response/Contact/DOB/month" />
													<xsl:with-param name="day"		select="/Response/Contact/DOB/day" />
													<xsl:with-param name="format"	select="'%b %d, %Y'"/>
												</xsl:call-template>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Attention">No Date of Birth</span></strong>
											</xsl:otherwise>
										</xsl:choose>
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
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('Email')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Contact/Email != ''">
												<a>
													<xsl:attribute name="href">
														<xsl:text>mailto:</xsl:text>
														<xsl:value-of select="/Response/Contact/Email" />
													</xsl:attribute>
													<xsl:value-of select="/Response/Contact/Email" />
												</a>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Attention">No Email Address</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
								<!--Check for Phone-->
								<xsl:if test="/Response/Contact/Phone != ''">
									<tr>
										<th>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Contact')" />
												<xsl:with-param name="field" select="string('Phone')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Contact/Phone" />
										</td>
									</tr>
								</xsl:if>
								<!-- Check for Mobile -->
								<xsl:if test="/Response/Contact/Mobile != ''">
									<tr>
										<th>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Contact')" />
												<xsl:with-param name="field" select="string('Mobile')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Contact/Mobile" />
										</td>
									</tr>
								</xsl:if>
								<!--Check for Fax -->
								<xsl:if test="/Response/Contact/Fax != ''">
									<tr>
										<th>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Contact')" />
												<xsl:with-param name="field" select="string('Fax')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Contact/Fax" />
										</td>
									</tr>
								</xsl:if>
								<tr>
									<td colspan="2">
										<div class="MicroSeperator"></div>
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
										<xsl:value-of select="/Response/Contact/UserName" />
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
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('CustomerContact')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Contact/CustomerContact = 1">
												All Associated Accounts
											</xsl:when>
											<xsl:otherwise>
												Primary Account Only
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Archive')" />
											<xsl:with-param name="field" select="string('Archived')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Contact/Archived = 0">
												<strong><span class="Green">Active Contact</span></strong>
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Red">Archived Contact</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="LinkEdit">
						<xsl:choose>
							<xsl:when test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
								<!-- The user has OPERATOR privileges and can therefore edit the contact details -->
								<a>
									<xsl:attribute name="href">
										<xsl:text>contact_edit.php?Id=</xsl:text>
										<xsl:value-of select="/Response/Contact/Id" />
									</xsl:attribute>
									<xsl:text>Edit Contact Details</xsl:text>
								</a>
							</xsl:when>
						</xsl:choose>
					</div>
				</td>
				<td width="30" nowrap="nowrap"></td>
				
				<!-- column 2 -->
				<td valign="top">
					<!-- Contact Options -->
					<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
						<!-- The user has OPERATOR privileges and can therefore perform operations that would modify data -->
					
						<h2 class="Options">Contact Options</h2>
						<ul>
							<li>
								<a>
									<xsl:attribute name="href">
										<xsl:text>contact_edit.php?Id=</xsl:text>
										<xsl:value-of select="/Response/Contact/Id" />
									</xsl:attribute>
									<xsl:text>Edit Contact Details</xsl:text>
								</a>
							</li>
							<li>
								<a>
									<xsl:attribute name="href">
										<xsl:text>account_add.php?Associated=</xsl:text>
										<xsl:value-of select="/Response/Contact/Account" />
									</xsl:attribute>
									Add Associated Account
								</a>
							</li>
							<li>
								<a>
									<xsl:attribute name="href">
										<xsl:text>../admin/flex.php/Account/InvoicesAndPayments/?Account.Id=</xsl:text>
										<xsl:value-of select="/Response/Contact/Account" />
									</xsl:attribute>
									Make Payment
								</a>
							</li>
						</ul>
						<div class="Seperator"></div>
					</xsl:if>
					<!-- Contact Notes -->
					<h2 class="Notes">Contact Notes</h2>
					
					<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
						<!-- The user has OPERATOR privileges and can therefore perform operations that would modify data -->
						<form method="post" action="note_add.php" onsubmit="return noteAdd (this)" name="NoteAdd">
							<input type="hidden" name="AccountGroup">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/Contact/AccountGroup" />
								</xsl:attribute>
							</input>
							<input type="hidden" name="Contact">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/Contact/Id" />
								</xsl:attribute>
							</input>
							Type new note for this Contact in the field below:
							<textarea name="Note" class="input-summary" rows="6" />
							
							<div>
								<input type="checkbox" name="Account" checked="checked">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contact/Account" />
									</xsl:attribute>
								</input>
								Show this note in Account Notes.
							</div>
							
							<select name="NoteType" class="Left">
								<xsl:for-each select="/Response/NoteTypes/NoteType">
									<option>
										<xsl:attribute name="style">
											<xsl:text>background-color: #</xsl:text>
											<xsl:value-of select="./BackgroundColor" />
											<xsl:text>;</xsl:text>
											
											<xsl:text>border: solid 1px #</xsl:text>
											<xsl:value-of select="./BorderColor" />
											<xsl:text>;</xsl:text>
											
											<xsl:text>color: #</xsl:text>
											<xsl:value-of select="./TextColor" />
											<xsl:text>;</xsl:text>
										</xsl:attribute>
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:value-of select="./TypeLabel" />
									</option>
								</xsl:for-each>
							</select>
							
							<div class="Right">
								<input type="submit" value="Add Note &#0187;" class="input-submit-disabled" disabled="disabled" />
							</div>
						</form>
						
						<div class="Clear"></div>
					</xsl:if>
					
					<h3>Recent Notes</h3>
					<xsl:choose>
						<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
							There are no notes attached to this Contact.
						</xsl:when>
						<xsl:otherwise>
							The 5 most recent notes are listed below:
							<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
								<xsl:variable name="Note" select="." />
								<div class="SmallSeperator"></div>
								<div class="Note">
									<xsl:attribute name="style">
										<xsl:text>background-color: #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BackgroundColor" />
										<xsl:text>;</xsl:text>
										
										<xsl:text>border: solid 1px #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/BorderColor" />
										<xsl:text>;</xsl:text>
										
										<xsl:text>color: #</xsl:text>
										<xsl:value-of select="/Response/NoteTypes/NoteType[Id=$Note/NoteType]/TextColor" />
										<xsl:text>;</xsl:text>
									</xsl:attribute>
									
									<div class="small">
										Created on 
											<strong>
												<xsl:call-template name="dt:format-date-time">
													<xsl:with-param name="year"	select="./Datetime/year" />
													<xsl:with-param name="month"	select="./Datetime/month" />
													<xsl:with-param name="day"		select="./Datetime/day" />
							 						<xsl:with-param name="hour"	select="./Datetime/hour" />
													<xsl:with-param name="minute"	select="./Datetime/minute" />
													<xsl:with-param name="second"	select="./Datetime/second" />
													<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
												</xsl:call-template>
											</strong>
										by
											<strong>
												<xsl:value-of select="./Employee/FirstName" />
												<xsl:text> </xsl:text>
												<xsl:value-of select="./Employee/LastName" />
											</strong>.
									</div>
									<div class="Seperator"></div>
									
									<xsl:value-of select="./Note" disable-output-escaping="yes" />
								</div>
							</xsl:for-each>
							<div class="Right">
								<a href="#" title="Contact Notes" alt="Notes for this Contact">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, 'note_list.php?Contact=</xsl:text>
										<xsl:value-of select="/Response/Contact/Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View All Contact Notes</xsl:text>
								</a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
		
		<div class="Seperator"></div>
		
		<!-- Accounts Table -->
		<h2 class="Accounts">Accounts</h2>
		
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Account ID</th>
				<th>Business Name</th>
				<th>Trading Name</th>
				<th>Overdue Charges</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/Accounts/Results/rangeSample/Account">
				<tr>
					<xsl:attribute name="class">
						<xsl:choose>
							<xsl:when test="position() mod 2 = 1">
								<xsl:text>Odd</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>Even</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
					
					<td><xsl:value-of select="position()" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>account_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./Id" />
						</a>
					</td>
					<td><xsl:value-of select="./BusinessName" /></td>
					<td><xsl:value-of select="./TradingName" /></td>
					<td>
						<strong>
							<span>
								<xsl:attribute name="class">
									<xsl:choose>
										<xsl:when test="./OverdueAmount = '' or ./OverdueAmount = '0'">
											<xsl:text>Green</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>Red</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="./OverdueAmount" />
									<xsl:with-param name="Decimal" select="number('4')" />
		       					</xsl:call-template>
							</span>
						</strong>
					</td>
					<td>
						<a href="#" title="Account Notes" alt="Notes for this Account">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, 'note_list.php?Account=</xsl:text>
								<xsl:value-of select="./Id" /><xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:text>View Notes</xsl:text>
						</a>, 
						<a>
							<xsl:attribute name="href">
								<xsl:text>../admin/flex.php/Account/InvoicesAndPayments/?Account.Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>View Invoices &amp; Payments</xsl:text>
						</a>
						<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
							<!-- The user has OPERATOR privileges and can therefore perform operations that would modify data -->
							, <!-- this comma has been left here intentionaly to separate the links -->
							<a>
								<xsl:attribute name="href">
									<xsl:text>payment_add.php?Account=</xsl:text>
									<xsl:value-of select="./Id" />
								</xsl:attribute>
								<xsl:text>Make Payment</xsl:text>
							</a>
						</xsl:if>
						
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
			<!-- The user has OPERATOR privileges and can therefore perform operations that would modify data -->
			<div class="SmallSeperator"> </div> 
			<div class="Right">
				<a>
					<xsl:attribute name="href">
						<xsl:text>account_add.php?Associated=</xsl:text>
						<xsl:value-of select="/Response/Contact/Account" />
					</xsl:attribute>
					Add Associated Account
				</a>
			</div>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
