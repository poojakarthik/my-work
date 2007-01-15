<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Download Payment Summary</h1>
		<div class="Seperator"></div>
		
		<h2 class="Invoice">Payment Information</h2>
		<form method="post" action="payment_download.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaymentType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="PaymentType">
									<xsl:for-each select="/Response/PaymentTypes/PaymentType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaidOn')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="3" cellspacing="0">
									<tr>
										<td>
											<input type="radio" name="PaidOn" value="TODAY" id="PaidOn:TODAY" checked="checked" />
										</td>
										<th>
											<label for="PaidOn:TODAY">
												Entered Today
											</label>
										</th>
									</tr>
									<tr>
										<td>
											<input type="radio" name="PaidOn" value="YESTERDAY" id="PaidOn:YESTERDAY" />
										</td>
										<th>
											<label for="PaidOn:YESTERDAY">
												Entered Yesterday
											</label>
										</th>
									</tr>
									<tr>
										<td>
											<input type="radio" name="PaidOn" value="CUSTOM" id="PaidOn:CUSTOM" />
										</td>
										<th>
											<label for="PaidOn:CUSTOM">
												Entered on this Date:
											</label>
										</th>
									</tr>
									<tr>
										<td></td>
										<td>
											<select name="PaidOn:CUSTOM:year">
												<option value="2007">2007</option>
											</select> - 
											<select name="PaidOn:CUSTOM:month">
												<option value="01">01 (JAN)</option>
												<option value="02">02 (FEB)</option>
												<option value="03">03 (MAR)</option>
												<option value="04">04 (APR)</option>
												<option value="05">05 (MAY)</option>
												<option value="06">06 (JUN)</option>
												<option value="07">07 (JUL)</option>
												<option value="08">08 (AUG)</option>
												<option value="09">09 (SEP)</option>
												<option value="10">10 (OCT)</option>
												<option value="11">11 (NOV)</option>
												<option value="12">12 (DEC)</option>
											</select> - 
											<select name="PaidOn:CUSTOM:day">
												<option value="01">01</option>
												<option value="02">02</option>
												<option value="03">03</option>
												<option value="04">04</option>
												<option value="05">05</option>
												<option value="06">06</option>
												<option value="07">07</option>
												<option value="08">08</option>
												<option value="09">09</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
												<option value="13">13</option>
												<option value="14">14</option>
												<option value="15">15</option>
												<option value="16">16</option>
												<option value="17">17</option>
												<option value="18">18</option>
												<option value="19">19</option>
												<option value="20">20</option>
												<option value="21">21</option>
												<option value="22">22</option>
												<option value="23">23</option>
												<option value="24">24</option>
												<option value="25">25</option>
												<option value="26">26</option>
												<option value="27">27</option>
												<option value="28">28</option>
												<option value="29">29</option>
												<option value="30">30</option>
												<option value="31">31</option>
											</select>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Payment &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>
