// Class: Plan
// Handles the Plans in Flex
var Plan	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupSetBrochure	= new Reflex_Popup(40);
		this.pupSetBrochure.setTitle('Internal Contact List');
		this.pupSetBrochure.addCloseButton();
		/*
		var elmCloseButton			= document.createElement('input');
		elmCloseButton.type			= 'button';
		elmCloseButton.value		= 'Close';
		elmCloseButton.onclick		= this.pupSetBrochure.hide.bind(this.pupSetBrochure);
		this.pupSetBrochure.setFooterButtons(new Array(elmCloseButton), true);
		*/
	},
	
	setBrochure	: function(intRatePlanId, strRatePlanName, strCustomerGroup)
	{
		// Render Brochure Upload Popup
		var strHTML	= "\n" +
		"<form id='Plan_SetBrochure_Form' name='Plan_SetBrochure_Form' method='post' action='../admin/reflex.php/RatePlan/SetBrochure/' enctype='multipart/form-data' onsubmit='return Flex.Plan._setBrochureSubmit()' >\n" +
		"	<input name='Plan_SetBrochure_RatePlanId' type='hidden' value='"+intRatePlanId+"' />\n" + 
		"	<div class='GroupedContent'>\n" + 
		"		<div>\n" + 
		"			<span>Please select the PDF brochure for '"+strRatePlanName+"' for "+strCustomerGroup+":</span>\n" + 
		"		</div>\n" + 
		"		<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td style='text-align:left;'>Brochure PDF File</td>\n" + 
		"					<td style='text-align:right;'><input type='file' id='Plan_SetBrochure_File' name='Plan_SetBrochure_File' /></td>\n" +  
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" +  
		"	</div>\n" + 
		"	<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"		<input id='Plan_SetBrochure_Submit' value='Submit' type='submit' /> \n" + 
		"		<input id='Plan_SetBrochure_Cancel' value='Cancel' onclick='Flex.Plan.pupSetBrochure.hide();' style='margin-left: 3px;' type='button' /> \n" + 
		"	</div>\n" +
		"</form>\n";
		
		this.pupSetBrochure.setContent(strHTML);
		this.pupSetBrochure.display();
		
		return;
	},
	
	_setBrochureSubmit	: function()
	{
		// Ensure that all fields are populated
		var arrErrors	= new Array();
		
		if (!$ID('Plan_SetBrochure_File').value)
		{
			arrErrors.push("[!] Please select a valid PDF Brochure file to upload");
		}
		
		if (arrErrors.length)
		{
			var strError	= "There is an error with your input.  Please satisfy the following requirements before submitting again:<br />";
			for (i = 0; i < arrErrors.length; i++)
			{
				strError	+=  "<br />" + arrErrors[i];
			}
			$Alert(strError);
			return false;
		}
		
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Uploading Plan Brochure...", null, null, null, 100);

		// Perform pseudo-AJAX query
		return jQuery.json.jsonIframeFormSubmit($ID('Plan_SetBrochure_Form'), Flex.Plan._setBrochureResponse.bind(this));
	},
	
	_setBrochureResponse	: function(objResponse)
	{
		// Close the Loading Splash & Popup
		Vixen.Popup.ClosePageLoadingSplash();
		this.pupSetBrochure.hide();
		
		// Display response message
		if (objResponse.Success)
		{
			$Alert("The Brochure was successfully uploaded", null, null, 'autohide-reload');
		}
		else
		{
			$Alert(objResponse.Message);
		}
	},
});

Flex.Plan = (Flex.Plan == undefined) ? new Plan() : Flex.Plan;