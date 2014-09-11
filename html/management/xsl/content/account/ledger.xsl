<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<!--TODO!bash! [  DONE  ]		URGENT - DO NOT have menus linking to the page you are already on!!!!-->
	<xsl:template name="Content">
		<script language="javascript" type="text/javascript">		
			function validate(payment)
			{
				var check=confirm("Are you sure you want to reverse this payment?");
				if (check)
				{
					document.forms['payments'].Id.value=payment;
					document.forms['payments'].submit();
					return true;
				}
			}
		</script>			
	
		<h1>View Invoices &amp; Payments</h1>
		
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
						<td>
							<xsl:value-of select="/Response/Account/Id" />
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('BusinessName')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Account/BusinessName" />
						</td>
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
		
		<h2 class="Invoice">Invoice</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Invoice #</th>
				<th>Date</th>
				<th class='thRight'>Opening Balance</th>
				<th class='thRight'>We Received</th>
				<th class='thRight'>Balance</th>
				<th class='thRight'>Total of this Bill</th>
				<th class='thRight'>Total Owing</th>
				<th class='thRight'>Invoice Balance</th>
				<th class='thRight'>Disputed</th>
			</tr>
			<xsl:for-each select="/Response/Invoices/Record">
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
					<td><xsl:value-of select="position()" />.</td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_view.php?Invoice=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./Id" />
						</a>
					</td>
					<td>
						<!-- <xsl:call-template name="dt:format-date-time">  
							<xsl:with-param name="year"		select="./CreatedOn/year" />
							<xsl:with-param name="month"	select="./CreatedOn/month" />
							<xsl:with-param name="day"		select="./CreatedOn/day" />
							<xsl:with-param name="format"	select="'%m/%Y'"/>
							<xsl:with-param name="date"		select="./CreatedOn" />
						</xsl:call-template> -->
						<xsl:value-of select="./CreatedOn" />
					</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./OpeningBalance" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
       				</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Received" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
					</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./AccountBalance" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
       				</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Total" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
       				</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./TotalOwing" />
							<xsl:with-param name="Decimal" select="number('2')" />
       					</xsl:call-template>
       				</td>					
					<td class="Currency">
						<strong>
							<span>
								<xsl:attribute name="class">
									<xsl:choose>
										<xsl:when test="./Balance != 0">
											<xsl:text>Red</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>Green</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
								
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="./Balance" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
							</span>
						</strong>
					</td>
					<td class="Currency">
						<span>
						<!-- TODO!bash! [  DONE  ]		Make this green when it has been resolved -->
							<xsl:choose>
								<xsl:when test="./Disputed != 0">
									<xsl:attribute name="class">
										<xsl:text>Red</xsl:text>
									</xsl:attribute>
									
									<xsl:attribute name="style">
										<xsl:text>font-weight: bold;</xsl:text>
									</xsl:attribute>
								</xsl:when>
							</xsl:choose>
							
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="./Disputed" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</span>
					</td>

				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/Invoices/Results/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Invoices associated with this Account.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="Seperator"></div>
		<!--TODO!bash! [  DONE  ]		URGENT - View PDF does not work-->
		<h2 class="PDF">PDF Bills</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Bill Month</th>
				<th>Bill Year</th>
				<th>Bill Name</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/Invoices-PDFs/Invoice-PDF">
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
					
					<td><xsl:value-of select="position()" />.</td>
					<td>
						<xsl:value-of select="./Month" />
						(<xsl:choose>
							<xsl:when test="./Month = 1"><xsl:text>January</xsl:text></xsl:when>
							<xsl:when test="./Month = 2"><xsl:text>February</xsl:text></xsl:when>
							<xsl:when test="./Month = 3"><xsl:text>March</xsl:text></xsl:when>
							<xsl:when test="./Month = 4"><xsl:text>April</xsl:text></xsl:when>
							<xsl:when test="./Month = 5"><xsl:text>May</xsl:text></xsl:when>
							<xsl:when test="./Month = 6"><xsl:text>June</xsl:text></xsl:when>
							<xsl:when test="./Month = 7"><xsl:text>July</xsl:text></xsl:when>
							<xsl:when test="./Month = 8"><xsl:text>August</xsl:text></xsl:when>
							<xsl:when test="./Month = 9"><xsl:text>September</xsl:text></xsl:when>
							<xsl:when test="./Month = 10"><xsl:text>October</xsl:text></xsl:when>
							<xsl:when test="./Month = 11"><xsl:text>November</xsl:text></xsl:when>
							<xsl:when test="./Month = 12"><xsl:text>December</xsl:text></xsl:when>
						</xsl:choose>)
					</td>
					<td><xsl:value-of select="./Year" /></td>
					<td><xsl:value-of select="./Name" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_pdf.php</xsl:text>
								<xsl:text>?Account=</xsl:text><xsl:value-of select="./Account" />
								<xsl:text>&amp;Year=</xsl:text><xsl:value-of select="./Year" />
								<xsl:text>&amp;Month=</xsl:text><xsl:value-of select="./Month" />
							</xsl:attribute>
							View PDF
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="count(/Response/Invoices-PDFs/Invoice-PDF) = 0">
				<div class="MsgNoticeWide">
					There are no PDF files associated with this Account.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="Seperator"></div>
		
		<h2 class="Payment">Payments</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Payment Date</th>
				<th>Payment Type</th>
				<th>Payment Status</th>
				<th class='thRight'>Payment Amount</th>
				<th width="8%"></th>
				<th class='thRight'>Amount Applied</th>
				<th width="8%"></th>
				<th class='thRight'>Balance</th>
			</tr>
			<!-- <xsl:for-each select="/Response/AccountPayments/Results/rangeSample/InvoicePayment"> -->
			<xsl:for-each select="/Response/Payments/Record">
			<form method="POST" name="payments" id="payments" action="../admin/flex.php/Account/InvoicesAndPayments/?Account.Id=">
				<input name="Account" type="hidden">
					<xsl:attribute name="value">
						<xsl:value-of select="/Response/Account/Id" />
					</xsl:attribute>
				</input>
				<input name="Id" id="Id" type="hidden">
					<xsl:attribute name="value">
						<xsl:value-of select="./Id" />
					</xsl:attribute>
				</input>
				<input name="Employee" type="hidden">
					<xsl:attribute name="value">
						<xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/Id" />
					</xsl:attribute>
				</input>
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
					
					<td><xsl:value-of select="position()" />.</td>
					<td>
						<xsl:value-of select="./PaidOn" />
					</td>
					<td>
						<xsl:value-of select="./TypeName" />
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="./StatusName">
								<xsl:value-of select="./StatusName" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="/Response/Permission/IsAuthenticated = 1 and ./Type != 0">
										<!--<input type="submit" class="input-link" value="Reverse Payment"/>-->
										<a name="link">
										<xsl:attribute name="href">
											<xsl:text>javascript:validate(</xsl:text>
											<xsl:value-of select="./Id"/>
											<xsl:text>)</xsl:text>
										</xsl:attribute>
										Reverse Payment</a>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text></xsl:text>
									</xsl:otherwise>
								</xsl:choose>	
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Amount" />
							<xsl:with-param name="Decimal" select="number('2')" />
    					</xsl:call-template>
   					</td>
					<td></td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Applied" />
							<xsl:with-param name="Decimal" select="number('2')" />
    					</xsl:call-template>
   					</td>
					<td></td>
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Balance" />
							<xsl:with-param name="Decimal" select="number('2')" />
    					</xsl:call-template>
   					</td>
				</tr>
			</form>
			</xsl:for-each>
		</table>
		
		<div class="Seperator"></div>
		
		<div class="Seperator"></div>
		
		<h2 class="Payment">Payments Applied</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Invoice #</th>
				<th>Payment Date</th>
				<th class='thRight'>Payment Amount</th>
				<th width="10%"></th>
				<th>Actions</th>
			</tr>
			<!-- <xsl:for-each select="/Response/AccountPayments/Results/rangeSample/InvoicePayment"> -->
			<xsl:for-each select="/Response/AppliedPayments/Record">
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
					
					<td><xsl:value-of select="position()" />.</td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>invoice_view.php?Invoice=</xsl:text>
								<xsl:value-of select="./Invoice" />
							</xsl:attribute>
							<xsl:value-of select="./Invoice" />
						</a>
					</td>
					<td>
							<xsl:value-of select="./PaidOn" />
					</td>
				
					<td class="Currency">
		       			<xsl:call-template name="Currency">
		       				<xsl:with-param name="Number" select="./Amount" />
							<xsl:with-param name="Decimal" select="number('2')" />
    					</xsl:call-template>
   					</td>
					<td></td>
					<td>
						<a href="#" title="View Invoice Payment Details" alt="Information about a payment that was made">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, 'invoicepayment_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>')</xsl:text>
							</xsl:attribute>
							Payment Details
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<!-- <xsl:when test="count(/Response/AccountPayments/Record) = 0"> -->
			<xsl:when test="/Response/AccountPayments/collationLength = 0">
				<div class="MsgNoticeWide">
					There are no Payments associated with this Account.
				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
