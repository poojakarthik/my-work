// Class: Plan
// Handles the Plans in Flex
var Plan	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupSetBrochure	= new Reflex_Popup(40);
		this.pupSetBrochure.setTitle('Attach Plan Brochure');
		this.pupSetBrochure.addCloseButton();
		
		this.pupSetAuthScript	= new Reflex_Popup(40);
		this.pupSetAuthScript.setTitle('Attach Plan Authorisation Script');
		this.pupSetAuthScript.addCloseButton();
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
			jQuery.json.errorPopup(objResponse);
		}
	},
	
	setAuthScript	: function(intRatePlanId, strRatePlanName, strCustomerGroup)
	{
		// Render Authorisation Script Upload Popup
		var strHTML	= "\n" +
		"<form id='Plan_SetAuthScript_Form' name='Plan_SetAuthScript_Form' method='post' action='../admin/reflex.php/RatePlan/SetAuthScript/' enctype='multipart/form-data' onsubmit='return Flex.Plan._setAuthScriptSubmit()' >\n" +
		"	<input name='Plan_SetAuthScript_RatePlanId' type='hidden' value='"+intRatePlanId+"' />\n" + 
		"	<div class='GroupedContent'>\n" + 
		"		<div>\n" + 
		"			<span>Please select the Authorisation Script text file for '"+strRatePlanName+"' for "+strCustomerGroup+":</span>\n" + 
		"		</div>\n" + 
		"		<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td style='text-align:left;'>Authorisation Script Text File</td>\n" + 
		"					<td style='text-align:right;'><input type='file' id='Plan_SetAuthScript_File' name='Plan_SetAuthScript_File' /></td>\n" +  
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" +  
		"	</div>\n" + 
		"	<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"		<input id='Plan_SetAuthScript_Submit' value='Submit' type='submit' /> \n" + 
		"		<input id='Plan_SetAuthScript_Cancel' value='Cancel' onclick='Flex.Plan.pupSetAuthScript.hide();' style='margin-left: 3px;' type='button' /> \n" + 
		"	</div>\n" +
		"</form>\n";
		
		this.pupSetAuthScript.setContent(strHTML);
		this.pupSetAuthScript.display();
		
		return;
	},
	
	_setAuthScriptSubmit	: function()
	{
		// Ensure that all fields are populated
		var arrErrors	= new Array();
		
		if (!$ID('Plan_SetAuthScript_File').value)
		{
			arrErrors.push("[!] Please select a valid Authorisation Script text file to upload");
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
		Vixen.Popup.ShowPageLoadingSplash("Uploading Authorisation Script...", null, null, null, 100);

		// Perform pseudo-AJAX query
		return jQuery.json.jsonIframeFormSubmit($ID('Plan_SetAuthScript_Form'), Flex.Plan._setAuthScriptResponse.bind(this));
	},
	
	_setAuthScriptResponse	: function(objResponse)
	{
		// Close the Loading Splash & Popup
		Vixen.Popup.ClosePageLoadingSplash();
		this.pupSetAuthScript.hide();
		
		// Display response message
		if (objResponse.Success)
		{
			$Alert("The Authorisation Script was successfully uploaded", null, null, 'autohide-reload');
		}
		else
		{
			jQuery.json.errorPopup(objResponse);
		}
	},
	
	// getForAccount: JSON Handler wrapper
	getForAccount : function(iAccountId, bReturnArchived, fnCallback, oResponse) {
		if (typeof oResponse == 'undefined') {
			// Make request
			var fnResponse		= this.getForAccount.bind(this, iAccountId, bReturnArchived, fnCallback);
			var fnGetForAccount	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Rate_Plan', 'getForAccount');
			fnGetForAccount(iAccountId, bReturnArchived);
		} else {
			// Handle response
			if (!oResponse.bSuccess) {
				jQuery.json.errorPopup(oResponse);
				fnCallback(null);
				return;
			}
			
			fnCallback(oResponse.aRatePlans);
		}
	},
	
	// getForCustomerGroup: JSON Handler wrapper
	getForCustomerGroup	: function(iCustomerGroupId, bReturnArchived, fnCallback, oResponse) {
		if (typeof oResponse == 'undefined') {
			// Make request
			var fnResponse				= this.getForCustomerGroup.bind(this, iCustomerGroupId, bReturnArchived, fnCallback);
			var fnGetForCustomerGroup	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Rate_Plan', 'getForCustomerGroup');
			fnGetForCustomerGroup(iCustomerGroupId, bReturnArchived);
		} else {
			// Handle response
			if (!oResponse.bSuccess) {
				jQuery.json.errorPopup(oResponse);
				fnCallback(null);
				return;
			}
			
			fnCallback(oResponse.aRatePlans);
		}
	},
	
	// getForId: JSON Handler wrapper
	getForId : function(iId, fnCallback, oResponse) {
		if (typeof oResponse == 'undefined') {
			// Make request
			var fnResponse	= this.getForId.bind(this, iId, fnCallback);
			var fnGetForId	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Rate_Plan', 'getForId');
			fnGetForId(iId);
		} else {
			// Handle response
			if (!oResponse.bSuccess) {
				jQuery.json.errorPopup(oResponse);
				fnCallback(null);
				return;
			}
			
			fnCallback(oResponse.oRatePlan);
		}
	}
});

Flex.Plan = (Flex.Plan == undefined) ? new Plan() : Flex.Plan;