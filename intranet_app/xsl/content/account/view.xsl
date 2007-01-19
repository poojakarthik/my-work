<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>View Account Details</h1>
		
		<script language="javascript" src="js/note_add.js"></script>
		
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2 class="Account">Account Details</h2>
					
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<form method="POST" action="account_edit.php">
								<input type="hidden" name="Id">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
								</input>
								<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Id')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Account/Id" />
										</td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
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
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('ABN')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/ABN" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('ACN')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/ACN" /></td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Address1')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Address1" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Address2')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Address2" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Suburb')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Suburb" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Postcode')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Postcode" /></td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('State')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/State" /></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Country')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Account/Country" />
										</td>
									</tr>
									<tr>
										<td colspan="2"><div class="Seperator"></div></td>
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
												<xsl:when test="/Response/Account/Archived = 0">
													<strong><span class="Green">Currently Available</span></strong>
												</xsl:when>
												<xsl:otherwise>
													<strong><span class="Red">Currently Archived</span></strong>
												</xsl:otherwise>
											</xsl:choose>
										</td>
									</tr>
								</table>
							</form>
						</div>
					</div>
					<div class="LinkEdit">
						<a>
							<xsl:attribute name="href">
								<xsl:text>account_edit.php?Id=</xsl:text>
								<xsl:value-of select="/Response/Account/Id" />
							</xsl:attribute>
							<xsl:text>Edit Account</xsl:text>
						</a>
					</div>
					
					<div class="Clear"></div>
					<div class="Seperator"></div>
					
					<h2 class="Contacts">Active Contacts</h2>
					
					<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
						<tr class="First">
							<th></th>
							<th>Contact Details</th>
							<th>Actions</th>
						</tr>
						<xsl:for-each select="/Response/Contacts/Contact">
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
								<td width="50"><img src="img/template/contact.png" /></td>
								<td>
									<strong>
										<xsl:value-of select="./FirstName" />
										<xsl:text> </xsl:text>
										<xsl:value-of select="./LastName" />
									</strong><br />
									<xsl:value-of select="./UserName" />
								</td>
								<td>
									<a>
										<xsl:attribute name="href">
											<xsl:text>contact_view.php?Id=</xsl:text>
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										<xsl:text>View</xsl:text>
									</a>
								</td>
							</tr>
						</xsl:for-each>
					</table>
					<div class="LinkAdd">
						<a>
							<xsl:attribute name="href">
								<xsl:text>contact_add.php?Account=</xsl:text>
								<xsl:value-of select="/Response/Account/Id" />
							</xsl:attribute>
							<xsl:text>Add Contact</xsl:text>
						</a>
					</div>
				</td>
				<td width="30" nowrap="nowrap"></td>
				<td valign="top" width="300">
					<h2 class="Invoice">Overdue Amount</h2>
					Overdue charges for this account stand at :
					<br />
					<strong>
						<span>
							<xsl:attribute name="class">
								<xsl:text>Large Right </xsl:text>
								<xsl:choose>
									<xsl:when test="/Response/Account/OverdueAmount = '$0.0000'">
										<xsl:text>Green</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>Red</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							<xsl:value-of select="/Response/Account/OverdueAmount" />
						</span>
					</strong>
					
					<div class="Clear"></div>
					<div class="Seperator"></div>
					
					<h2 class="Options">Account Options</h2>
					<ul>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_ledger.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View Invoices + Payments</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>recurring_charge_list.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View Recurring Charges</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>payment_add.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Make Payment</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_payment.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Change Payment Method</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_edit.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Edit Account Details</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_add.php?AccountGroup=</xsl:text>
									<xsl:value-of select="/Response/Account/AccountGroup" />
								</xsl:attribute>
								<xsl:text>Add Associated Account</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_add.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Add Service</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>contact_add.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Add Contact</xsl:text>
							</a>
						</li>
					</ul>
					
					<div class="Seperator"></div>
					
					<h2 class="Notes">Account Notes</h2>
					
					<form method="post" action="note_add.php" onsubmit="return noteAdd (this)">
						<input type="hidden" name="AccountGroup">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Account/AccountGroup" />
							</xsl:attribute>
						</input>
						<input type="hidden" name="Account">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Account/Id" />
							</xsl:attribute>
						</input>
						Type new note for this account in the field below:
						<textarea name="Note" class="input-summary" rows="6" />
						
						<select class="Left" name="NoteType">
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
							<input type="submit" value="Create Note &#0187;" class="input-submit" />
						</div>
					</form>
					
					<div class="Clear"></div>
					
					<div class="Seperator"></div>
					<h3>Recent Notes</h3>
					<xsl:choose>
						<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
							No notes have been attached to this Account.
						</xsl:when>
						<xsl:otherwise>
							The 5 most recent notes are listed below:
							<div class="Right">
								<a href="#" title="Account Notes" alt="Notes for this Account">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, 'note_list.php?Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View All Account Notes</xsl:text>
								</a>
							</div>
							<div class="Clear"></div>
							<div class="Seperator"></div>
							<xsl:for-each select="/Response/Notes/Results/rangeSample/Note">
								<xsl:variable name="Note" select="." />
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
													<xsl:with-param name="year"		select="./Datetime/year" />
													<xsl:with-param name="month"	select="./Datetime/month" />
													<xsl:with-param name="day"		select="./Datetime/day" />
							 						<xsl:with-param name="hour"		select="./Datetime/hour" />
													<xsl:with-param name="minute"	select="./Datetime/minute" />
													<xsl:with-param name="second"	select="./Datetime/second" />
													<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
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
								<div class="Seperator"></div>
							</xsl:for-each>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
		<div class="Clear"></div>
		<div class="Seperator"></div>
		
		<!-- Services -->
		<h2 class="Services">Services</h2>
		
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Service Number</th>
				<th>Service Type</th>
				<th>Plan Name</th>
				<th>Archive Status</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/Services/Results/rangeSample/Service">
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
					
					<td><xsl:value-of select="/Response/Services/Results/rangeStart + position()" />.</td>
					<td><xsl:value-of select="./FNN" /></td>
					<td><xsl:value-of select="./ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					<td>
						<xsl:choose>
							<xsl:when test="./RatePlan">
								<xsl:value-of select="./RatePlan/Name" />
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Attention">No Plan Selected</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="./ClosedOn/year">
								<strong><span class="Red">
									Closes On:
									<xsl:call-template name="dt:format-date-time">
										<xsl:with-param name="year"		select="./ClosedOn/year" />
										<xsl:with-param name="month"	select="./ClosedOn/month" />
										<xsl:with-param name="day"		select="./ClosedOn/day" />
										<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
									</xsl:call-template>
								</span></strong>
							</xsl:when>
							<xsl:when test="./CreatedOn/year and ./Available = 0">
								<strong><span class="Blue">
									Opens On:
									<xsl:call-template name="dt:format-date-time">
										<xsl:with-param name="year"		select="./CreatedOn/year" />
										<xsl:with-param name="month"	select="./CreatedOn/month" />
										<xsl:with-param name="day"		select="./CreatedOn/day" />
										<xsl:with-param name="format"	select="'%A, %b %d, %Y'"/>
									</xsl:call-template>
								</span></strong>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Green">Currently Available</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>Service Details</xsl:text>
						</a>
						<xsl:text>, </xsl:text>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_unbilled.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>Unbilled Charges</xsl:text>
						</a>
						<xsl:text>, </xsl:text>
						<a href="#" title="Service Notes" alt="Notes for this Service">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, 'note_list.php?Service=</xsl:text>
								<xsl:value-of select="./Id" /><xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:text>View Notes</xsl:text>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<div class="LinkAdd">
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_add.php?Account=</xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
				Add Service
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
