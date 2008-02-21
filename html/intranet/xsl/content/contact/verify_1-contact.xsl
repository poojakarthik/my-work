<?xml version="1.0" encoding="utf-8"?>
<!-- TODO!bash! [  DONE  ]		Don't show this page if no contact is found. show an error on the first page like you do if a business name search returns no results -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Find Customer</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'Unselected'">
						Please select a Contact.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
					
		<h2 class="Contact">Select a Contact</h2>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<form method="post" action="contact_verify.php">
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
					
					<div class="Wide-Form">
						<div class="Form-Content">
							<table border="0" cellpadding="3" cellspacing="0">
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
					

					
					<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
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
								<xsl:variable name="Contact" select="." />
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
									<td><xsl:value-of select="/Response/ui-answers/TitleTypes/TitleType[./Id = $Contact/Title]/Name" /></td>
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
							<div class="MsgErrorWide">
								There are too many results to display.  Please refine your search and try again.
							</div>
						</xsl:when>
						<xsl:when test="/Response/ui-answers/Contacts/Results/collationLength = 0">
							<div class="MsgErrorWide">
								There were no results matching your search. Please change your search and try again.
							</div>
						</xsl:when>
						<xsl:when test="count(/Response/ui-answers/Contacts/Results/rangeSample/Contact) = 0">
							<div class="MsgNoticeWide">
								There were no results matching your search. Please change your search and try again.
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div class="SmallSeperator"></div>
								<div class="Right">
									<input type="submit" name="ContinueContact" value="Continue &#0187;" class="input-submit" />
								</div>
						</xsl:otherwise>
					</xsl:choose>
				</form>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
