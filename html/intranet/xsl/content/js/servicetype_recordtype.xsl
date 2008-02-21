<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" />
	<xsl:template match="/">
		
		var RecordTypes = new Array ();
		
		<xsl:for-each select="/Response/Types/ServiceTypes/ServiceType">
			<xsl:variable name="ServiceType" select="./Id" />
			
			RecordTypes [<xsl:value-of select="$ServiceType" />] = new Array ();
			
			<xsl:for-each select="/Response/Types/RecordTypes/Results/rangeSample/RecordType[ServiceType=$ServiceType]">
				
				RecordTypes [<xsl:value-of select="$ServiceType" />] [<xsl:value-of select="position () - 1" />] = new Option (
					"<xsl:value-of select="Name" />",
					"<xsl:value-of select="Id" />"
				);
				
			</xsl:for-each>
		</xsl:for-each>
		
		window.addEventListener (
			"load",
			function ()
			{
				var ServiceTypeCombo = document.getElementById ("ServiceType");
				var RecordTypeCombo = document.getElementById ("RecordType");
				
				function changeRecordType () {
					for (var i=RecordTypeCombo.options.length - 1; i >= 0; i--)
					{
						RecordTypeCombo.options [i] = null;
					}
					
					for (var i=0; i &lt; RecordTypes [ServiceTypeCombo.options[ServiceTypeCombo.selectedIndex].value].length; i++)
					{
						RecordTypeCombo.options [i] = new Option (
							RecordTypes [ServiceTypeCombo.options[ServiceTypeCombo.selectedIndex].value][i].text,
							RecordTypes [ServiceTypeCombo.options[ServiceTypeCombo.selectedIndex].value][i].value
						);
					}
					
					if (RecordTypes [ServiceTypeCombo.options[ServiceTypeCombo.selectedIndex].value].length != 0)
					{
						RecordTypeCombo.options [0].selected = true;
					}
				}
				
				changeRecordType ();
				ServiceTypeCombo.onchange = changeRecordType;
			},
			true
		);
		
	</xsl:template>
</xsl:stylesheet>
