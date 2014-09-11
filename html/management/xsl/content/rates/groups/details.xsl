<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Rate Group Details</h1>
		
		<h2 class="Plan"> Group Details</h2>
		
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th>Rate Group Name:</th>
						<td><xsl:value-of select="/Response/RateGroup/Name" /></td>
					</tr>
					<tr>
						<th>Rate Group Description:</th>
						<td><xsl:value-of select="/Response/RateGroup/Description" /></td>
					</tr>
					<tr>
						<th>Service Type:</th>
						<td><xsl:value-of select="/Response/RateGroup/ServiceTypes/ServiceType[@selected='selected']/Name" /></td>
					</tr>
					<tr>
						<th>Record Type:</th>
						<td><xsl:value-of select="/Response/RateGroup/RecordType/Name" /></td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
		
		<div class="Seperator"></div>
		
		<h2 class="Invoice">Charges</h2>
		
		<!--TODO!bash! [  DONE  ]		URGENT! This needs to be the same table as on the view rate plan details page!-->
		<table border="0" width="100%" cellpadding="5" cellspacing="0" style="font-family: monospace; font-size: 9pt;" class="Listing">
			<tr class="First">
				<th colspan="3">
					<xsl:value-of select="/Response/RateGroup/RecordType/Name" /> ( <xsl:value-of select="/Response/RateGroup/Name" /> )
				</th>
			</tr>
			<xsl:for-each select="/Response/RateGroupRate/rangeSample/Rate">
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
					
					<td>
						<!-- TODO!bash! [  DONE  ]		link this to view rate details -->
						<a href="#" title="Rate Details" alt="Information about this Rate and its Charges">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, </xsl:text>
								<xsl:text>'rates_rate_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>')</xsl:text>
							</xsl:attribute>
							<xsl:value-of select="./Description" />	
						</a>
					</td>
					<td>
						<table border="0" cellpadding="3" cellspacing="0">
							<tr>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Monday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									Mo</span></strong>
								</td>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Tuesday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									Tu</span></strong>
								</td>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Wednesday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									We</span></strong>
								</td>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Thursday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									Th</span></strong>
								</td>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Friday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									Fr</span></strong>
								</td>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Saturday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									Sa</span></strong>
								</td>
								<td>
									<strong><span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="./Sunday = 1">Green</xsl:when>
											<xsl:otherwise>Red</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
									Su</span></strong>
								</td>
							</tr>
						</table>
					</td>
					<td>
						<xsl:call-template name="dt:format-date-time">
	 						<xsl:with-param name="hour"		select="./StartTime/hour" />
							<xsl:with-param name="minute"	select="./StartTime/minute" />
							<xsl:with-param name="second"	select="./StartTime/second" />
							<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
						</xsl:call-template>
						to
						<xsl:call-template name="dt:format-date-time">
	 						<xsl:with-param name="hour"		select="./EndTime/hour" />
							<xsl:with-param name="minute"	select="./EndTime/minute" />
							<xsl:with-param name="second"	select="./EndTime/second" />
							<xsl:with-param name="format"	select="'%I:%M:%S %P'"/>
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:if test="/Response/RateGroupRate/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/RateGroupRate/rangePage != 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>rates_group_details.php</xsl:text>
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/RateGroup/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangeLength" />
											
											<xsl:text>&amp;rangePage=1</xsl:text>
										</xsl:attribute>
										<xsl:text>&#124;&lt;- First</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- &#124;&lt;- First -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/RateGroupRate/rangePage &gt; 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>rates_group_details.php</xsl:text>
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/RateGroup/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangePage - 1" />
										</xsl:attribute>
										<xsl:text>&lt;- Prev</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- &lt;- Prev -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="60%" align="center">
							Page <xsl:value-of select="/Response/RateGroupRate/rangePage" />
							of <xsl:value-of select="/Response/RateGroupRate/rangePages" /><br />
							Showing  
							<xsl:value-of select="/Response/RateGroupRate/rangeStart + 1" />
							to
							<xsl:choose>
								<xsl:when test="/Response/RateGroupRate/rangeLength + /Response/RateGroupRate/rangeStart &gt; /Response/RateGroupRate/collationLength">
									<xsl:value-of select="/Response/RateGroupRate/collationLength" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/RateGroupRate/rangeStart + /Response/RateGroupRate/rangeLength" />
								</xsl:otherwise>
							</xsl:choose>
							of
							<xsl:value-of select="/Response/RateGroupRate/collationLength" />
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/RateGroupRate/rangePage &lt; /Response/RateGroupRate/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>rates_group_details.php</xsl:text>
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/RateGroup/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangePage + 1" />
										</xsl:attribute>
										<xsl:text>Next -&gt;</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- Next -&gt; -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/RateGroupRate/rangePage &lt; /Response/RateGroupRate/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>rates_group_details.php</xsl:text>
											<xsl:text>?Id=</xsl:text>
											<xsl:value-of select="/Response/RateGroup/Id" />
											
											<xsl:text>&amp;rangeLength=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/RateGroupRate/rangePages" />
										</xsl:attribute>
										<xsl:text>Last -&gt;&#124;</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- Last -&gt;&#124; -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</table>
			</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
