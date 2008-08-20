<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!-- TODO!bash! [  DONE  ]		Use a msgbox for blank note errors like all other messages! DO THIS EVERYWHERE!!-->
		<!-- Error === X"Please enter details to create a new note." -->
		
		<!-- Page for Viewing Account Details -->
		<h1>View Account Details</h1>
		
		<script language="javascript" src="js/note_add.js"></script>
		
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<h2 class="Account">Account Details</h2>
					
					<div class="Narrow-Form">
						<div class="Form-Content">
							<form method="POST" action="account_edit.php">
								<input type="hidden" name="Id">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Account/Id" />
									</xsl:attribute>
								</input>
								<table border="0" cellpadding="3" cellspacing="0">
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
										<td colspan="2"><div class="MicroSeperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Balance')" />
											</xsl:call-template>
										</th>
										<td>
											<!-- TODO!bash! [  DONE  ]		Show account balance as green if it = 0, just like all the other 0s!!!!! -->
											<span>
												<!-- TODO!bash! [  DONE  ]		Display overdue amount to 2 decimal places -->
												<xsl:attribute name="class">
													<xsl:choose>
														<xsl:when test="not(/Response/Account/Balance) or /Response/Account/Balance = 0">
															<xsl:text>Red</xsl:text>
														</xsl:when>
														<xsl:otherwise>
															<xsl:text>Green</xsl:text>
														</xsl:otherwise>
													</xsl:choose>
												</xsl:attribute>
								       			<xsl:call-template name="Currency">
								       				<xsl:with-param name="Number" select="/Response/AccountBalance" />
													<xsl:with-param name="Decimal" select="number('2')" />
						       					</xsl:call-template>
											</span>
										</td>
									</tr>
									<xsl:choose>
										<!-- TODO!bash! [  DONE  ]		Display overdue amount to 2 decimal places -->
										<xsl:when test="/Response/Account/OverdueAmount != '0' and /Response/Account/OverdueAmount != ''">
											<tr>
												<th class="JustifiedWidth">
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Account')" />
														<xsl:with-param name="field" select="string('Overdue')" />
													</xsl:call-template>
												</th>
												<td>
													<span Class="Red">
										       			<xsl:call-template name="Currency">
										       				<xsl:with-param name="Number" select="/Response/Account/OverdueAmount" />
															<xsl:with-param name="Decimal" select="number('2')" />
									    				</xsl:call-template>
													</span>
												</td>
											</tr>
										</xsl:when>
									</xsl:choose>
									<tr>
										<td colspan="2"><div class="MicroSeperator"></div></td>
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
									<tr>
										<td colspan="2"><div class="MicroSeperator"></div></td>
									</tr>
									<!--Check for ABN-->
									<xsl:if test="/Response/Account/ABN != ''">
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Account')" />
													<xsl:with-param name="field" select="string('ABN')" />
												</xsl:call-template>
											</th>
											<td>
												<xsl:value-of select="/Response/Account/ABN" />
												<!--
												<xsl:text> [</xsl:text>
												<a target="_blank">
													<xsl:attribute name="href">
														<xsl:text>http://www.search.asic.gov.au/cgi-bin/gns030c?FORMID=gns010s1&amp;ACN=</xsl:text>
														<xsl:value-of select="/Response/Account/ABN" />
													</xsl:attribute>
													<xsl:text>View ASIC</xsl:text>
												</a>
												<xsl:text>]</xsl:text>
												-->
											</td>
										</tr>
									</xsl:if>
									<!--Check for ACN-->
									<xsl:if test="/Response/Account/ACN != ''">
										<tr>
											<th>
												<xsl:call-template name="Label">
													<xsl:with-param name="entity" select="string('Account')" />
													<xsl:with-param name="field" select="string('ACN')" />
												</xsl:call-template>
											</th>
											<td>
												<xsl:value-of select="/Response/Account/ACN" />
												<!--
												<xsl:text> [</xsl:text>
												<a target="_blank">
													<xsl:attribute name="href">
														<xsl:text>http://www.search.asic.gov.au/cgi-bin/gns030c?FORMID=gns010s1&amp;ACN=</xsl:text>
														<xsl:value-of select="/Response/Account/ACN" />
													</xsl:attribute>
													<xsl:text>View ASIC</xsl:text>
												</a>
												<xsl:text>]</xsl:text>
												-->
											</td>
										</tr>
									</xsl:if>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('Address1')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/Account/Address1" /></td>
									</tr>
									<!--Check for Address2-->
									<xsl:choose>
										<xsl:when test="/Response/Account/TradingName = ''">
										</xsl:when>
										<xsl:otherwise>
											<tr>
												<th>
													<xsl:call-template name="Label">
														<xsl:with-param name="entity" select="string('Account')" />
														<xsl:with-param name="field" select="string('Address2')" />
													</xsl:call-template>
												</th>
												<td>
													<xsl:value-of select="/Response/Account/Address2" />
												</td>
											</tr>
										</xsl:otherwise>
									</xsl:choose>
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
										<td colspan="2"><div class="MicroSeperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('State')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/ServiceStateType/Name" /></td>
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
										<td colspan="2"><div class="MicroSeperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Payment')" />
												<xsl:with-param name="field" select="string('PaymentMethod')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/BillingType/Name" />
										</td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Billing')" />
												<xsl:with-param name="field" select="string('BillingMethod')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/BillingMethod/Name" />
										</td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('DisableLatePayment')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:choose>
												<xsl:when test="/Response/Account/DisableLatePayment = 0">
													<xsl:text>Charge a late payment fee</xsl:text>
												</xsl:when>
												<xsl:when test="/Response/Account/DisableLatePayment &lt;= -1">
													<xsl:text>Don't charge a late payment fee on the next invoice</xsl:text>
												</xsl:when>
												<xsl:when test="/Response/Account/DisableLatePayment = 1">
													<xsl:text>Never charge a late payment fee</xsl:text>
												</xsl:when>
											</xsl:choose>
										</td>
									</tr>
									
									<tr>
										<td colspan="2"><div class="MicroSeperator"></div></td>
									</tr>
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('CustomerGroup')" />
												<xsl:with-param name="field" select="string('CustomerGroup')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/CustomerGroup/Name" />
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
							<xsl:text>Edit Account Details</xsl:text>
						</a>
					</div>
					
					<div class="Clear"></div>
					<div class="Seperator"></div>
					
					
					<!-- Active Contacts -->
					<h2 class="Contacts">Active Contacts</h2>
					
					<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
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
				
				<!-- column spacer -->
				<td class="ColumnSpacer"></td>
				
				<!-- second column -->
				<td valign="top">
					<!-- options -->
					<h2 class="Options">Account Options</h2>
					<ul>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>../admin/flex.php/Account/InvoicesAndPayments/?Account.Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View Invoices &amp; Payments</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_charges_unbilled.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View/Add Adjustments</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>recurring_charge_list.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View/Add Recurring Adjustments</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>costcentre_list.php?Account=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>View Cost Centres</xsl:text>
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
									<xsl:text>account_payment.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Change Payment Method</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_add.php?Associated=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Add Associated Account</xsl:text>
							</a>
						</li>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_addbulk.php?Account=</xsl:text>
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
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>../admin/flex.php/Account/InvoicesAndPayments/?Account.Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Make Payment</xsl:text>
							</a>
						</li>
					</ul>
					
					<div class="Seperator"></div>
					
					<!-- Account Notes -->
					<h2 class="Notes">Account Notes</h2>
					
					<form method="post" action="note_add.php" onsubmit="return noteAdd (this)" name="NoteAdd">
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
							<input type="submit" value="Add Note &#0187;" class="input-submit-disabled" disabled="disabled" />
						</div>
					</form>
					
					<div class="Clear"></div>
					
					<!--Recent Notes-->
					<h3>Recent Notes</h3>
					<xsl:choose>
						<xsl:when test="count(/Response/Notes/Results/rangeSample/Note) = 0">
							No notes have been attached to this Account.
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
													<xsl:with-param name="year"		select="./Datetime/year" />
													<xsl:with-param name="month"	select="./Datetime/month" />
													<xsl:with-param name="day"		select="./Datetime/day" />
							 						<xsl:with-param name="hour"		select="./Datetime/hour" />
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
								<a href="#" title="Account Notes" alt="Notes for this Account">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, 'note_list.php?Account=</xsl:text>
										<xsl:value-of select="/Response/Account/Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View All Account Notes</xsl:text>
								</a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
		<div class="Clear"></div>
		<div class="Seperator"></div>
		
		<!-- Services -->
		<h2 class="Services">Services</h2>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Service #</th>
				<th>Service Type</th>
				<th>Plan Name</th>
				<th>Status</th>
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
					<td>		
						
						<a title="View Service Details">
							<xsl:attribute name="href">
								<xsl:text>service_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:choose>
								<xsl:when test="./FNN=''">
									<span class="Red"><strong>None</strong></span> 
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="./FNN" /> 
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</td>
					<td><xsl:value-of select="./ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					<td>
						<xsl:choose>
							<xsl:when test="./RatePlan">
								<a target="blank">
									<xsl:attribute name="href">
										<xsl:text>rates_plan_summary.php?Id=</xsl:text>
										<xsl:value-of select="./RatePlan/Id" />
									</xsl:attribute>
									<xsl:value-of select="./RatePlan/Name" />
								</a>
							</xsl:when>
							<xsl:otherwise>
								<a>
									<xsl:attribute name="href">
										<xsl:text>service_plan.php?Service=</xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<strong><span class="Attention">No Plan Selected</span></strong>
								</a>
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
									<xsl:choose>
										<xsl:when test="/Response/Now/year &lt;= ./CreatedOn/year and /Response/Now/month &lt;= ./CreatedOn/month and /Response/Now/day &lt;= ./CreatedOn/day">
											Opens On:
										</xsl:when>
										<xsl:otherwise>
											Opened On:
										</xsl:otherwise>
									</xsl:choose>
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
						<a href="#" title="Service Notes" alt="Notes for this Service">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, 'note_list.php?Service=</xsl:text>
								<xsl:value-of select="./Id" /><xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:text>View Notes</xsl:text>
						</a>
						<xsl:text>, </xsl:text>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_unbilled.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:text>View Unbilled Charges</xsl:text>
						</a>
						
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/Services/Results/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Services associated with this Account.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="LinkAdd">
			<a>
				<xsl:attribute name="href">
					<xsl:text>service_addbulk.php?Account=</xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
				Add Service
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>
