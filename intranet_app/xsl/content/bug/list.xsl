<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Bug List</h1>
		
		<h2 class="Bug"> Bug Details</h2>
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Created On</th>
				<th>Created By</th>
				<th>Assigned To</th>
				<th>Status</th>
				<th>Page Name</th>
			</tr>
			<xsl:for-each select="/Response/Bugs/Record">
				<xsl:sort select="./CreatedOn" />
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
					
					<td><xsl:value-of select="position()" />.</td>
					<td><xsl:value-of select="./CreatedOn" /></td>
					<td><xsl:value-of select="./CreatedBy" /></td>
					<td><xsl:value-of select="./AssignedTo" /></td>
					<td><xsl:value-of select="./Status" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>bug_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./PageName" />
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/Bugs/collationLength = 0">
				<div class="MsgErrorWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/Bugs/Record) = 0">
				<div class="MsgNoticeWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
		</xsl:choose>
		<br></br>
		<h2 class="Bug"> Search Bugs</h2>
		<form method="POST" action="bug_list.php">
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('CreatedBetween')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="DOB">
									<xsl:with-param name="Name-Day"			select="string('CreatedOnStartDay')" />
									<xsl:with-param name="Name-Month"		select="string('CreatedOnStartMonth')" />
									<xsl:with-param name="Name-Year"		select="string('CreatedOnStartYear')" />
								</xsl:call-template>
								and
								<xsl:call-template name="DOB">
									<xsl:with-param name="Name-Day"			select="string('CreatedOnEndDay')" />
									<xsl:with-param name="Name-Month"		select="string('CreatedOnEndMonth')" />
									<xsl:with-param name="Name-Year"		select="string('CreatedOnEndYear')" />
								</xsl:call-template>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('ClosedBetween')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="DOB">
									<xsl:with-param name="Name-Day"			select="string('ClosedOnStartDay')" />
									<xsl:with-param name="Name-Month"		select="string('ClosedOnStartMonth')" />
									<xsl:with-param name="Name-Year"		select="string('ClosedOnStartYear')" />
								</xsl:call-template>
								and
								<xsl:call-template name="DOB">
									<xsl:with-param name="Name-Day"			select="string('ClosedOnEndDay')" />
									<xsl:with-param name="Name-Month"		select="string('ClosedOnEndMonth')" />
									<xsl:with-param name="Name-Year"		select="string('ClosedOnEndYear')" />
								</xsl:call-template>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('CreatedBy')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="CreatedBy">
									<xsl:for-each select="/Response/CreatedByEmployees/Record">
										<option>
											<xsl:attribute name="value"> 
												<xsl:value-of select="./CreatedById" />
											</xsl:attribute>
											<xsl:value-of select="./CreatedByName" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('AssignedTo')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="AssignedTo">
									<xsl:for-each select="/Response/AssignedToEmployees/Record">
										<option>
											<xsl:attribute name="value"> 
												<xsl:value-of select="./AssignedToId" />
											</xsl:attribute>
											<xsl:value-of select="./AssignedToName" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('Status')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Status">
									<xsl:for-each select="/Response/Statuses/Record">
										<option>
											<xsl:attribute name="value"> 
												<xsl:value-of select="./StatusId" />
											</xsl:attribute>
											<xsl:value-of select="./Status" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('PageName')" />
								</xsl:call-template>
							</th>
							<td>
								<input name="PageName" style="width: 400px;"></input>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Bugs')" />
									<xsl:with-param name="field" select="string('Keyword')" />
								</xsl:call-template>
							</th>
							<td>
								<input name="Search" style="width: 400px;"></input>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class = "Right">
				<input type="submit" class="input-submit" value="Search &#0187;" />
			</div>
		</form>

	</xsl:template>
</xsl:stylesheet>
