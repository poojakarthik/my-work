<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">

	<embedded-fonts>
	</embedded-fonts>

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
			
				<page type="pageOne" stationery='raw/header_even.raw|raw/scissors_left.raw'>
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveOddPage" />
					
					<!-- Logo and label -->
					<xsl:call-template name="logoAndLabel" />
					
					<!-- Contact details -->
					<xsl:call-template name="contactDetails" />
					
					<!-- Title -->
					<div style="bottom: 250.2mm; width: 210mm; text-align: center; font-size: 14pt;; font-weight: bold;">
						<p>
							<span style="font-size: 19pt;">V</span><span>OICETALK </span>
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
								padding-bottom: 3.2mm; 
								border-width: 0.2mm; 
								border-color: #000; 
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
										background-color: #dcdddf; 
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
										background-color: #eeeff0; 
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
										background-color: #dcdddf; 
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
							<xsl:for-each select="/invoice/charge-summary/charge-category">
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
								
								<p style="font-family: WINGDING; font-weight: normal; font-style: normal;">
								
								&#32;
								&#33;
								&#34;&#34;&#34;
								&#35;
								&#36;
								&#37;
								&#38;
								&#39;
								&#40;
								&#41;
								&#42;
								&#43;
								&#44;
								&#45;
								&#46;
								&#47;
								&#48;
								&#49;
								&#50;
								&#51;
								&#52;
								&#53;
								&#54;
								&#55;
								&#56;
								&#57;
								&#58;
								&#59;
								&#60;
								&#61;
								&#62;
								&#63;
								&#64;
								&#65;
								&#66;
								&#67;
								&#68;
								&#69;
								
								&#70;
								&#71;
								&#72;
								&#73;
								&#74;
								&#75;
								&#76;
								&#77;
								&#78;
								&#79;
								
								&#80;
								&#81;
								&#82;
								&#83;
								&#84;
								&#85;
								&#86;
								&#87;
								&#88;
								&#89;	
															
								&#90;
								&#91;
								&#92;
								&#93;
								&#94;
								&#95;
								&#96;
								&#97;
								&#98;
								&#99;
								
								&#100;
								&#101;
								&#102;
								&#103;
								&#104;
								&#105;
								&#106;
								&#107;
								&#108;
								&#109;
								
								&#110;
								&#111;
								&#112;
								&#113;
								&#114;
								&#115;
								&#116;
								&#117;
								&#118;
								&#119;
								
								</p>
								bradley-hand
								<p style="font-family: bradley-hand; font-weight: normal; font-style: normal;">
								#abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&amp;*()_+-={}|:"&lt;&gt;?[]\;',./~`
								</p>
								
		
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
					
						<img src="images/logo_gray.jpg" style="width: 52.5mm; height: 14.0mm; top: 0.5mm; left: 3mm;" />
						
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
												background-color: #eeeff0; 
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
												background-color: #eeeff0; 
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
												background-color: #dcdddf; 
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
					
						<page-wrap-include content="itemisation" />
					
					</div>
	
					<div style="top: 25mm; left: 103mm; width: 82mm; height: 176mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="itemisation" />
					
					</div>
	
				</page>
	
				<page type="pageOdd">
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveOddPage" />
		
					<div style="top: 25mm; left: 10mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">

						<page-wrap-include content="itemisation" />
					
					</div>
		
					<div style="top: 25mm; left: 103mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="itemisation" />
					
					</div>
				</page>
	
				<page type="pageEven">
		
					<!-- Blue curve at head of page -->
					<xsl:call-template name="blueCurveEvenPage" />
		
					<div style="top: 25mm; left: 10mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="itemisation" />
					
					</div>
		
					<div style="top: 25mm; left: 103mm; width: 82mm; height: 244mm; border-width: 3px; border-color: pink; background-color: #8f8;">
					
						<page-wrap-include content="itemisation" />
					
					</div>
				</page>
					
			</pages>
			
			<page-wrap-contents>
	
				<page-wrap-content id="itemisation" break-after="page">

					<wrapped-header style=".top: -20mm" include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						<p>itemisation first-section header</p>
					</wrapped-header>
					
					<wrapped-header style=".top: -15mm" include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						<p>itemisation every-section header</p>
					</wrapped-header>
					
					<wrapped-header style=".top: -20mm" include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						<p>itemisation after-first-section header</p>
					</wrapped-header>
					
					<wrapped-header style=".top: -10mm" include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						<p>itemisation first-section-on-page header</p>
					</wrapped-header>
					
					<wrapped-header style=".top: -10mm" include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						<p>itemisation other-section-on-page header</p>
					</wrapped-header>
					
					<wrapped-header style=".top: -5mm" include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						itemisation after-first-page header
					</wrapped-header>
					
					<wrapped-content>
						<page-wrap-include content="number-2" />
						<page-wrap-include content="number-1" />
					</wrapped-content>

					<wrapped-footer style=".bottom: -10mm" include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						<p>itemisation last-section footer</p>
					</wrapped-footer>

					<wrapped-footer style=".bottom: -15mm" include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						itemisation every-section footer
					</wrapped-footer>

					<wrapped-footer style=".bottom: -10mm" include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						itemisation before-last-section footer
					</wrapped-footer>

					<wrapped-footer style=".bottom: -20mm" include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						itemisation every-page footer
					</wrapped-footer>

					<wrapped-footer style=".bottom: -25mm" include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						itemisation before-last-page footer
					</wrapped-footer>

				</page-wrap-content>
	
				<page-wrap-content id="number-1" break-after="page">

					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1 first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1 every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1 after-first-section header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1 first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1 other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1 after-first-page header
					</wrapped-header>
					
					<wrapped-content>
						<page-wrap-include content="number-1-mobile-to-mobile" />
						<page-wrap-include content="number-1-international" />
					</wrapped-content>

					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1 last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1 every-section footer
					</wrapped-footer>

					<wrapped-footer include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1 before-last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1 every-page footer
					</wrapped-footer>

					<wrapped-footer include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1 before-last-page footer
					</wrapped-footer>

				</page-wrap-content>
	
				<page-wrap-content id="number-2" break-after="page">

					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2 first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2 every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2 after-first-section header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2 first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2 other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2 after-first-page header
					</wrapped-header>
					
					<wrapped-content>
						<page-wrap-include content="number-2-international" />
						<page-wrap-include content="number-2-mobile-to-mobile" />
					</wrapped-content>

					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2 last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2 every-section footer
					</wrapped-footer>

					<wrapped-footer include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2 before-last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2 every-page footer
					</wrapped-footer>

					<wrapped-footer include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2 before-last-page footer
					</wrapped-footer>

				</page-wrap-content>
	
				<page-wrap-content id="number-1-mobile-to-mobile">
	
					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-mobile-to-mobile first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-mobile-to-mobile every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-mobile-to-mobile after-first-section header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-mobile-to-mobile first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-mobile-to-mobile other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-mobile-to-mobile after-first-page header
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
					</wrapped-content>

					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-mobile-to-mobile last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-mobile-to-mobile every-section footer
					</wrapped-footer>

					<wrapped-footer include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-mobile-to-mobile before-last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-mobile-to-mobile every-page footer
					</wrapped-footer>

					<wrapped-footer include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-mobile-to-mobile before-last-page footer
					</wrapped-footer>
	
				</page-wrap-content>			
	
				<page-wrap-content id="number-2-mobile-to-mobile">
	
					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-mobile-to-mobile first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-mobile-to-mobile every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-mobile-to-mobile after-first-section header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-mobile-to-mobile first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-mobile-to-mobile other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-mobile-to-mobile after-first-page header
					</wrapped-header>
					
					<wrapped-content>
						<p><span>Content 01</span></p>
						<p><span>Content 02</span></p>
						<p><span>Content 03</span></p>
						<p><span>Content 04</span></p>
						<p><span>Content 05</span></p>
						<p><span>Content 06</span></p>
						<p><span>Content 07</span></p>
						<p><span>Content 08</span></p>
						<p><span>Content 09</span></p>
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
					</wrapped-content>

					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-mobile-to-mobile last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-mobile-to-mobile every-section footer
					</wrapped-footer>

					<wrapped-footer include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-mobile-to-mobile before-last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-mobile-to-mobile every-page footer
					</wrapped-footer>

					<wrapped-footer include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-mobile-to-mobile before-last-page footer
					</wrapped-footer>

				</page-wrap-content>			
	
				<page-wrap-content id="number-1-international">
	
					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-international first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-international every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-international after-first-section header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-international first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-international other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-1-international after-first-page header
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
					</wrapped-content>

					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-international last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-international every-section footer
					</wrapped-footer>

					<wrapped-footer include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-international before-last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-international every-page footer
					</wrapped-footer>

					<wrapped-footer include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-1-international before-last-page footer
					</wrapped-footer>
	
				</page-wrap-content>			
	
				<page-wrap-content id="number-2-international">
	
					<wrapped-header include="first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-international first-section header
					</wrapped-header>
					
					<wrapped-header include="every-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-international every-section header
					</wrapped-header>
					
					<wrapped-header include="after-first-section"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-international after-first-section header
					</wrapped-header>
					
					<wrapped-header include="first-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-international first-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="other-section-on-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-international other-section-on-page header
					</wrapped-header>
					
					<wrapped-header include="after-first-page"> <!-- first-section every-section after-first-section every-page(==first-section-on-page) other-section-on-page after-first-page -->
						number-2-international after-first-page header
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
					</wrapped-content>

					<wrapped-footer include="last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-international last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-international every-section footer
					</wrapped-footer>

					<wrapped-footer include="before-last-section"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-international before-last-section footer
					</wrapped-footer>

					<wrapped-footer include="every-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-international every-page footer
					</wrapped-footer>

					<wrapped-footer include="before-last-page"><!-- last-section every-section before-last-section every-page before-last-page -->
						number-2-international before-last-page footer
					</wrapped-footer>
	
				</page-wrap-content>			
	
			</page-wrap-contents>		
		
		</body>
	
	</html>

