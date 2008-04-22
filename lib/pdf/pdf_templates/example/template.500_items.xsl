<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">


	<!-- page-order defines the order in which page-object's are to be included -->
	<page-order>

		<!-- always have page 1 & 2, as these have the payment advice at the bottom -->
		<page-object include="always" type="pageOne" />
		<page-object include="always" type="pageTwo" />

		<!-- repeat with odd/even pages until all itemisation has been included -->
		<page-repeat>

			<page-object include="optional" type="pageOdd" />
			<page-object include="optional" type="pageEven" />

		</page-repeat>

	</page-order>

	<html>
	
		<body style="font-family: Helvetica; font-size: 12pt; color: navy;">
		
			<pages>
			
				<page type="pageOne">
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveOddPage" />
					
					<!-- Logo and label -->
					<xsl:call-template name="logoAndLabel" />
					
					<!-- Contact details -->
					<xsl:call-template name="contactDetails" />
					
					<!-- Title -->
					<div style="bottom: 250.2mm; width: 210mm; text-align: center; font-size: 14pt;; font-weight: bold;">
						<p>
							<span style="font-size: 19pt;">V</span><span>OICE </span>
							<span style="font-size: 19pt;">T</span><span>ALK </span>
							<span style="font-size: 19pt;">S</span><span>TATEMENT</span>
						</p>
					</div>
					
					<!-- Customer address -->
					<div style="top: 59mm; left: 40mm; font-size: 10pt; line-height: 5mm;">
						<p><span><xsl:value-of select="/invoice/account/contact/addressee" /></span></p>
						<p><span><xsl:value-of select="/invoice/account/contact/street-address" /></span></p>
						<p>
							<span>
								<xsl:value-of select="/invoice/account/contact/town" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="/invoice/account/contact/state" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="/invoice/account/contact/postcode" />
							</span>
						</p>
					</div>
					
					<!-- Amounts due -->
					<div style="right: 11.2mm; 
								bottom: 218.7mm; 
								width: 65mm; 
								height: 22mm; 
								padding-left: 6mm; 
								padding-right: 3mm; 
								padding-top: 2.7mm; 
								padding-bottom: 4.2mm; 
								border-width: 0.3mm; 
								border-color: #000; 
								background-color: #fff; 
								corner-radius: 4mm; 
								color: #000; 
								font-size: 9pt;">
						<!-- Labels -->
						<div style="top: 0; left: 0; width: 42mm;">
							<p style="top: 0mm;">
								<span>Past Due Amount </span><span style="font-weight: bold;">Due Now</span>
							</p>
							<p style="top: 8mm;">
								<span>New Charges Due</span>
							</p>
							<p style="top: 16mm;">
								<span style="font-weight: bold;">Total Amount Due</span>
							</p>
						</div>
						<!-- Details -->
						<div style="top: 0; left: 43mm;">
							<div style="border-width: 0.3mm; 
										border-color: #000; 
										background-color: #aaa; 
										corner-radius: 1mm; 
										text-align: right; 
										top: 0mm;  
										height: 5.7mm; 
										width: 21.4mm; 
										padding-right: 1mm;">
								<p>
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/past-amount-due" /></span>
								</p>
							</div>
							<div style="border-width: 0.3mm; 
										border-color: #000; 
										background-color: #ccc; 
										corner-radius: 1mm; 
										text-align: right; 
										top: 8mm;  
										height: 5.7mm; 
										width: 21.4mm; 
										padding-right: 1mm;">
								<p>
									<span><xsl:value-of select="/invoice/statement/due-date" /></span>
								</p>
							</div>
							<div style="border-width: 0.3mm; 
										border-color: #000; 
										background-color: #aaa; 
										corner-radius: 1mm; 
										text-align: right; 
										top: 16mm; 
										height: 5.7mm; 
										width: 21.4mm; 
										padding-right: 1mm;">
								<p style="font-weight: bold;">
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/closing-balance" /></span>
								</p>
							</div>
						</div>
					</div>
					
					<!-- Statement Information -->
					<div style="right: 11.2mm; 
								bottom: 155mm; 
								width: 65mm; 
								height: 54.2mm; 
								padding-left: 6mm; 
								padding-right: 3mm; 
								padding-top: 0.7mm; 
								padding-bottom: 1.5mm; 
								border-width: 0.3mm; 
								border-color: #000; 
								background-color: #fff; 
								corner-radius: 4mm; 
								color: #000; 
								font-size: 9pt;">
						<!-- Labels -->
						<div style="top: 0; 
									width: 65mm; 
									text-align: left; 
									font-size: 12pt; 
									font-weight: bold; 
									border-width-bottom: 0.25mm; 
									border-color: #000; 
									background-color: #fff; 
									color: #000; 
									padding-bottom: 1.75mm;">
							<p>
								<span style="font-size: 17pt;">S</span><span>TATEMENT </span>
								<span style="font-size: 17pt;">I</span><span>NFORMATION</span>
							</p>
						</div>
						
						<div style="bottom: 0mm; line-height: 4.2mm">
						
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Billing Period</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/statement/bill-date-from" /><xsl:text> - </xsl:text><xsl:value-of select="/invoice/statement/bill-date-to" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Account No.</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/account/@number" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Page No.</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><page-nr /><xsl:text> of </xsl:text><page-count /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Invoice No.</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/@number" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Opening Balance</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/opening-balance" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Payments</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/payments-received" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Adjustments</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/adjustments-applied" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Balance</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/past-amount-due" /></span>
								</p>
							</div>
		
							<div style="width: 65mm;">
								<p style="left: 0 top: 0;">
									<span>Total of this Bill</span>
								</p>
								<p style="right: 0; top: 0;">
									<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/charges-added" /></span>
								</p>
							</div>
		
							<div style="height: 0.8mm"><!-- spacer --></div>
		
							<div style="width: 65mm; font-weight: bold;">
								<p style="left: 0;  top: 0;"><span>TOTAL OWING</span></p>
								<p style="right: 0; top: 0;"><span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/closing-balance" /></span></p>
							</div>
						
						</div>
		
					</div>
				
					<!-- Account Summary -->
					<div style="left: 29.8mm; 
								bottom: 155mm; 
								width: 65mm; 
								padding-left: 6mm; 
								padding-right: 3mm; 
								padding-top: 0.7mm; 
								padding-bottom: 1.5mm; 
								border-width: 0.3mm; 
								border-color: #000; 
								background-color: #fff; 
								corner-radius: 4mm; 
								color: #000; 
								font-size: 9pt;">
						<!-- Labels -->
						<div style="width: 65mm; 
									text-align: left; 
									font-size: 12pt;
									font-weight: bold; 
									border-width-bottom: 0.25mm; 
									border-color: #000; 
									background-color: #fff; 
									color: #000; 
									padding-bottom: 1.75mm;">
							<p>
								<span style="font-size: 17pt;">A</span><span>CCOUNT </span>
								<span style="font-size: 17pt;">S</span><span>UMMARY</span>
							</p>
						</div>
						<div style="height: 2mm;"><!-- This is a vertical spacer only! --></div>
						
						<div style="top: 8.5mm; line-height: 4.2mm">
						
							<!-- The summary items should be listed here -->
							<xsl:for-each select="/invoice/charges-summary/charge-category">
								<div style="width: 65mm;">
									<p style="left: 0 top: 0;">
										<span><xsl:value-of select="category-description" /></span>
									</p>
									<p style="right: 0; top: 0;">
										<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="category-total" /></span>
									</p>
								</div>
							</xsl:for-each>
		
							<div style="height: 0.8mm"><!-- spacer --></div>
		
							<div style="width: 65mm; font-weight: bold;">
								<p style="left: 0;  top: 0;"><span>TOTAL</span></p>
								<p style="right: 0; top: 0;"><span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/charges-added" /></span></p>
							</div>
							
						</div>
	
					</div>
	
					<!-- Promotional Content -->
					<div style="left: 29.8mm; 
								top: 148.4mm; 
								width: 169mm; 
								height: 43.5mm; 
								padding-left: 6mm; 
								padding-right: 3mm; 
								padding-top: 0.7mm; 
								padding-bottom: 2.7mm; 
								border-width: 0.4mm; 
								border-color: #000; 
								background-color: #fff; 
								corner-radius: 4mm; 
								color: #000; 
								font-size: 9pt;">
		
					</div>
					
					
					<!-- Cut Here -->
					<xsl:call-template name="scissorLine" />
		
					<!-- Payment Record (rendered after the scissor line to ensure it appears over the top of the white image background, although positioned vertically above it) -->
		
					
					<!-- Payment Advice -->
					<div style="top: 210.1mm; left: 20mm; width: 180mm;">
					
						<div style="width: 180mm; top: 5.5mm; left: 0mm; text-align: center; font-size: 12pt;; font-weight: bold; color: #000;">
							<p>
								<span style="font-size: 17pt;">P</span><span>AYMENT </span>
								<span style="font-size: 17pt;">A</span><span>DVICE</span>
							</p>
						</div>
					
						<img src="images/logo_gray.png" style="width: 52.5mm; height: 14.0mm; top: 0.5mm; left: 3mm;" />
						
						<!-- Left side -->
						<div style="width: 101mm; left: 0; top: 0; font-size: 5.3pt;">
		
							<!-- Late Fee-->
							<div style="left: 0; top: 17mm; width: 101mm;">
								<p style="left: 17mm; font-size: 6pt;"><span>LATE FEE</span></p>
								<p style="left: 17mm;"><span>To Avoid late payment fee please ensure your payment is made by the due date</span></p>
							</div>
		
							<div style="left: 0; top: 23.8mm; width: 101mm;">
								<img src="images/bill_pay.jpg" style="width: 6mm; height: 9mm; top: 1.1mm; left: 4mm;" />
								<p style="left: 17mm; font-size: 6pt;">
									<span>BPAY </span>
									<span>- Biller Code: 63412 Customer Ref:<xsl:text> </xsl:text><xsl:value-of select="/invoice/account/@number" /></span>
								</p>
								<p style="left: 17mm;">
									<span>Call your bank, credit union or building society to make payment from your cheque, savings or credit</span>
								</p>
								<p style="left: 17mm;">
									<span>card account. More info: www.bpay.com.au</span>\
								</p>
							</div>
		
							<div style="left: 0; top: 33mm; width: 101mm;">
								<p style="left: 17mm; font-size: 6pt;"><span>CREDIT CARD</span></p>
								<p style="left: 17mm;"><span>Call Telco Blue Pty Ltd on 1300 797 114 to pay your bill. Telco Blue accepts Visa, American Express, Diners</span></p>
								<p style="left: 17mm;"><span>and Mastercard. Please note that transaction limits may apply. Please record your receipt number and the</span></p>
								<p style="left: 17mm;"><span>date of your payment.</span></p>
							</div>
		
							<div style="left: 0; top: 44.5mm; width: 101mm;">
								<img src="images/direct_debit.jpg" style="width: 9mm; height: 10.288mm; top: -5.5mm; left: 2mm;" />
								<p style="left: 17mm; font-size: 6pt;"><span>DIRECT DEBIT</span></p>
								<p style="left: 17mm;"><span>To apply please call out customer service team on 1300 835 262</span></p>
							</div>
							
							<div style="left: 0; top: 51.5mm; width: 101mm;">
								<img src="images/mail.jpg" style="width: 13mm; height: 6.5mm; top: 1.1mm; left: 0.25mm;" />
								<p style="left: 17mm; font-size: 6pt;"><span>MAIL</span></p>
								<p style="left: 17mm;"><span>Detach the payment slip from the bottom of your account and return it together with your cheque or credit</span></p>
								<p style="left: 17mm;"><span>card details. Cheques should be made payable to "Telco Blue Pty Ltd" and mailed to: Telco Blue Pty Ltd,</span></p>
								<p style="left: 17mm;"><span>Locked Bag 4000, Fortitude Valley, QLD 4006</span></p>
							</div>
							
							<div style="left: 0; top: 63mm; width: 101mm;">
								<img src="images/bill_express.jpg" style="width: 13.5mm; height: 5mm; top: 1.1mm; left: 0mm;" />
								<p style="left: 17mm; font-size: 6pt;"><span>BILL EXPRESS</span></p>
								<p style="left: 17mm;"><span>Look for the red BILLEXPRESS logo at newsagents to pay this account with cash, cheque or debit card. For</span></p>
								<p style="left: 17mm;"><span>locations call 1300 739 250 or visit www.billexpress.com.au</span></p>
							</div>
							
							<div style="top: 71.2mm; left: 17mm; height: 10.5mm; text-align: right;">
							
							
								<xsl:element name="barcode">
									<xsl:attribute name="type">code128</xsl:attribute>
									<xsl:attribute name="value">000</xsl:attribute>
									<xsl:attribute name="style">height: 8mm; width: 54.7mm;</xsl:attribute>
								</xsl:element>
							
								<p style="top: 8.6mm; left: 0"><span>Biller ID: 63412</span></p>
								<p style="top: 8.6mm;"><span>Ref:<xsl:text> </xsl:text><xsl:value-of select="/invoice/account/@number" /></span></p>
							</div>
		
						</div>
						
						<!-- Right side -->
						<div style="width: 75mm; left: 115mm; font-size: 9pt; top: 19mm;">
		
							<!-- Top half -->
							<div style="width: 65mm; left: 0mm; top: 0mm;">
								<!-- Labels -->
								<div style="top: 0mm; left: 0; width: 31mm;">
									<p style="top: 0mm;"><span>Account No.</span></p>
									<p style="top: 6mm;"><span>Account Name</span></p>
									<p style="top: 12mm;"><span>Payment Due</span></p>
									<p style="top: 18mm;"><span>Past Due Amount</span></p>
									<p style="top: 24mm;"><span style="font-weight: bold;">Total Amount Due</span></p>
								</div>
								<!-- Details -->
								<div style="top: 0mm; left: 32mm; width: 33mm; text-align: right;">
									<p style="top: 0mm;"><span><xsl:value-of select="/invoice/account/@number" /></span></p>
									<p style="top: 6mm;"><span><xsl:value-of select="/invoice/account/@name" /></span></p>
									<p style="top: 12mm;"><span><xsl:value-of select="/invoice/statement/due-date" /></span></p>
									<p style="top: 18mm;"><span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/past-amount-due" /></span></p>
									<p style="top: 24mm;; font-weight: bold;">
										<span><xsl:value-of select="/invoice/currency-symbol" /><xsl:value-of select="/invoice/statement/closing-balance" /></span>
									</p>
								</div>
							</div>
		
							<!-- Bottom half -->
							<div style="width: 65mm; left: 0mm; top: 30mm; border-width-top: 0.3mm; border-color: #000; background-color: #fff; color: #000;">
								<!-- Labels -->
								<div style="top: 3mm; left: 0; width: 31mm;">
									<p style="top: 0mm;"><span>Date</span></p>
									<p style="top: 8mm;"><span>Cash</span></p>
									<p style="top: 16mm;"><span>Cheque</span></p>
									<p style="top: 24mm;"><span style="font-weight: bold;">Total</span></p>
								</div>
								<!-- Details -->
								<div style="top: 3mm; left: 32mm;">
									<div style="border-width: 0.3mm; 
												border-color: #fff; 
												background-color: #fff; 
												top: 0mm;  
												height: 5.7mm; 
												width: 35mm;">
										<p style="left: 11mm; top: 0mm;">
											<span>/</span>
										</p>
										<p style="left: 23mm; top: 0mm;">
											<span>/</span>
										</p>
									</div>
									<div style="border-width: 0.3mm; 
												border-color: #000; 
												background-color: #ccc; 
												corner-radius: 1mm; 
												text-align: right; 
												top: 8mm;  
												height: 5.7mm; 
												width: 28.4mm; 
												padding-right: 6mm;">
										<p>
											<span>.</span>
										</p>
									</div>
									<div style="border-width: 0.3mm; 
												border-color: #000; 
												background-color: #ccc; 
												corner-radius: 1mm; 
												text-align: right; 
												top: 16mm;  
												height: 5.7mm; 
												width: 28.4mm; 
												padding-right: 6mm;">
										<p>
											<span>.</span>
										</p>
									</div>
									<p style="top: 24mm; left: -3mm;">
										<span style="font-weight: bold;"><xsl:value-of select="/invoice/currency-symbol" /></span>
									</p>
									<div style="border-width: 0.3mm; 
												border-color: #000; 
												background-color: #aaa; 
												corner-radius: 1mm; 
												text-align: right; 
												top: 24mm;  
												height: 5.7mm; 
												width: 28.4mm; 
												padding-right: 6mm;">
										<p>
											<span style="font-weight: bold;">.</span>
										</p>
									</div>
								</div>
							</div>
							
							
						</div>
		
					</div>
		
				</page>
	
				<page type="pageTwo">
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveEvenPage" />
		
					<!-- Cut Here -->
					<xsl:call-template name="scissorLine" />
					
					<div style="top: 25mm; left: 10mm; width: 82mm; height: 176mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="invoice-items" />
					
					</div>
	
					<div style="top: 25mm; left: 103mm; width: 82mm; height: 176mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="invoice-items" />
					
					</div>
	
				</page>
	
				<page type="pageOdd">
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveOddPage" />
		
					<div style="top: 25mm; left: 10mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="invoice-items" />
					
					</div>
		
					<div style="top: 25mm; left: 103mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="invoice-items" />
					
					</div>
				</page>
	
				<page type="pageEven">
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveEvenPage" />
		
					<div style="top: 25mm; left: 10mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="invoice-items" />
					
					</div>
		
					<div style="top: 25mm; left: 103mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="invoice-items" />
					
					</div>
				</page>
					
			</pages>
			
			<page-wrap-contents>
	
				<page-wrap-content id="invoice-items">
	
					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section">
						every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section">
						after-first-section header
					</wrapped-header>
					
					<wrapped-header include="every-page">
						every-page header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page">
						first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page">
						other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="every-page">
						every-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page">
						after-first-page header
					</wrapped-header>
					
					<wrapped-content>
						<p><span>Content 1</span></p>
						<p><span>Content 2</span></p>
						<p><span>Content 3</span></p>
						<p><span>Content 4</span></p>
						<p><span>Content 5</span></p>
						<p><span>Content 6</span></p>
						<p><span>Content 7</span></p>
						<p><span>Content 8</span></p>
						<p><span>Content 9</span></p>
						<p><span>Content 10</span></p>
						<p><span>Content 11</span></p>
						<p><span>Content 12</span></p>
						<p><span>Content 13</span></p>
						<p><span>Content 14</span></p>
						<p><span>Content 15</span></p>
						<p><span>Content 16</span></p>
						<p><span>Content 17</span></p>
						<p><span>Content 18</span></p>
						<p><span>Content 19</span></p>
						<p><span>Content 20</span></p>
						<p><span>Content 21</span></p>
						<p><span>Content 22</span></p>
						<p><span>Content 23</span></p>
						<p><span>Content 24</span></p>
						<p><span>Content 25</span></p>
						<p><span>Content 26</span></p>
						<p><span>Content 27</span></p>
						<p><span>Content 28</span></p>
						<p><span>Content 29</span></p>
						<p><span>Content 30</span></p>
						<p><span>Content 31</span></p>
						<p><span>Content 32</span></p>
						<p><span>Content 33</span></p>
						<p><span>Content 34</span></p>
						<p><span>Content 35</span></p>
						<p><span>Content 36</span></p>
						<p><span>Content 37</span></p>
						<p><span>Content 38</span></p>
						<p><span>Content 39</span></p>
						<p><span>Content 40</span></p>
						<p><span>Content 41</span></p>
						<p><span>Content 42</span></p>
						<p><span>Content 43</span></p>
						<p><span>Content 44</span></p>
						<p><span>Content 45</span></p>
						<p><span>Content 46</span></p>
						<p><span>Content 47</span></p>
						<p><span>Content 48</span></p>
						<p><span>Content 49</span></p>
						<p><span>Content 50</span></p>
						<p><span>Content 51</span></p>
						<p><span>Content 52</span></p>
						<p><span>Content 53</span></p>
						<p><span>Content 54</span></p>
						<p><span>Content 55</span></p>
						<p><span>Content 56</span></p>
						<p><span>Content 57</span></p>
						<p><span>Content 58</span></p>
						<p><span>Content 59</span></p>
						<p><span>Content 60</span></p>
						<p><span>Content 61</span></p>
						<p><span>Content 62</span></p>
						<p><span>Content 63</span></p>
						<p><span>Content 64</span></p>
						<p><span>Content 65</span></p>
						<p><span>Content 66</span></p>
						<p><span>Content 67</span></p>
						<p><span>Content 68</span></p>
						<p><span>Content 69</span></p>
						<p><span>Content 70</span></p>
						<p><span>Content 71</span></p>
						<p><span>Content 72</span></p>
						<p><span>Content 73</span></p>
						<p><span>Content 74</span></p>
						<p><span>Content 75</span></p>
						<p><span>Content 76</span></p>
						<p><span>Content 77</span></p>
						<p><span>Content 78</span></p>
						<p><span>Content 79</span></p>
						<p><span>Content 80</span></p>
						<p><span>Content 81</span></p>
						<p><span>Content 82</span></p>
						<p><span>Content 83</span></p>
						<p><span>Content 84</span></p>
						<p><span>Content 85</span></p>
						<p><span>Content 86</span></p>
						<p><span>Content 87</span></p>
						<p><span>Content 88</span></p>
						<p><span>Content 89</span></p>
						<p><span>Content 90</span></p>
						<p><span>Content 91</span></p>
						<p><span>Content 92</span></p>
						<p><span>Content 93</span></p>
						<p><span>Content 94</span></p>
						<p><span>Content 95</span></p>
						<p><span>Content 96</span></p>
						<p><span>Content 97</span></p>
						<p><span>Content 98</span></p>
						<p><span>Content 99</span></p>
						<p><span>Content 100</span></p>
						<p><span>Content 101</span></p>
						<p><span>Content 102</span></p>
						<p><span>Content 103</span></p>
						<p><span>Content 104</span></p>
						<p><span>Content 105</span></p>
						<p><span>Content 106</span></p>
						<p><span>Content 107</span></p>
						<p><span>Content 108</span></p>
						<p><span>Content 109</span></p>
						<p><span>Content 110</span></p>
						<p><span>Content 111</span></p>
						<p><span>Content 112</span></p>
						<p><span>Content 113</span></p>
						<p><span>Content 114</span></p>
						<p><span>Content 115</span></p>
						<p><span>Content 116</span></p>
						<p><span>Content 117</span></p>
						<p><span>Content 118</span></p>
						<p><span>Content 119</span></p>
						<p><span>Content 120</span></p>
						<p><span>Content 121</span></p>
						<p><span>Content 122</span></p>
						<p><span>Content 123</span></p>
						<p><span>Content 124</span></p>
						<p><span>Content 125</span></p>
						<p><span>Content 126</span></p>
						<p><span>Content 127</span></p>
						<p><span>Content 128</span></p>
						<p><span>Content 129</span></p>
						<p><span>Content 130</span></p>
						<p><span>Content 131</span></p>
						<p><span>Content 132</span></p>
						<p><span>Content 133</span></p>
						<p><span>Content 134</span></p>
						<p><span>Content 135</span></p>
						<p><span>Content 136</span></p>
						<p><span>Content 137</span></p>
						<p><span>Content 138</span></p>
						<p><span>Content 139</span></p>
						<p><span>Content 140</span></p>
						<p><span>Content 141</span></p>
						<p><span>Content 142</span></p>
						<p><span>Content 143</span></p>
						<p><span>Content 144</span></p>
						<p><span>Content 145</span></p>
						<p><span>Content 146</span></p>
						<p><span>Content 147</span></p>
						<p><span>Content 148</span></p>
						<p><span>Content 149</span></p>
						<p><span>Content 150</span></p>
						<p><span>Content 151</span></p>
						<p><span>Content 152</span></p>
						<p><span>Content 153</span></p>
						<p><span>Content 154</span></p>
						<p><span>Content 155</span></p>
						<p><span>Content 156</span></p>
						<p><span>Content 157</span></p>
						<p><span>Content 158</span></p>
						<p><span>Content 159</span></p>
						<p><span>Content 160</span></p>
						<p><span>Content 161</span></p>
						<p><span>Content 162</span></p>
						<p><span>Content 163</span></p>
						<p><span>Content 164</span></p>
						<p><span>Content 165</span></p>
						<p><span>Content 166</span></p>
						<p><span>Content 167</span></p>
						<p><span>Content 168</span></p>
						<p><span>Content 169</span></p>
						<p><span>Content 170</span></p>
						<p><span>Content 171</span></p>
						<p><span>Content 172</span></p>
						<p><span>Content 173</span></p>
						<p><span>Content 174</span></p>
						<p><span>Content 175</span></p>
						<p><span>Content 176</span></p>
						<p><span>Content 177</span></p>
						<p><span>Content 178</span></p>
						<p><span>Content 179</span></p>
						<p><span>Content 180</span></p>
						<p><span>Content 181</span></p>
						<p><span>Content 182</span></p>
						<p><span>Content 183</span></p>
						<p><span>Content 184</span></p>
						<p><span>Content 185</span></p>
						<p><span>Content 186</span></p>
						<p><span>Content 187</span></p>
						<p><span>Content 188</span></p>
						<p><span>Content 189</span></p>
						<p><span>Content 190</span></p>
						<p><span>Content 191</span></p>
						<p><span>Content 192</span></p>
						<p><span>Content 193</span></p>
						<p><span>Content 194</span></p>
						<p><span>Content 195</span></p>
						<p><span>Content 196</span></p>
						<p><span>Content 197</span></p>
						<p><span>Content 198</span></p>
						<p><span>Content 199</span></p>
						<p><span>Content 200</span></p>
						<p><span>Content 201</span></p>
						<p><span>Content 202</span></p>
						<p><span>Content 203</span></p>
						<p><span>Content 204</span></p>
						<p><span>Content 205</span></p>
						<p><span>Content 206</span></p>
						<p><span>Content 207</span></p>
						<p><span>Content 208</span></p>
						<p><span>Content 209</span></p>
						<p><span>Content 210</span></p>
						<p><span>Content 211</span></p>
						<p><span>Content 212</span></p>
						<p><span>Content 213</span></p>
						<p><span>Content 214</span></p>
						<p><span>Content 215</span></p>
						<p><span>Content 216</span></p>
						<p><span>Content 217</span></p>
						<p><span>Content 218</span></p>
						<p><span>Content 219</span></p>
						<p><span>Content 220</span></p>
						<p><span>Content 221</span></p>
						<p><span>Content 222</span></p>
						<p><span>Content 223</span></p>
						<p><span>Content 224</span></p>
						<p><span>Content 225</span></p>
						<p><span>Content 226</span></p>
						<p><span>Content 227</span></p>
						<p><span>Content 228</span></p>
						<p><span>Content 229</span></p>
						<p><span>Content 230</span></p>
						<p><span>Content 231</span></p>
						<p><span>Content 232</span></p>
						<p><span>Content 233</span></p>
						<p><span>Content 234</span></p>
						<p><span>Content 235</span></p>
						<p><span>Content 236</span></p>
						<p><span>Content 237</span></p>
						<p><span>Content 238</span></p>
						<p><span>Content 239</span></p>
						<p><span>Content 240</span></p>
						<p><span>Content 241</span></p>
						<p><span>Content 242</span></p>
						<p><span>Content 243</span></p>
						<p><span>Content 244</span></p>
						<p><span>Content 245</span></p>
						<p><span>Content 246</span></p>
						<p><span>Content 247</span></p>
						<p><span>Content 248</span></p>
						<p><span>Content 249</span></p>
						<p><span>Content 250</span></p>
						<p><span>Content 251</span></p>
						<p><span>Content 252</span></p>
						<p><span>Content 253</span></p>
						<p><span>Content 254</span></p>
						<p><span>Content 255</span></p>
						<p><span>Content 256</span></p>
						<p><span>Content 257</span></p>
						<p><span>Content 258</span></p>
						<p><span>Content 259</span></p>
						<p><span>Content 260</span></p>
						<p><span>Content 261</span></p>
						<p><span>Content 262</span></p>
						<p><span>Content 263</span></p>
						<p><span>Content 264</span></p>
						<p><span>Content 265</span></p>
						<p><span>Content 266</span></p>
						<p><span>Content 267</span></p>
						<p><span>Content 268</span></p>
						<p><span>Content 269</span></p>
						<p><span>Content 270</span></p>
						<p><span>Content 271</span></p>
						<p><span>Content 272</span></p>
						<p><span>Content 273</span></p>
						<p><span>Content 274</span></p>
						<p><span>Content 275</span></p>
						<p><span>Content 276</span></p>
						<p><span>Content 277</span></p>
						<p><span>Content 278</span></p>
						<p><span>Content 279</span></p>
						<p><span>Content 280</span></p>
						<p><span>Content 281</span></p>
						<p><span>Content 282</span></p>
						<p><span>Content 283</span></p>
						<p><span>Content 284</span></p>
						<p><span>Content 285</span></p>
						<p><span>Content 286</span></p>
						<p><span>Content 287</span></p>
						<p><span>Content 288</span></p>
						<p><span>Content 289</span></p>
						<p><span>Content 290</span></p>
						<p><span>Content 291</span></p>
						<p><span>Content 292</span></p>
						<p><span>Content 293</span></p>
						<p><span>Content 294</span></p>
						<p><span>Content 295</span></p>
						<p><span>Content 296</span></p>
						<p><span>Content 297</span></p>
						<p><span>Content 298</span></p>
						<p><span>Content 299</span></p>
						<p><span>Content 300</span></p>
						<p><span>Content 301</span></p>
						<p><span>Content 302</span></p>
						<p><span>Content 303</span></p>
						<p><span>Content 304</span></p>
						<p><span>Content 305</span></p>
						<p><span>Content 306</span></p>
						<p><span>Content 307</span></p>
						<p><span>Content 308</span></p>
						<p><span>Content 309</span></p>
						<p><span>Content 310</span></p>
						<p><span>Content 311</span></p>
						<p><span>Content 312</span></p>
						<p><span>Content 313</span></p>
						<p><span>Content 314</span></p>
						<p><span>Content 315</span></p>
						<p><span>Content 316</span></p>
						<p><span>Content 317</span></p>
						<p><span>Content 318</span></p>
						<p><span>Content 319</span></p>
						<p><span>Content 320</span></p>
						<p><span>Content 321</span></p>
						<p><span>Content 322</span></p>
						<p><span>Content 323</span></p>
						<p><span>Content 324</span></p>
						<p><span>Content 325</span></p>
						<p><span>Content 326</span></p>
						<p><span>Content 327</span></p>
						<p><span>Content 328</span></p>
						<p><span>Content 329</span></p>
						<p><span>Content 330</span></p>
						<p><span>Content 331</span></p>
						<p><span>Content 332</span></p>
						<p><span>Content 333</span></p>
						<p><span>Content 334</span></p>
						<p><span>Content 335</span></p>
						<p><span>Content 336</span></p>
						<p><span>Content 337</span></p>
						<p><span>Content 338</span></p>
						<p><span>Content 339</span></p>
						<p><span>Content 340</span></p>
						<p><span>Content 341</span></p>
						<p><span>Content 342</span></p>
						<p><span>Content 343</span></p>
						<p><span>Content 344</span></p>
						<p><span>Content 345</span></p>
						<p><span>Content 346</span></p>
						<p><span>Content 347</span></p>
						<p><span>Content 348</span></p>
						<p><span>Content 349</span></p>
						<p><span>Content 350</span></p>
						<p><span>Content 351</span></p>
						<p><span>Content 352</span></p>
						<p><span>Content 353</span></p>
						<p><span>Content 354</span></p>
						<p><span>Content 355</span></p>
						<p><span>Content 356</span></p>
						<p><span>Content 357</span></p>
						<p><span>Content 358</span></p>
						<p><span>Content 359</span></p>
						<p><span>Content 360</span></p>
						<p><span>Content 361</span></p>
						<p><span>Content 362</span></p>
						<p><span>Content 363</span></p>
						<p><span>Content 364</span></p>
						<p><span>Content 365</span></p>
						<p><span>Content 366</span></p>
						<p><span>Content 367</span></p>
						<p><span>Content 368</span></p>
						<p><span>Content 369</span></p>
						<p><span>Content 370</span></p>
						<p><span>Content 371</span></p>
						<p><span>Content 372</span></p>
						<p><span>Content 373</span></p>
						<p><span>Content 374</span></p>
						<p><span>Content 375</span></p>
						<p><span>Content 376</span></p>
						<p><span>Content 377</span></p>
						<p><span>Content 378</span></p>
						<p><span>Content 379</span></p>
						<p><span>Content 380</span></p>
						<p><span>Content 381</span></p>
						<p><span>Content 382</span></p>
						<p><span>Content 383</span></p>
						<p><span>Content 384</span></p>
						<p><span>Content 385</span></p>
						<p><span>Content 386</span></p>
						<p><span>Content 387</span></p>
						<p><span>Content 388</span></p>
						<p><span>Content 389</span></p>
						<p><span>Content 390</span></p>
						<p><span>Content 391</span></p>
						<p><span>Content 392</span></p>
						<p><span>Content 393</span></p>
						<p><span>Content 394</span></p>
						<p><span>Content 395</span></p>
						<p><span>Content 396</span></p>
						<p><span>Content 397</span></p>
						<p><span>Content 398</span></p>
						<p><span>Content 399</span></p>
						<p><span>Content 400</span></p>
						<p><span>Content 401</span></p>
						<p><span>Content 402</span></p>
						<p><span>Content 403</span></p>
						<p><span>Content 404</span></p>
						<p><span>Content 405</span></p>
						<p><span>Content 406</span></p>
						<p><span>Content 407</span></p>
						<p><span>Content 408</span></p>
						<p><span>Content 409</span></p>
						<p><span>Content 410</span></p>
						<p><span>Content 411</span></p>
						<p><span>Content 412</span></p>
						<p><span>Content 413</span></p>
						<p><span>Content 414</span></p>
						<p><span>Content 415</span></p>
						<p><span>Content 416</span></p>
						<p><span>Content 417</span></p>
						<p><span>Content 418</span></p>
						<p><span>Content 419</span></p>
						<p><span>Content 420</span></p>
						<p><span>Content 421</span></p>
						<p><span>Content 422</span></p>
						<p><span>Content 423</span></p>
						<p><span>Content 424</span></p>
						<p><span>Content 425</span></p>
						<p><span>Content 426</span></p>
						<p><span>Content 427</span></p>
						<p><span>Content 428</span></p>
						<p><span>Content 429</span></p>
						<p><span>Content 430</span></p>
						<p><span>Content 431</span></p>
						<p><span>Content 432</span></p>
						<p><span>Content 433</span></p>
						<p><span>Content 434</span></p>
						<p><span>Content 435</span></p>
						<p><span>Content 436</span></p>
						<p><span>Content 437</span></p>
						<p><span>Content 438</span></p>
						<p><span>Content 439</span></p>
						<p><span>Content 440</span></p>
						<p><span>Content 441</span></p>
						<p><span>Content 442</span></p>
						<p><span>Content 443</span></p>
						<p><span>Content 444</span></p>
						<p><span>Content 445</span></p>
						<p><span>Content 446</span></p>
						<p><span>Content 447</span></p>
						<p><span>Content 448</span></p>
						<p><span>Content 449</span></p>
						<p><span>Content 450</span></p>
						<p><span>Content 451</span></p>
						<p><span>Content 452</span></p>
						<p><span>Content 453</span></p>
						<p><span>Content 454</span></p>
						<p><span>Content 455</span></p>
						<p><span>Content 456</span></p>
						<p><span>Content 457</span></p>
						<p><span>Content 458</span></p>
						<p><span>Content 459</span></p>
						<p><span>Content 460</span></p>
						<p><span>Content 461</span></p>
						<p><span>Content 462</span></p>
						<p><span>Content 463</span></p>
						<p><span>Content 464</span></p>
						<p><span>Content 465</span></p>
						<p><span>Content 466</span></p>
						<p><span>Content 467</span></p>
						<p><span>Content 468</span></p>
						<p><span>Content 469</span></p>
						<p><span>Content 470</span></p>
						<p><span>Content 471</span></p>
						<p><span>Content 472</span></p>
						<p><span>Content 473</span></p>
						<p><span>Content 474</span></p>
						<p><span>Content 475</span></p>
						<p><span>Content 476</span></p>
						<p><span>Content 477</span></p>
						<p><span>Content 478</span></p>
						<p><span>Content 479</span></p>
						<p><span>Content 480</span></p>
						<p><span>Content 481</span></p>
						<p><span>Content 482</span></p>
						<p><span>Content 483</span></p>
						<p><span>Content 484</span></p>
						<p><span>Content 485</span></p>
						<p><span>Content 486</span></p>
						<p><span>Content 487</span></p>
						<p><span>Content 488</span></p>
						<p><span>Content 489</span></p>
						<p><span>Content 490</span></p>
						<p><span>Content 491</span></p>
						<p><span>Content 492</span></p>
						<p><span>Content 493</span></p>
						<p><span>Content 494</span></p>
						<p><span>Content 495</span></p>
						<p><span>Content 496</span></p>
						<p><span>Content 497</span></p>
						<p><span>Content 498</span></p>
					<!--	<p><span>Content 499</span></p> -->
					<!--	<p><span>Content 500</span></p> -->
					</wrapped-content>
	  
					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						last-section footer
					</wrapped-footer>
	
					<wrapped-footer include="every-section">
						every-section footer
					</wrapped-footer>
	
					<wrapped-footer include="before-last-section">
						before-last-section footer
					</wrapped-footer>
	
					<wrapped-footer include="every-page">
						every-page footer
					</wrapped-footer>
	
					<wrapped-footer include="before-last-page">
						before-last-page footer
					</wrapped-footer>
	
				</page-wrap-content>			
	
			</page-wrap-contents>		
		
		</body>
	
	</html>

