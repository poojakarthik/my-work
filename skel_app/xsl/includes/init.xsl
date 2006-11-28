<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template name="Label">
		<xsl:param name="entity" />
		<xsl:param name="field" />
		
		<xsl:variable name="Element" select="/Response/Documentation/Entity/Fields/Field[Entity=$entity][Field=$field]" />
		
		<a href="#" class="Label">
			<xsl:attribute name="title">
				<xsl:text></xsl:text>
				<xsl:value-of select="$Element/Title" />
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
			</xsl:when>
			<xsl:when test="$DataType = 'String'">
				<select>
					<xsl:attribute name="name">
						<xsl:text></xsl:text>
						<xsl:value-of select="$Name" />
					</xsl:attribute>
					<option value="LIKE">Contains ...</option>
				</select>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>Unknown Data Type</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
