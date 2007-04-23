<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>View Bug</h1>
		
		<h2 class="Bug">Bug Details</h2>

					<div class="Wide-Form">
						<div class="Form-Content">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Bugs')" />
											<xsl:with-param name="field" select="string('CreatedBy')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Bug/CreatedBy" />

									</td>
								</tr>
																		
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Bugs')" />
											<xsl:with-param name="field" select="string('CreatedOn')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Bug/CreatedOn" />
										
									</td>
								</tr>
								
								<tr>
									<td colspan="2">
										<div class="MicroSeperator"></div>
									</td>
								</tr>
								<tr>
									<th class="JustifiedWidth">
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Bugs')" />
											<xsl:with-param name="field" select="string('AssignedTo')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:choose>
											<xsl:when test="/Response/Bug/AssignedTo != ''">
												<xsl:value-of select="/Response/Bug/AssignedTo" />
											</xsl:when>
											<xsl:otherwise>
												<strong><span class="Attention">Not yet assigned</span></strong>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Bugs')" />
											<xsl:with-param name="field" select="string('Status')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Bug/Status" />
									</td>
								</tr>								
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Bugs')" />
											<xsl:with-param name="field" select="string('PageName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Bug/PageName" />
									</td>
								</tr>
								<!--Check for Resolution -->
								<xsl:if test="/Response/Bug/Resolution != ''">
									<tr>
										<th valign='top'>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Bugs')" />
												<xsl:with-param name="field" select="string('Resolution')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Bug/Resolution" />
										</td>
									</tr>
								</xsl:if>
								<!-- Check if bug is closed -->
								<!-- Check is weird as null comes up as 000000000000... only if using original bug.php-->
								<xsl:if test="/Response/Bug/ClosedOn != ''">
									<tr>
										<th>
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Bugs')" />
												<xsl:with-param name="field" select="string('ClosedOn')" />
											</xsl:call-template>
										</th>
										<td>
											<xsl:value-of select="/Response/Bug/ClosedOn" />
										</td>
									</tr>
								</xsl:if>
							</table>
						</div>
					</div>
		<br />					
		<h2 class="Note"> Comments</h2>
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr>
				<xsl:attribute name="class">
					<xsl:text>Even</xsl:text>
				</xsl:attribute>	
				<th width="200" valign='top'>
						<xsl:value-of select="Response/Bug/CreatedBy" />
					<br />
						<xsl:value-of select="Response/Bug/CreatedOn" />
				</th>
				<th><xsl:value-of select="Response/Bug/Comment" /></th>
			</tr>
			<xsl:for-each select="/Response/BugComments/Record">
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
					<td valign='top'>
							<xsl:value-of select="./CreatedBy" />
						<br />
							<xsl:value-of select="./CreatedOn" />
					</td>
					<td><xsl:value-of select="./Comment" /></td>
				</tr>
			</xsl:for-each>
		</table>
		<br />
		<form method="POST" action="bug_view.php">
			<div class="Wide-Form">
				<div class="Form-Content">
					<input type="hidden" name="Id">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/Bug/Id" />
						</xsl:attribute>
					</input>
					
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('AddComment')" />
								</xsl:call-template>
							</th>
							<td>
								<textarea name="Comment" style="width: 725px; height: 125px;" class="input-summary" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class = "Right">
				<input type="submit" class="input-submit" value="Add Comment &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>