</xsl:template>

<xsl:template name="blueCurveOddPage">
				<img src="./images/head_odd.jpg" style="top: 0; right: 0; width: 190mm; height: 60.5mm;" />
				<!--
				<div style="left: 20mm; top: -12.5mm; width: 220mm; corner-radius: 12.5mm; height: 46.5mm; background-color: #bcf; border-color: #fff; border-width: 12.5mm;"></div>
				<div style="left: 110mm; top: 0; width: 110mm; height: 90mm; background-color: #bcf;"></div>
				<div style="left: -470mm; top: 34mm; width: 800mm; corner-radius: 210mm; height: 420mm; background-color: #fff;"></div>
				-->
</xsl:template>

<xsl:template name="blueCurveEvenPage">
				<img src="./images/head_even.jpg" style="top: 0; left: 0; width: 190mm; height: 60.5mm;" />
				<!--
				<div style="left: -30mm; top: -12.5mm; width: 220mm; corner-radius: 12.5mm; height: 46.5mm; background-color: #bcf; border-color: #fff; border-width: 12.5mm;"></div>
				<div style="left: -10mm; top: 0; width: 110mm; height: 90mm; background-color: #bcf;"></div>
				<div style="left: -120mm; top: 34mm; width: 800mm; corner-radius: 210mm; height: 420mm; background-color: #fff;"></div>
				-->
