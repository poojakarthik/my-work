<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<!-- Voicetalk -->
	<!--
	<xsl:variable name="bill_express_biller_id">000376</xsl:variable>
	<xsl:variable name="bill_pay_biller_code">63412</xsl:variable>
	<xsl:variable name="abn">25 104 357 842</xsl:variable>
	<xsl:variable name="business-phone">1300 882 172</xsl:variable>
	<xsl:variable name="business-fax">1300 733 393</xsl:variable>
	<xsl:variable name="business-web">www.voicetalk.com.au</xsl:variable>
	<xsl:variable name="business-email">contact@voicetalk.com.au</xsl:variable>
	<xsl:variable name="customer-service-team-phone">1300 653 588</xsl:variable>
	<xsl:variable name="business-payable-name">Voicetalk Pty Ltd</xsl:variable>
	<xsl:variable name="business-payable-address">Locked Bag 4000, Fortitude Valley, QLD 4006</xsl:variable>
	<xsl:variable name="business-friendly-name">Voicetalk</xsl:variable>

	<xsl:template name="statement-title">
		<p>
			<span style="font-size: 19pt;">D</span><span>OCUMENT </span>
			<span style="font-size: 19pt;">T</span><span>ITLE</span>
		</p>
	</xsl:template>

	<xsl:template name="logo-and-label">
		<div style="top: 74.6pt; left: 80.19pt; height: 0pt;">
			<img src="images/logo.png" style="media: email; width: 177.32pt; height: 45.56pt; bottom: 0pt; left: -0.47pt" />
			<p style="font-family: Arial_Narrow; font-size: 7pt; top: 3.19pt; left: 0pt; "><span>ABN: <xsl:value-of select="$abn" /></span></p>
		</div>
	</xsl:template>

	<xsl:template name="pay-slip-logo">
		<img src="images/logo_grey.png" style="bottom: 0; width: 132.72pt; height: 35.43pt;" />
	</xsl:template>
	-->

	<!-- Telcoblue -->
	<!-- -->
	<xsl:variable name="bill_express_biller_id">000376</xsl:variable><!-- MUST BE 6 DIGITS - PAD WITH LEADING ZEROS IF NECESSARY -->
	<xsl:variable name="bill_pay_biller_code">63412</xsl:variable>
	<xsl:variable name="abn">25 104 357 842</xsl:variable>
	<xsl:variable name="business-phone">1300 835 262</xsl:variable>
	<xsl:variable name="business-fax">1300 733 393</xsl:variable>
	<xsl:variable name="business-web">www.telcoblue.com.au</xsl:variable>
	<xsl:variable name="business-email">contact@telcoblue.com.au</xsl:variable>
	<xsl:variable name="customer-service-team-phone">1300 797 114</xsl:variable>
	<xsl:variable name="business-payable-name">Telce Blue Pty Ltd</xsl:variable>
	<xsl:variable name="business-payable-address">Locked Bag 4000, Fortitude Valley, QLD 4006</xsl:variable>
	<xsl:variable name="business-friendly-name">Telco Blue</xsl:variable>

	<xsl:template name="statement-title">
		<p>
			<span style="font-size: 19pt;">D</span><span>OCUMENT </span>
			<span style="font-size: 19pt;">T</span><span>ITLE</span>
		</p>
	</xsl:template>

	<xsl:template name="logo-and-label">
		<div style="top: 74.6pt; left: 80.19pt; height: 0pt;">
			<img src="images/logo.png" style="media: email; width: 223.11pt; height: 32.12pt; bottom: 0pt; left:-15.96pt" />
			<p style="font-family: Arial_Narrow; font-size: 7pt; top: 3.19pt; left: 0pt;"><span>ABN: <xsl:value-of select="$abn" /></span></p>
		</div>
	</xsl:template>

	<xsl:template name="pay-slip-logo">
		<img src="images/logo_grey.png" style="bottom: 0; left: -10.25; width: 155.23pt; height: 22.55pt;" />
	</xsl:template>
	<!-- -->

	<!-- Create the zero padded account number for the barcode -->
	<xsl:variable name="account-number"><xsl:value-of select="/Document/Account/@Id" /></xsl:variable>
	<xsl:variable name="account-reference"><xsl:value-of select="/Document/Payment/BillExpress/CustomerReference" /></xsl:variable><!-- SHOULD BE 20 DIGITS INCLUDING A 'mod 10' CHECK DIGIT -->

	<!-- Create the zero padded amount (in cents) for the barcode -->
	<xsl:variable name="decimal_amount"><xsl:value-of select="/Document/Statement/TotalOwing" /></xsl:variable>
	<xsl:variable name="dollars" select="substring-before($decimal_amount, '.')" />
	<xsl:variable name="cents" select="substring-after($decimal_amount, '.')" />
	<xsl:variable name="cents-amount"><xsl:value-of select="$dollars" /><xsl:value-of select="$cents" /></xsl:variable>
	<xsl:variable name="amount-leading-zeros">00000000<xsl:value-of select="$cents-amount" /></xsl:variable>
	<xsl:variable name="amount-leading-zeros-length" select="string-length($amount-leading-zeros)" />
	<xsl:variable name="amount-padded" select="substring($amount-leading-zeros, $amount-leading-zeros-length - 7)" />

	<xsl:variable name="cr-suffix"><xsl:value-of select="/Document/Currency/Negative[@Location='Suffix']" /></xsl:variable>

	<!-- Create the barcode value -->
	<xsl:variable name="bill_express_barcode_value">C<xsl:value-of select="$bill_express_biller_id" /><xsl:value-of select="$account-reference" /><xsl:value-of select="$amount-padded" /></xsl:variable>




	<xsl:template match="/">

		<!-- embedded-fonts of type TTF can be included in PDF documents, but the license must permit them to be embedded for this to work.
			 The name of the font should match that used in the 'style' attribute of elements in the XML output.
			 The relative path to the font files should be specified to allow the font to be loaded. -->

		<embedded-fonts>

			<!-- Use embedded ARIAL fonts for the printed versions -->
			<embedded-font media="PRINT" name="ARIAL_NARROW" 		path="fonts/arial_narrow.ttf" />
			<embedded-font media="PRINT" name="ARIAL" 				path="fonts/arial.ttf" />
			<embedded-font media="PRINT" name="ARIAL BOLD" 			path="fonts/arial_bold.ttf" />
			<embedded-font media="PRINT" name="ARIAL BOLD ITALIC" 	path="fonts/arial_bold_italic.ttf" />
			<embedded-font media="PRINT" name="ARIAL ITALIC" 		path="fonts/arial_italic.ttf" />

			<!-- Use standard, non-embedded fonts for the email versions -->
			<embedded-font media="EMAIL" name="ARIAL_NARROW" 		default="HELVETICA" />
			<embedded-font media="EMAIL" name="ARIAL" 				default="HELVETICA" />
			<embedded-font media="EMAIL" name="ARIAL BOLD" 			default="HELVETICA BOLD" />
			<embedded-font media="EMAIL" name="ARIAL BOLD ITALIC" 	default="HELVETICA BOLD ITALIC" />
			<embedded-font media="EMAIL" name="ARIAL ITALIC" 		default="HELVETICA ITALIC" />

		</embedded-fonts>

		<!-- page-order defines the order in which page-object's are to be included -->
		<page-order>

			<!-- always have page 1 & 2, as these have the payment advice at the bottom -->
			<page-object include="always" type="pageOne" />
			<page-object include="always" type="pageTwo" />
	
			<!-- repeat with odd/even pages until all textual content has been included -->
			<page-repeat>

				<page-object include="optional" type="pageOdd" />
				<page-object include="optional" type="pageEven" />

			</page-repeat>

		</page-order>

		<html>

			<body style="font-family: ARIAL; font-size: 12pt; color: #000;">

				<pages>

					<page type="pageOne" stationery="/raw/header_odd.raw">
			
						<!-- Logo and label -->
						<xsl:call-template name="logo-and-label" />

						<!-- Contact details -->
						<xsl:call-template name="contact-details" />

						<!-- Title -->
						<div style="bottom: 706.93pt; left: 56.7pt; width: 538.3pt; text-align: center; font-size: 14pt; font-weight: bold;">
							<xsl:call-template name="document-title" />
						</div>

						<!-- Customer address -->
						<div style="top: 191.69pt; left: 114.04pt; font-size: 12pt; line-height: 14.37pt;">
							<p><span><xsl:value-of select="/Document/Account/Addressee" /></span></p>
							<xsl:for-each select="/Document/Account/AddressLine1">
								<p><span><xsl:value-of select="/Document/Account/AddressLine1" /></span></p>
							</xsl:for-each>
							<xsl:for-each select="/Document/Account/AddressLine2">
								<p><span><xsl:value-of select="/Document/Account/AddressLine2" /></span></p>
							</xsl:for-each>
							<p>
								<span>
									<xsl:value-of select="/Document/Account/Suburb" />
									<xsl:text> </xsl:text>
									<xsl:value-of select="/Document/Account/State" />
									<xsl:text> </xsl:text>
									<xsl:value-of select="/Document/Account/Postcode" />
								</span>
							</p>
						</div>
						
						<!-- Cut Here -->
						<raw src="raw/scissors_front.raw" />
			
	
						<!-- Amounts due -->
						<div style="left: 382.26pt; 
									top: 169.56pt; 
									width: 183.62pt; 
									height: 59.43pt; 
									padding-left: 14pt; 
									padding-right: 14pt; 
									padding-top: 10pt; 
									padding-bottom: 13.8pt; 
									border-width: 0.5pt;
									border-color: #000; 
									corner-radius: 13.15pt; 
									font-size: 10pt;">
							<!-- Labels -->
							<div style="top: 1; left: 0; width: 159pt;">
								<p style="top: -1.5;">
									<span>Overdue please pay </span><span style="font-weight: bold;">now</span>
								</p>
								<p style="top: 25pt;">
									<span>This Invoice due <xsl:value-of select="/Document/Statement/DueDate" /></span>
								</p>
								<p style="top: 51pt;">
									<a href="#accountSummary" style="font-weight: bold;">Total Amount Owing</a>
								</p>
							</div>
							<!-- Details -->
							<div style="top: 0.1; right: 0;">
								<div style="border-width: 0.5pt; 
											border-color: #000; 
											background-color: #dcdddf; 
											corner-radius: 2pt; 
											text-align: right; 
											top: -1;  
											height: 6.7pt; 
											width: 52.84pt; 
											padding-left: 5pt;
											padding-top: 1pt;
											padding-bottom: 9pt;
											padding-right: 3.82pt;">
									<p style="font-weight: bold;">
										<span>
											<xsl:value-of select="/Document/Currency/Symbol" />
											<xsl:call-template name="abs">
												<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/Overdue" /></xsl:with-param>
											</xsl:call-template>
											<xsl:call-template name="cr">
												<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/Overdue" /></xsl:with-param>
											</xsl:call-template>
										</span>
									</p>
								</div>
								<div style="border-width: 0.5pt; 
											border-color: #000; 
											background-color: #eeeff0; 
											corner-radius: 2pt;
											text-align: right; 
											top: 25pt;  
											height: 6.7pt; 
											width: 52.84pt; 
											padding-left: 5pt;
											padding-top: 1pt;
											padding-bottom: 9pt;
											padding-right: 3.82pt;">
									<p>
										<span>
											<xsl:value-of select="/Document/Currency/Symbol" />
											<xsl:call-template name="abs">
												<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/NewCharges" /></xsl:with-param>
											</xsl:call-template>
											<xsl:call-template name="cr">
												<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/NewCharges" /></xsl:with-param>
											</xsl:call-template>
										</span>
									</p>
								</div>
								<div style="border-width: 0.5pt; 
											border-color: #000; 
											background-color: #dcdddf; 
											corner-radius: 2pt;
											text-align: right; 
											top: 51pt; 
											height: 6.7pt; 
											width: 52.84pt; 
											padding-left: 5pt;
											padding-top: 1pt;
											padding-bottom: 9pt;
											padding-right: 3.82pt;">
									<p style="font-weight: bold;">
										<span>
											<xsl:value-of select="/Document/Currency/Symbol" />
											<xsl:call-template name="abs">
												<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/TotalOwing" /></xsl:with-param>
											</xsl:call-template>
											<xsl:call-template name="cr">
												<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/TotalOwing" /></xsl:with-param>
											</xsl:call-template>
										</span>
									</p>
								</div>
							</div>
						</div>
						

						<!-- Document content -->
						<div where="page_odd_section_1" style="bottom: 256.48pt; left: 70.85pt; width: 524.66pt; height: 695pt;">
						
							<page-wrap-include content="document-body" />
						
						</div>
						
						
						<!-- Payment Advice -->
						<div style="top: 210.1mm; left: 57.02pt; width: 180mm;">
						
							<div style="width: 510pt; top: 9pt; left: 0pt; text-align: center; font-size: 12pt; font-weight: bold;">
								<p>
									<span style="font-size: 17pt;">P</span><span>AYMENT </span>
									<span style="font-size: 17pt;">A</span><span>DVICE</span>
								</p>
							</div>
						
							<div style="top: 2pt; left: 8.535pt; font-size: 5pt;">
								<div style="height: 35.43pt; left: 14.8pt;">
									<xsl:call-template name="pay-slip-logo" />
								</div>
								<p style="font-family: Arial_Narrow; left: 14.8pt;">
									<span style="font-size: 6pt;">ABN:</span><span> <xsl:value-of select="$abn" /></span>
								</p>
							</div>
							
							<!-- Left side -->
							<div style="font-family: Arial_Narrow; width: 245pt; left: 14.8pt; top: 0.5pt; font-size: 6pt;">
			
								<!-- Late Fee-->
								<div style="left: 0; top: 17mm;">
									<p style="left: 17mm; font-size: 7pt;"><span>LATE FEE</span></p>
									<p style="left: 17mm;"><span>To Avoid late payment fee please ensure your payment is made by the due date</span></p>
								</div>
			
								<div style="left: 0; top: 23.8mm;">
									<img src="images/bill_pay.png" style="width: 6mm; height: 9mm; top: 1.1mm; left: 4mm;" />
									<p style="left: 17mm;">
										<span style="font-size: 7pt;">BPAY </span>
										<span>- Biller Code: <xsl:value-of select="$bill_pay_biller_code" /> Customer Ref:<xsl:text> </xsl:text><xsl:value-of select="/Document/Payment/BPay/CustomerReference" /></span>
									</p>
									<p style="left: 17mm;">
										<span>Call your bank, credit union or building society to make payment from your cheque, savings or credit card account. More info: www.bpay.com.au</span>
									</p>
								</div>
			
								<div style="left: 0; top: 33mm;">
									<p style="left: 17mm; font-size: 7pt;"><span>CREDIT CARD</span></p>
									<p style="left: 17mm;"><span>Call <xsl:value-of select="$business-payable-name" /> on <xsl:value-of select="$customer-service-team-phone" /> to pay your bill. <xsl:value-of select="$business-friendly-name" /> accepts Visa, American Express, Diners and Mastercard. Please note that transaction limits may apply. Please record your receipt number and the date of your payment.</span></p>
								</div>
			
								<div style="left: 0; top: 44.5mm;">
									<img src="images/direct_debit.png" style="width: 9mm; height: 10.288mm; top: -5.5mm; left: 2mm;" />
									<p style="left: 17mm; font-size: 7t;"><span>DIRECT DEBIT</span></p>
									<p style="left: 17mm;"><span>To apply please call our customer service team on <xsl:value-of select="$customer-service-team-phone" /></span></p>
								</div>
								
								<div style="left: 0; top: 51.5mm;">
									<img src="images/mail.png" style="width: 13mm; height: 6.5mm; top: 1.1mm; left: 0.25mm;" />
									<p style="left: 17mm; font-size: 7pt;"><span>MAIL</span></p>
									<p style="left: 17mm;"><span>Detach the payment slip from the bottom of your account and return it together with your cheque or credit card details. Cheques should be made payable to "<xsl:value-of select="$business-payable-name" />" and mailed to: <xsl:value-of select="$business-payable-name" />, <xsl:value-of select="$business-payable-address" /></span></p>
								</div>
								
								<div style="left: 0; top: 63mm;">
									<img src="images/bill_express.png" style="width: 13.46mm; height: 5.251mm; top: 1.1mm; left: 0mm;" />
									<p style="left: 17mm; font-size: 7pt;"><span>BILL EXPRESS</span></p>
									<p style="left: 17mm;"><span>Look for the red BillEXPRESS logo at newsagents to pay this account with cash, cheque or debit card. For locations call 1300 739 250 or visit www.billexpress.com.au</span></p>
								</div>
								
								<div style="top: 71.2mm; left: 17mm; height: 10.5mm; text-align: right;">
								
								
									<xsl:element name="barcode">
										<xsl:attribute name="type">code128</xsl:attribute>
										<xsl:attribute name="value"><xsl:value-of select="$bill_express_barcode_value" /></xsl:attribute>
										<xsl:attribute name="style">height: 8mm; width: 54.7mm;</xsl:attribute>
									</xsl:element>
								
									<p style="top: 8.6mm; left: 0"><span>Biller ID: <xsl:value-of select="$bill_express_biller_id" /></span></p>
									<p style="top: 8.6mm;"><span>Ref:<xsl:text> </xsl:text><xsl:value-of select="/Document/Payment/BillExpress/CustomerReference" /></span></p>
								</div>
			
							</div>
							
							<!-- Right side -->
							<div style="width: 75mm; left: 115mm; font-size: 10pt; top: 52.51pt;">
			
								<!-- Top half -->
								<div style="width: 65mm; left: 0mm; top: 0mm;">
									<!-- Labels -->
									<div style="top: 0mm; left: 0; width: 31mm;">
										<p style="top: 0mm;"><span>Account No.</span></p>
										<p style="top: 8mm;"><span>Due Date</span></p>
										<p style="top: 16mm;"><span style="font-weight: bold;">Total Amount Due</span></p>
									</div>
									<!-- Details -->
									<div style="top: 0mm; left: 32mm; width: 33mm; text-align: right;">
										<p style="top: 0mm;"><span><xsl:value-of select="$account-number" /></span></p>
										<p style="top: 8mm;"><span><xsl:value-of select="/Document/Statement/DueDate" /></span></p>
										<p style="top: 16mm;; font-weight: bold;">
											<span>
												<xsl:value-of select="/Document/Currency/Symbol" />
												<xsl:call-template name="abs">
													<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/TotalOwing" /></xsl:with-param>
												</xsl:call-template>
												<xsl:call-template name="cr">
													<xsl:with-param name="amount"><xsl:value-of select="/Document/Statement/TotalOwing" /></xsl:with-param>
												</xsl:call-template>
											</span>
										</p>
									</div>
								</div>
			
								<!-- Bottom half -->
								<div style="width: 65mm; left: 0mm; top: 61.819pt; border-width-top: 0.5pt; border-color: #000; background-color: #fff;">
									<!-- Labels -->
									<div style="top: 8.535pt; left: 0; width: 31mm;">
										<p style="top: 0mm;"><span>Date</span></p>
										<p style="top: 8mm;"><span>Payment Method</span></p>
										<p style="top: 16mm;"><span style="font-weight: bold;">Total</span></p>
									</div>
									<!-- Details -->
									<div style="top: 8.535pt; left: 32mm;">
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
										<div style="border-width: 0.5pt; 
													border-color: #000; 
													background-color: #eeeff0; 
													corner-radius: 2pt;
													text-align: right; 
													top: 8mm;  
													height: 6.7pt; 
													width: 75.29pt; 
													padding-left: 5pt;
													padding-top: 1pt;
													padding-bottom: 9pt;
													padding-right: 17.88pt;">
										</div>
										<p style="top: 16mm; left: -14pt;">
											<span style="font-weight: bold;"><xsl:value-of select="/Document/Currency/Symbol" /></span>
										</p>
										<div style="border-width: 0.5pt; 
													border-color: #000; 
													background-color: #eeeff0; 
													corner-radius: 2pt;
													text-align: right; 
													top: 16mm;  
													height: 6.7pt; 
													width: 75.29pt; 
													padding-left: 5pt;
													padding-top: 1pt;
													padding-bottom: 9pt;
													padding-right: 17.88pt;">
											<p>
												<span style="font-weight: bold;">.</span>
											</p>
										</div>
									</div>
								</div>


							</div>

						</div>

					</page>

					<page type="pageTwo" stationery="/raw/header_even.raw">

						<xsl:call-template name="page-details-even" />

						<!-- Cut Here -->
						<raw src="raw/scissors_back.raw" />

						<!-- Payment Advice -->
						<div style="top: 595.6pt; left: 21.7pt;">
	
							<div style="top: 14pt; left: 171.2pt; text-align: left; font-size:8pt;">
								<p>
									<span>Please return this section with your payment details to:</span>
								</p>
								<p>
									<span><xsl:value-of select="$business-payable-name" />, <xsl:value-of select="$business-payable-address" /></span>
								</p>
							</div>
	
							<div style="top: 2pt; left: 0pt; font-size: 5pt;">
								<div style="height: 35.43pt; left: 14.8pt;">
									<xsl:call-template name="pay-slip-logo" />
								</div>
							</div>
	
							<div style="top: 52pt; left: 0pt; height: 172pt; width: 508.2pt; corner-radius: 16.3pt; border-width: 0.5pt; border-color: #000; background-color: #fff;">
	
								<!-- Left side -->
								<div style="width: 179.47pt; height: 158.77pt; left: 14.2pt; top: 6.75pt; font-size: 9pt; padding-right: 12pt; border-width-right: 0.5pt; border-color: #000; background-color: #fff;">
	
									<p style="text-align: center; width: 179.47; top: 0; font-weight: bold;"><span>CHEQUE DETAILS</span></p>
	
									<p style="bottom: 136.56;"><span>BSB:</span></p>
									<div style="bottom: 136.56; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 155.92pt;" />
									
									<p style="bottom: 120.81;"><span>Account No.:</span></p>
									<div style="bottom: 120.81; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 124.71pt;" />
									
									<p style="bottom: 105.06;"><span>Cheque No.:</span></p>
									<div style="bottom: 105.06; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 124.71pt;" />
									
									<p style="bottom:  89.28;"><span>Cheque Amount: <xsl:value-of select="/Document/Currency/Symbol" /></span></p>
									<div style="bottom: 89.28; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 101.25pt;" />
									
									<p style="width: 179.47; bottom: 64.4pt; font-weight: bold; font-style: italic;"><span>Moving Or Changing Customer Details?</span></p>
	
									<p style="bottom:  49.26;"><span>Correct Address:</span></p>
									<div style="bottom: 49.26; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 109.22pt;" />
									<div style="bottom: 34.21; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 179.47pt;" />
									<div style="bottom: 19.16; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 179.47pt;" />
									<div style="bottom:  4.11; left: 0; border-width-bottom: 0.5pt; border-color: #000; width: 82pt;" />
	
									<p style="bottom:   4.11; left: 90.58;"><span>Postcode</span></p>
									<div style="bottom: 4.11; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 46.92pt;" />
	
	
								</div>
	
								<!-- Right side -->
								<div style="width: 280pt; height: 158.77pt; left: 215.6pt; top: 6.75pt; font-size: 9pt;">
	
									<p style="text-align: center; width: 280pt; top: 0; font-weight: bold;"><span>CREDIT CARD DETAILS</span></p>
	
									<p style="bottom: 132.91pt;"><span>Please return this section with your payment:</span></p>
	
									<p style="bottom: 117.98pt; font-weight: bold;"><span>Payment by card:</span></p>
	
									<div style="bottom: 100.74; left: 0;">
										<p style="bottom: 0;"><span>Visa</span></p>
										<div style="right: -13.83; bottom: 0; background-color: #fff; border-color: #000; border-width: 0.25pt; padding: 2.85; width: 7.96; height: 7.96;"></div>
									</div>
	
									<div style="bottom: 100.74; left: 45.96;">
										<p style="bottom: 0;"><span>MasterCard</span></p>
										<div style="right: -13.83; bottom: 0; background-color: #fff; border-color: #000; border-width: 0.25pt; padding: 2.85; width: 7.96; height: 7.96;"></div>
									</div>
	
									<div style="bottom: 100.74; left: 120.77;">
										<p style="bottom: 0;"><span>Amex</span></p>
										<div style="right: -13.83; bottom: 0; background-color: #fff; border-color: #000; border-width: 0.25pt; padding: 2.85; width: 7.96; height: 7.96;"></div>
									</div>
	
									<div style="bottom: 100.74; left: 174.66;">
										<p style="bottom: 0;"><span>Diners Club</span></p>
										<div style="right: -13.83; bottom: 0; background-color: #fff; border-color: #000; border-width: 0.25pt; padding: 2.85; width: 7.96; height: 7.96;"></div>
									</div>
	
									<p style="bottom: 82.73;"><span>Cardholders Name:</span></p>
									<div style="bottom: 82.73; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 195.66pt;" />
									<p style="bottom: 74.52; left: 95.75; font-style: italic; font-size: 5.64pt;"><span>(Please print)</span></p>
	
	
									<p style="bottom: 59.37;"><span>Card Number:</span></p>
	
									<div style="bottom: 59.37; left: 58.31pt;">
										<div style="bottom: 0; left: 0pt;">
											<div style="left:  0.00pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 12.44pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 24.88pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 37.32pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										</div>
										<div style="bottom: 0; left: 54.84pt;">
											<div style="left:  0.00pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 12.44pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 24.88pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 37.32pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										</div>
										<div style="bottom: 0; left: 109.68pt;">
											<div style="left:  0.00pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 12.44pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 24.88pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 37.32pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										</div>
										<div style="bottom: 0; left: 164.52pt;">
											<div style="left:  0.00pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 12.44pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 24.88pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
											<div style="left: 37.32pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										</div>
									</div>
	
									<p style="bottom: 40.62;"><span>Expiry Date:</span></p>
									<div style="bottom: 40.62; left: 50.68pt;">
										<div style="left:  0.00pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										<div style="left: 12.44pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										<div style="left: 29.96pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										<div style="left: 42.40pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
									</div>
	
									<p style="bottom: 40.62; left: 122;"><span>CVV:</span></p>
									<div style="bottom: 40.62; left: 148.97pt;">
										<div style="left:  0.00pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										<div style="left: 12.44pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
										<div style="left: 24.88pt; bottom: 0; width: 10.25pt; border-width-bottom: 0.5pt; border-color: #000;" />
									</div>
	
	
									<p style="bottom: 22.2;"><span>Amount: <xsl:value-of select="/Document/Currency/Symbol" /></span></p>
									<div style="bottom: 22.2; right:169.24; border-width-bottom: 0.5pt; border-color: #000; width: 70.39pt;" />
	
									<p style="bottom: 3.6;"><span>Cardholders Signature:</span></p>
									<div style="bottom: 3.6; right:0; border-width-bottom: 0.5pt; border-color: #000; width: 185.51pt;" />
	
	
								</div>
	
							</div>
	
						</div>
	
	
	
						<div where="page_2_section_1" style="top: 74.84pt; left: 29.27pt; width: 524.66pt; height: 510.24pt;">
						
							<page-wrap-include content="document-body" />
						
						</div>
		
					</page>
		
					<page type="pageOdd" stationery="/raw/header_odd.raw">
			
						<xsl:call-template name="page-details-odd" />
						
						<!-- Logo and label -->
						<xsl:call-template name="logo-and-label" />
			
						<div where="page_odd_section_1" style="top: 128.84pt; left: 70.85pt; width: 524.66pt; height: 695pt;">
						
							<page-wrap-include content="document-body" />
						
						</div>

					</page>
		
					<page type="pageEven" stationery="/raw/header_even.raw">
			
						<xsl:call-template name="page-details-even" />
			
						<div where="page_even_section_1" style="top: 74.84pt; left: 29.27pt; width: 524.66pt; height: 745pt;">
	
							<page-wrap-include content="document-body" />
	
						</div>
	
					</page>
	
				</pages>
	
				<page-wrap-contents>
	
					<page-wrap-content identifier="document-body">
						<wrapped-content>


						</wrapped-content>
					</page-wrap-content>
	
				</page-wrap-contents>		
	
			</body>
	
		</html>
	
	</xsl:template>
	
	
	
	
	
	
	<xsl:template name="abs">
		<xsl:param name="amount" />
		<xsl:choose>
			<xsl:when test="starts-with($amount, '-')">
				<xsl:value-of select="substring($amount, 2)" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$amount" />
			</xsl:otherwise>
		</xsl:choose>
		<xsl:variable name="numeric" />
	</xsl:template>
	
	<xsl:template name="cr">
		<xsl:param name="amount" />
		<xsl:if test="starts-with($amount, '-')">
			<xsl:text> </xsl:text><xsl:value-of select="$cr-suffix" />
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="contact-details">
		<div style="font-family: Arial_Narrow; bottom: 771.5pt; left: 442.81pt; width: 140pt; font-size: 9pt; line-height: 11.5pt">
			<!-- Labels -->
			<div style="top: 0; left: 0; width: 30pt;">
				<p><span>Phone:</span></p>
				<p><span>Fax:</span></p>
				<p><span>Web:</span></p>
				<p><span>Email:</span></p>
			</div>
			<!-- Details -->
			<div style="top: 0; left: 30.9pt;">
				<p><span><xsl:value-of select="$business-phone" /></span></p>
				<p><span><xsl:value-of select="$business-fax" /></span></p>
				<p><span><xsl:value-of select="$business-web" /></span></p>
				<p><span><xsl:value-of select="$business-email" /></span></p>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template name="page-details-odd">
		<div style="font-family: Arial_Narrow; bottom: 799.65pt; right: 28.07; width: 126.73pt; font-size: 7pt; line-height: 11.35pt">
			<xsl:call-template name="page-details" />
		</div>
	</xsl:template>
	
	<xsl:template name="page-details-even">
		<div style="font-family: Arial_Narrow; bottom: 799.65pt; left: 397.27pt; width: 126.73pt; font-size: 7pt; line-height: 11.35pt">
			<xsl:call-template name="page-details" />
		</div>
	</xsl:template>
	
	<xsl:template name="page-details">
		<!-- Labels -->
		<div style="top: 0; left: 0; width: 50pt;">
			<p><span>Date Issued</span></p>
			<p><span>Account No.</span></p>
			<p><span>Page No.</span></p>
		</div>
		<!-- Details -->
		<div style="top: 0; right: 0pt; text-align: right;">
			<p><span><xsl:value-of select="/Document/DateIssued" /><xsl:text> - </xsl:text><xsl:value-of select="/Document/DateIssued" /></span></p>
			<p><span><xsl:value-of select="$account-number" /></span></p>
			<p><span><page-nr /><xsl:text> of </xsl:text><page-count /></span></p>
		</div>
	</xsl:template>

</xsl:stylesheet>