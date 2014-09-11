<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:template name="Content">
	
		<!-- Heading 1 -->
		<h1>View CDR</h1>
		
		<div class="FormPopup">
			<div class="Form-Content">
				<table border="0" width="100%" cellpadding="3" cellspacing="0">
					<!-- FNN -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Service')" />
								<xsl:with-param name="field" select="string('FNN')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/FNN" /></td>
					</tr>
					
					<!-- Id -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Id" /></td>
					</tr>
					
					<!-- File -->
					<!-- TODO!bash! [  DONE  ]		make this display FileImport.FileName -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('File')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/FileImport/FileName" /></td>
					</tr>
					
					<!-- Carrier -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Carrier')" />
								<xsl:with-param name="field" select="string('CarrierName')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/Carriers/Carrier[./Id=/Response/CDR/Carrier]/Name" /></td>
					</tr>
					
					<!-- CarrierRef -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('CarrierRef')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/CarrierRef" /></td>
					</tr>
					
					<!-- Source -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Source')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/CDR/Source = ''">
									<strong><span class="Red">Undefined</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDR/Source" />
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					
					<!-- Destination -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Destination')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/CDR/Destination = ''">
									<strong><span class="Red">Undefined</span></strong>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CDR/Destination" />
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					
					<!-- StartDatetime -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('StartDatetime')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"		select="/Response/CDR/StartDatetime/year" />
								<xsl:with-param name="month"	select="/Response/CDR/StartDatetime/month" />
								<xsl:with-param name="day"		select="/Response/CDR/StartDatetime/day" />
		 						<xsl:with-param name="hour"		select="/Response/CDR/StartDatetime/hour" />
								<xsl:with-param name="minute"	select="/Response/CDR/StartDatetime/minute" />
								<xsl:with-param name="second"	select="/Response/CDR/StartDatetime/second" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%M:%S %P'"/>
							</xsl:call-template>
						</td>
					</tr>
					
					<!-- EndDatetime -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('EndDatetime')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:call-template name="dt:format-date-time">
								<xsl:with-param name="year"		select="/Response/CDR/EndDatetime/year" />
								<xsl:with-param name="month"	select="/Response/CDR/EndDatetime/month" />
								<xsl:with-param name="day"		select="/Response/CDR/EndDatetime/day" />
		 						<xsl:with-param name="hour"		select="/Response/CDR/EndDatetime/hour" />
								<xsl:with-param name="minute"	select="/Response/CDR/EndDatetime/minute" />
								<xsl:with-param name="second"	select="/Response/CDR/EndDatetime/second" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
							</xsl:call-template>
						</td>
					</tr>
					
					<!-- Cost -->
					<!-- TODO!bash! [  DONE  ]		only display if god -->
					<xsl:if test="/Response/Authentication/AuthenticatedEmployee/Privileges = '9223372036854775807'">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('CDR')" />
									<xsl:with-param name="field" select="string('Cost')" />
								</xsl:call-template>
							</th>
							<td>
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="/Response/CDR/Cost" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
							</td>
						</tr>
					</xsl:if>
					
					<!-- Status -->
					<!-- TODO!bash! [  DONE  ] make this show Status name ... ask flame how -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Status')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR-Status/Name" /></td>
					</tr>
					
					<!-- Description -->
					<!-- TODO!bash! [  DONE  ]		don't display if blank -->
					<xsl:if test="/Response/CDR/Description != ''">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('CDR')" />
									<xsl:with-param name="field" select="string('Description')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/CDR/Description" /></td>
						</tr>
					</xsl:if>
					
					<!-- DestinationCode -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('DestinationCode')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/DestinationCode" /></td>
					</tr>
					
					<!-- RecordType -->
					<!-- TODO!bash! [  DONE  ]		make this display RecordType.Name -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('RecordType')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/RecordType/Name" />
						</td>
					</tr>
					
					<!-- Charge -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Charge')" />
							</xsl:call-template>
						</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/CDR/Charge" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					
					<!-- Rate -->
					<!-- TODO!bash! [  DONE  ]		make this display Rate.Name -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Rate')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Rate/Name" />
						</td>
					</tr>
					
					<!-- NormalisedOn -->
					<!-- TODO!bash! [  DONE  ]		make this display 'Not Normalised' if not normalised -->
					<!-- TODO!bash! [  DONE  ]		format the date time -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('NormalisedOn')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/CDR/NormalisedOn/year">
									<xsl:call-template name="dt:format-date-time">
										<xsl:with-param name="year"		select="/Response/CDR/NormalisedOn/year" />
										<xsl:with-param name="month"	select="/Response/CDR/NormalisedOn/month" />
										<xsl:with-param name="day"		select="/Response/CDR/NormalisedOn/day" />
				 						<xsl:with-param name="hour"		select="/Response/CDR/NormalisedOn/hour" />
										<xsl:with-param name="minute"	select="/Response/CDR/NormalisedOn/minute" />
										<xsl:with-param name="second"	select="/Response/CDR/NormalisedOn/second" />
										<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<span class="Attention">Not Normalised</span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					
					<!-- RatedOn -->
					<!-- TODO!bash! [  DONE  ]		make this display 'Not Normalised' if not normalised -->
					<!-- TODO!bash! [  DONE  ]		format the date time -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('RatedOn')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/CDR/NormalisedOn/year">
									<xsl:call-template name="dt:format-date-time">
										<xsl:with-param name="year"		select="/Response/CDR/RatedOn/year" />
										<xsl:with-param name="month"	select="/Response/CDR/RatedOn/month" />
										<xsl:with-param name="day"		select="/Response/CDR/RatedOn/day" />
				 						<xsl:with-param name="hour"		select="/Response/CDR/RatedOn/hour" />
										<xsl:with-param name="minute"	select="/Response/CDR/RatedOn/minute" />
										<xsl:with-param name="second"	select="/Response/CDR/RatedOn/second" />
										<xsl:with-param name="format"	select="'%A, %b %d, %Y %I:%M:%S %P'"/>
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<span class="Attention">Not Rated</span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					
					<!-- InvoiceRun -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('InvoiceRun')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/invoice_run_id" /></td>
					</tr>
					
					<!-- SequenceNo -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('SequenceNo')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/SequenceNo" /></td>
					</tr>
					
					<!-- Credit -->
					<!-- TODO!bash! [  DONE  ]	make this display 'Charge' or 'Credit' -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Credit')" />
							</xsl:call-template>
						</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/CDR/Credit = '1'">
										<span class="Blue">Credit</span>
									</xsl:when>
									<xsl:otherwise>
										<span class="Green">Charge</span>
									</xsl:otherwise>
								</xsl:choose>
							</strong>
						</td>
					</tr>

					<!-- CDR -->
					<!-- TODO!flame! display raw CDR
							use functions from normalisation modules to split the cdr
							display key=>value from array into a text box
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('CDR')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/CDR" /></td>
					</tr>
					-->

				</table>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