</xsl:template>

<xsl:template name="logoAndLabel">
				<!--
				<div style="top: 5mm; left: 29mm;">
					<img src="images/logo.png" style="width: 63mm; height: 16mm;"/>
					<p style="color: #000; font-size: 6pt; top: 18mm; left: 3mm; bottom: 0mm;"><span>ABN: 25 104 357 842</span></p>
				</div>
				-->
</xsl:template>

<xsl:template name="contactDetails">
				<div style="top: 10mm; left: 156mm; width: 50mm; font-size: 7pt;">
					<!-- Labels -->
					<div style="top: 0; left: 0; width: 10mm;">
						<p><span>Phone:</span></p>
						<p><span>Fax:</span></p>
						<p><span>Web:</span></p>
						<p><span>Email:</span></p>
					</div>
					<!-- Details -->
					<div style="top: 0; left: 11mm;">
						<p><span>1300 882 172</span></p>
						<p><span>1300 733 393</span></p>
						<p><span>www.voicetalk.com.au</span></p>
						<p><span>contact@voicetalk.com.au</span></p>
					</div>
				</div>
</xsl:template>

<xsl:template name="scissorLine">
				<img src="images/scissors.png" style="left: 0; width: 210mm; height: 3mm; top: 207mm;" />
</xsl:template>

</xsl:stylesheet>