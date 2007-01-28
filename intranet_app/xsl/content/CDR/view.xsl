<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	
	<xsl:template name="Content">
		<h1>View CDR</h1>
		
		<h2 class="CDR">CDR Details</h2>
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
					<!-- TODO!bash! make this display FileImport.FileName -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('File')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/File" /></td>
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
								<xsl:with-param name="year"	select="/Response/CDR/StartDatetime/year" />
								<xsl:with-param name="month"	select="/Response/CDR/StartDatetime/month" />
								<xsl:with-param name="day"		select="/Response/CDR/StartDatetime/day" />
		 						<xsl:with-param name="hour"	select="/Response/CDR/StartDatetime/hour" />
								<xsl:with-param name="minute"	select="/Response/CDR/StartDatetime/minute" />
								<xsl:with-param name="second"	select="/Response/CDR/StartDatetime/second" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
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
								<xsl:with-param name="year"	select="/Response/CDR/EndDatetime/year" />
								<xsl:with-param name="month"	select="/Response/CDR/EndDatetime/month" />
								<xsl:with-param name="day"		select="/Response/CDR/EndDatetime/day" />
		 						<xsl:with-param name="hour"	select="/Response/CDR/EndDatetime/hour" />
								<xsl:with-param name="minute"	select="/Response/CDR/EndDatetime/minute" />
								<xsl:with-param name="second"	select="/Response/CDR/EndDatetime/second" />
								<xsl:with-param name="format"	select="'%A, %b %d, %Y %H:%I:%S %P'"/>
							</xsl:call-template>
						</td>
					</tr>
					
					<!-- Units -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('File')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/File" /></td>
					</tr>
					
					<!-- Cost -->
					<!-- TODO!bash! only display if god -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Cost')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Cost" /></td>
					</tr>
					
					<!-- Status -->
					<!-- TODO!bash! make this show Status name ... ask flame how -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Status')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Status" /></td>
					</tr>
					
					<!-- Description -->
					<!-- TODO!bash! don't display if blank -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Description')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Description" /></td>
					</tr>
					
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
					<!-- TODO!bash! make this display RecordType.Name -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('RecordType')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/RecordType" /></td>
					</tr>
					
					<!-- Charge -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Charge')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Charge" /></td>
					</tr>
					
					<!-- Rate -->
					<!-- TODO!bash! make this display Rate.Name -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Rate')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Rate" /></td>
					</tr>
					
					<!-- NormalisedOn -->
					<!-- TODO!bash! make this display 'Not Normalised' if not normalised -->
					<!-- TODO!bash! format the date time -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('NormalisedOn')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/NormalisedOn" /></td>
					</tr>
					
					<!-- RatedOn -->
					<!-- TODO!bash! make this display 'Not Normalised' if not normalised -->
					<!-- TODO!bash! format the date time -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('RatedOn')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/RatedOn" /></td>
					</tr>
					
					<!-- InvoiceRun -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('InvoiceRun')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/InvoiceRun" /></td>
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
					<!-- TODO!bash! make this display 'Charge' or 'Credit' -->
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('CDR')" />
								<xsl:with-param name="field" select="string('Credit')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/CDR/Credit" /></td>
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
