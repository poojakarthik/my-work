<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Find Customer</h1>
		
		<h2 class="Contact">Select a Contact</h2>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<form method="post" action="contact_list.php">
					<input type="hidden" name="ui-Contact-First">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/ui-values/Contact-First" />
						</xsl:attribute>
					</input>
					<input type="hidden" name="ui-Contact-Last">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/ui-values/Contact-Last" />
						</xsl:attribute>
					</input>
					
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('FirstName')" />
										</xsl:call-template>
									</th>
									<td><xsl:value-of select="/Response/ui-values/Contact-First" /></td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Contact')" />
											<xsl:with-param name="field" select="string('LastName')" />
										</xsl:call-template>
									</th>
									<td><xsl:value-of select="/Response/ui-values/Contact-Last" /></td>
								</tr>
							</table>
						</div>
					</div>
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
						<tr class="First">
							<th width="30">#</th>
							<th></th>
							<th>Title</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Job Title</th>
							<th>Business Name</th>
							<th>Suburb / Postcode</th>
						</tr>
						<xsl:if test="/Response/ui-answers/Contacts/Results/collationLength &lt;= 15">
							<xsl:for-each select="/Response/ui-answers/Contacts/Results/rangeSample/Contact">
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
									
									<td><xsl:value-of select="/Response/ui-answers/Contacts/Results/rangeStart + position()" />.</td>
									<td width="30">
										<input type="radio" name="ui-Contact-Sel">
											<xsl:attribute name="value">
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											
										</input>
									</td>
									<td><xsl:value-of select="./Title" /></td>
									<td><xsl:value-of select="./FirstName" /></td>
									<td><xsl:value-of select="./LastName" /></td>
									<td><xsl:value-of select="./JobTitle" /></td>
									<td><xsl:value-of select="./PrimaryAccount/Account/BusinessName" /></td>
									<td>
										<xsl:value-of select="./PrimaryAccount/Account/Suburb" />,
										<xsl:value-of select="./PrimaryAccount/Account/Postcode" />
									</td>
								</tr>
							</xsl:for-each>
						</xsl:if>
					</table>
					
					<xsl:choose>
						<xsl:when test="/Response/ui-answers/Contacts/Results/collationLength &gt; 15">
							<div class="MsgError">
								There are too many results to display. Please refine your search and try again.
							</div>
						</xsl:when>
						<xsl:when test="/Response/ui-answers/Contacts/Results/collationLength = 0">
							<div class="MsgError">
								There are no Contacts with the Search Criteria that you Specified.
							</div>
						</xsl:when>
						<xsl:when test="count(/Response/ui-answers/Contacts/Results/rangeSample/Contact) = 0">
							<div class="MsgNotice">
								There are no Records for the Range that you Searched for.
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div class="Seperator"></div>
							<input type="submit" value="Continue Verification &#0187;" class="input-submit" />
						</xsl:otherwise>
					</xsl:choose>
				</form>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
