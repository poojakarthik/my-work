// Class: Telemarketing_DiallerReportUpload
// Handles the Telemarketing Dialler Report Upload page
var Telemarketing_DiallerReportUpload	= Class.create
({
	objPopupUpload	: {
						strId		: 'Telemarketing_DiallerReportUpload',
						strSize		: 'medium',
						strAlign	: 'centre',
						strNature	: 'modal',
						strTitle	: 'Upload Dialler Report'
					},
	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	updatePermittedVendors	: function()
	{
		// Purge all entries in the Vendor combo
		var elmVendorCombo		= $ID('Telemarketing_DiallerReportUpload_Vendor');
		for (i = elmVendorCombo.length-1; i >= 0; i--)
		{
			// Purge everything but the [None] option
			if (elmVendorCombo.options[i].text != '[None]')
			{
				elmVendorCombo.removeChild(elmVendorCombo.options[i]);
			}
			else
			{
				// Set [None] as selected
				elmVendorCombo.options[i].selected	= true;
			}
		}
		
		// Add all of this Call Centre's permitted Vendors
		var elmDealerCombo	= $ID('Telemarketing_DiallerReportUpload_Dealer');
		var intCallCentre	= elmDealerCombo.options[elmDealerCombo.selectedIndex].value;
		if (this._arrCallCentres[intCallCentre])
		{
			for (intCustomerGroupId in this._arrCallCentres[intCallCentre].customerGroupIds)
			{
				if (this._arrVendors[intCustomerGroupId] != undefined)
				{
					// Create the element
					elmVendorCombo.add(new Option(this._arrVendors[intCustomerGroupId].external_name, intCustomerGroupId), null);
				}
			}
		}
	},
	
	submit			: function()
	{
		// Ensure that all fields are populated
		var arrErrors	= new Array();
		
		if ($ID('Telemarketing_DiallerReportUpload_Dealer').selectedIndex < 1)
		{
			arrErrors.push("[!] Please select a Call Centre");
		}
		if ($ID('Telemarketing_DiallerReportUpload_Vendor').selectedIndex < 1)
		{
			arrErrors.push("[!] Please select a Vendor");
		}
		if (!$ID('Telemarketing_DiallerReportUpload_File').value)
		{
			arrErrors.push("[!] Please select a valid Dialler Report file to upload");
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
		
		// Disable the buttons (including the [X] at in the popup titlebar)
		$ID('Telemarketing_DiallerReportUpload_Upload').style.display					= 'none';
		$ID('Telemarketing_DiallerReportUpload_Cancel').style.display					= 'none';
		$ID('VixenPopupTopBarClose__Telemarketing_DiallerReportUpload').style.display	= 'none';
		
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Uploading Dialler Report...", null, null, null, 100);
		
		// Perform AJAX query
		return Flex.Telemarketing.iframeFormSubmit($ID('Telemarketing_DiallerReportUpload_Form'), this.uploadReponseHandler.bind(this));
	},
	
	uploadReponseHandler	: function(objResponse)
	{
		// Close the Splash and File Upload Prompt
		Vixen.Popup.ClosePageLoadingSplash();
		Vixen.Popup.Close(this.objPopupUpload.strId);
		
		// Display confirmation popup
		$Alert(objResponse.Message.replace("\n", "<br />"), null, null, 'modal');
		return;
	},
	
	_renderPopup	: function(objPopup, strHTML, objResponse)
	{
		// Hide the 'Loading' Splash, and display the popup
		Vixen.Popup.ClosePageLoadingSplash();
		if (objResponse.Success)
		{
			// Render the popup
			Vixen.Popup.Create(
								objPopup.strId, 
								strHTML, 
								objPopup.strSize, 
								objPopup.strAlign, 
								objPopup.strNature, 
								objPopup.strTitle
							);
		}
		else
		{
			$Alert("Failed to open the '" + objPopup.strTitle + "' popup" + ((objResponse.ErrorMessage != undefined)? "<br />" + objResponse.ErrorMessage : ""), 'extralarge');
		}
	},
	
	displayPopupUpload	: function()
	{
		var remoteClass		= 'Telemarketing_Wash';
		var remoteMethod	= 'getCallCentrePermissions';
		var jsonFunc		= jQuery.json.jsonFunction(this._renderPopupUpload.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	},
	
	_renderPopupUpload	: function(objResponse)
	{
		if (objResponse.HasPermissions === false)
		{
			$Alert("You do not have sufficient privileges to upload a Dialler Report.");
			return false;
		}
		
		this._arrCallCentres	= objResponse.arrCallCentrePermissions;
		this._arrVendors		= objResponse.arrVendors;
		
		// Generate Call Centre List
		var strDealerListHTML	= '';
		if (!this._arrCallCentres.each)
		{
			for (intDealerId in this._arrCallCentres)
			{
				strDealerListHTML	+= "<option value='" + intDealerId + "'>" + this._arrCallCentres[intDealerId].firstName + " " + this._arrCallCentres[intDealerId].lastName + "</option>\n";
			}
		}
		
		// Generate Popup HTML
		var strHTML	= "\n" + 
		"<form id='Telemarketing_DiallerReportUpload_Form' name='Telemarketing_DiallerReportUpload_Form' method='post' action='../admin/reflex.php/Telemarketing/UploadDiallerReport/' enctype='multipart/form-data' onsubmit='return Flex.Telemarketing.DiallerReportUpload.submit()' >\n" + 
		"	<div class='GroupedContent'>\n" + 
		"		<table class='form-data' style='width:100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td>Dealer:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_DiallerReportUpload_Dealer' name='Telemarketing_DiallerReportUpload_Dealer' onchange='Flex.Telemarketing.DiallerReportUpload.updatePermittedVendors()'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"							" + strDealerListHTML + "\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"				<tr>\n" + 
		"					<td>Vendor:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_DiallerReportUpload_Vendor' name='Telemarketing_DiallerReportUpload_Vendor'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"				<tr>\n" + 
		"					<td>File to import:</td>\n" + 
		"					<td>\n" + 
		"						<input type='file' id='Telemarketing_DiallerReportUpload_File' name='Telemarketing_DiallerReportUpload_File' />\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" + 
		"	</div>\n" + 
		"	<div style='width:100%; margin: 0 auto; text-align:center;'>\n" +
		"		<input type='submit' class='normal-button' id='Telemarketing_DiallerReportUpload_Upload' value='Upload' style='margin-left:3px' /> \n" +
		"		<input type='button' id='Telemarketing_DiallerReportUpload_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px' /> \n" + 
		"	</div>\n" + 
		"</form>\n\n";
		
		// Render the Popup
		this._renderPopup(this.objPopupUpload, strHTML, objResponse);
	}
});

Flex.Telemarketing.DiallerReportUpload = (Flex.Telemarketing.DiallerReportUpload == undefined) ? new Telemarketing_DiallerReportUpload() : Flex.Telemarketing.DiallerReportUpload;