</xsl:template>

<xsl:template name="blueCurveOddPage">
				<div style="left: 20mm; top: -12.5mm; width: 220mm; corner-radius: 12.5mm; height: 46.5mm; background-color: #bcf; border-color: #fff; border-width: 12.5mm;"></div>
				<div style="left: 110mm; top: 0; width: 110mm; height: 90mm; background-color: #bcf;"></div>
				<div style="left: -470mm; top: 34mm; width: 800mm; corner-radius: 210mm; height: 420mm; background-color: #fff;"></div>
</xsl:template>

<xsl:template name="blueCurveEvenPage">
				<div style="left: -30mm; top: -12.5mm; width: 220mm; corner-radius: 12.5mm; height: 46.5mm; background-color: #bcf; border-color: #fff; border-width: 12.5mm;"></div>
				<div style="left: -10mm; top: 0; width: 110mm; height: 90mm; background-color: #bcf;"></div>
				<div style="left: -120mm; top: 34mm; width: 800mm; corner-radius: 210mm; height: 420mm; background-color: #fff;"></div>
</xsl:template>

<xsl:template name="logoAndLabel">
				<div style="top: 5mm; left: 29mm;">
					<img src="images/logo.png" style="width: 63mm; height: 16mm;"/>
					<p style="color: #000; font-size: 6pt; top: 18mm; left: 3mm; bottom: 0mm;"><span>ABN: 25 104 357 842</span></p>
				</div>
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
				<div style="font-family: wingding; font-style: normal; font-weight: normal; bottom: 90mm; left: 24.5mm; font-size: 12pt;">&#35;</div>
				<img src="images/scissors.png" style="left: 0; width: 212.5mm; height: 0.5; bottom: 90mm;" />
</xsl:template>

</xsl:stylesheet>