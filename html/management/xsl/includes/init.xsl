<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- Pablo Says (help) Links -->
	<xsl:template name="Label">
		<xsl:param name="entity" />
		<xsl:param name="field" />
		
		<xsl:variable name="Element" select="/Response/Documentation/Entity/Fields/Field[Entity=$entity][Field=$field]" />
		
		<a href="#" class="Label" alt="Pablo provides helpful online documentation">
			<xsl:attribute name="title">
				<xsl:text>Pablo 'the helpful donkey'</xsl:text>
			</xsl:attribute>
			<xsl:attribute name="onclick">
				<xsl:text>return ModalExternal (this, 'documentation_view.php?Entity=</xsl:text>
				<xsl:value-of select="$Element/Entity" />
				<xsl:text>&amp;Field=</xsl:text>
				<xsl:value-of select="$Element/Field" />
				<xsl:text>')</xsl:text>
			</xsl:attribute>
			<xsl:value-of select="$Element/Label" />
		</a> :
	</xsl:template>
	
	<xsl:template name="ConstraintOperator">
		<xsl:param name="Name" />
		<xsl:param name="Selected" />
		<xsl:param name="DataType" />
		
		<xsl:choose>
			<xsl:when test="$DataType = 'Id'">
				<input type="hidden" value="EQUALS">
					<xsl:attribute name="name">
						<xsl:text></xsl:text>
						<xsl:value-of select="$Name" />
					</xsl:attribute>
				</input>
			</xsl:when>
			<xsl:when test="$DataType = 'ABN'">
				<input type="hidden" value="EQUALS">
					<xsl:attribute name="name">
						<xsl:text></xsl:text>
						<xsl:value-of select="$Name" />
					</xsl:attribute>
				</input>
			</xsl:when>
			<xsl:when test="$DataType = 'ACN'">
				<input type="hidden" value="EQUALS">
					<xsl:attribute name="name">
						<xsl:text></xsl:text>
						<xsl:value-of select="$Name" />
					</xsl:attribute>
				</input>
			</xsl:when>
			<xsl:when test="$DataType = 'String'">
				<input type="hidden" value="LIKE">
					<xsl:attribute name="name">
						<xsl:text></xsl:text>
						<xsl:value-of select="$Name" />
					</xsl:attribute>
				</input>
				
				contains
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>Unknown Data Type</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="CreditCardExpiry">
		<xsl:param name="Name-Month" />
		<xsl:param name="Name-Year" />
		<xsl:param name="Selected-Month" />
		<xsl:param name="Selected-Year" />
		
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Month" />
			</xsl:attribute>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="12" />
				<xsl:with-param name="select" select="$Selected-Month" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Year" />
			</xsl:attribute>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="2008" />
				<xsl:with-param name="cease" select="2015" />
				<xsl:with-param name="select" select="$Selected-Year" />
			</xsl:call-template>
		</select>
	</xsl:template>
	
	<xsl:template name="DOB">
		<xsl:param name="Name-Day" />
		<xsl:param name="Name-Month" />
		<xsl:param name="Name-Year" />
		<xsl:param name="Selected-Day" />
		<xsl:param name="Selected-Month" />
		<xsl:param name="Selected-Year" />
		
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Day" />
			</xsl:attribute>
			
			<option value="">DD</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="31" />
				<xsl:with-param name="select" select="$Selected-Day" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Month" />
			</xsl:attribute>
			
			<option value="">MM</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="12" />
				<xsl:with-param name="select" select="$Selected-Month" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Year" />
			</xsl:attribute>
			
			<option value="">YYYY</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1910" />
				<xsl:with-param name="cease" select="1990" />
				<xsl:with-param name="select" select="$Selected-Year" />
			</xsl:call-template>
		</select>
	</xsl:template>
	
	<xsl:template name="NearFuture">
		<xsl:param name="Name-Day" />
		<xsl:param name="Name-Month" />
		<xsl:param name="Name-Year" />
		<xsl:param name="Selected-Day" />
		<xsl:param name="Selected-Month" />
		<xsl:param name="Selected-Year" />
		
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Day" />
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Day" />
			</xsl:attribute>
			
			<option value="">DD</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="31" />
				<xsl:with-param name="select" select="$Selected-Day" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Month" />
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Month" />
			</xsl:attribute>
			
			<option value="">MM</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="12" />
				<xsl:with-param name="select" select="$Selected-Month" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Year" />
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Year" />
			</xsl:attribute>
			<option value="">YYYY</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="2009" />
				<xsl:with-param name="cease" select="2012" />
				<xsl:with-param name="select" select="$Selected-Year" />
			</xsl:call-template>
		</select>
	</xsl:template>
	
	<xsl:template name="NearPast">
		<xsl:param name="Name-Day" />
		<xsl:param name="Name-Month" />
		<xsl:param name="Name-Year" />
		<xsl:param name="Selected-Day" />
		<xsl:param name="Selected-Month" />
		<xsl:param name="Selected-Year" />
		
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Day" />
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Day" />
			</xsl:attribute>
			<option value="">DD</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="31" />
				<xsl:with-param name="select" select="$Selected-Day" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Month" />
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Month" />
			</xsl:attribute>
			
			<option value="">MM</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="1" />
				<xsl:with-param name="cease" select="12" />
				<xsl:with-param name="select" select="$Selected-Month" />
			</xsl:call-template>
		</select> / 
		<select>
			<xsl:attribute name="name">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Year" />
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Name-Year" />
			</xsl:attribute>
			
			<option value="">YYYY</option>
			
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="2006" />
				<xsl:with-param name="cease" select="2009" />
				<xsl:with-param name="select" select="$Selected-Year" />
			</xsl:call-template>
		</select>
	</xsl:template>
	
	<xsl:template name="DateLoop">
		<xsl:param name="start">1</xsl:param>
		<xsl:param name="cease">0</xsl:param>
		<xsl:param name="steps">1</xsl:param>
		<xsl:param name="count">0</xsl:param>
		
		<xsl:param name="select">0</xsl:param>
		
		<xsl:if test="number($start) + number($count) &lt;= number($cease)">
			<option>
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="$start + $count" />
				</xsl:attribute>
				
				<xsl:choose>
					<xsl:when test="$select = $start + $count">
						<xsl:attribute name="selected">
							<xsl:text>selected</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:value-of select="$start + $count" />
			</option>
			<xsl:call-template name="DateLoop">
				<xsl:with-param name="start" select="$start" />
				<xsl:with-param name="cease" select="$cease" />
				<xsl:with-param name="steps" select="$steps" />
				<xsl:with-param name="count" select="$count + $steps" />
				<xsl:with-param name="select" select="$select" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="Currency">
		<xsl:param name="Number" />
		<xsl:param name="Decimal" select="number('4')" />
		
		<xsl:variable name="NumberCorrect">
			<xsl:choose>
				<xsl:when test="not($Number) or $Number = ''">
					<xsl:value-of select="number(0)" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="number($Number)" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<xsl:text>$</xsl:text>
		<xsl:choose>
			<xsl:when test="$Decimal = 2">
				<xsl:value-of select='format-number($NumberCorrect, "###,###,###,##0.00")' />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select='format-number($NumberCorrect, "###,###,###,##0.0000")' />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
