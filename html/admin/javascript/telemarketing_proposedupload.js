// Class: Telemarketing_ProposedUpload
// Handles the Telemarketing Proposed FNN Upload page
var Telemarketing_ProposedUpload	= Class.create
({
	objPopupUpload	: {
						strId		: 'Telemarketing_ProposedUpload',
						strSize		: 'medium',
						strAlign	: 'centre',
						strNature	: 'modal',
						strTitle	: 'Upload Proposed Dialling List'
					},
	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	updatePermittedVendors	: function()
	{
		// Purge all entries in the Vendor combo
		var elmVendorCombo		= $ID('Telemarketing_ProposedUpload_Vendor');
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
		var elmDealerCombo	= $ID('Telemarketing_ProposedUpload_Dealer');
		var intCallCentre	= elmDealerCombo.options[elmDealerCombo.selectedIndex].value;
		if (this._arrCallCentres[intCallCentre])
		{
			for (intCustomerGroupId in this._arrCallCentres[intCallCentre].customerGroupIds)
			{
				if (this._arrVendors[intCustomerGroupId] != undefined)
				{
					// Create the element
					elmVendorCombo.add(new Option(this._arrVendors[intCustomerGroupId].externalName, intCustomerGroupId), null);
				}
			}
		}
	},
	
	submit			: function()
	{
		// Ensure that all fields are populated
		var arrErrors	= new Array();
		
		if ($ID('Telemarketing_ProposedUpload_Dealer').selectedIndex < 1)
		{
			arrErrors.push("[!] Please select a Call Centre");
		}
		if ($ID('Telemarketing_ProposedUpload_Vendor').selectedIndex < 1)
		{
			arrErrors.push("[!] Please select a Vendor");
		}
		if (!$ID('Telemarketing_ProposedUpload_File').value)
		{
			arrErrors.push("[!] Please select a valid Proposed Dialling List file to upload");
		}
		
		if (arrErrors.length)
		{
			var strError	= "There is an error with your input.  Please satisfy the following requirements before submitting again:\n";
			for (i = 0; i < arrErrors.length; i++)
			{
				strError	+=  "\n\t" + arrErrors[i];
			}
			$Alert(strError);
			return false;
		}
		
		// Perform AJAX query
		alert("This is where the AJAX voodoo will happen");
		return true;
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
			$Alert("Failed to open the '" + objPopup.strTitle + "' popup" + ((objResponse.ErrorMessage != undefined)? "<br />" + objResponse.ErrorMessage : ""));
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
		this._arrCallCentres	= objResponse.arrCallCentrePermissions;
		this._arrVendors		= objResponse.arrVendors;
		
		// Generate Call Centre List
		var strDealerListHTML	= '';
		for (intDealerId in this._arrCallCentres)
		{
			strDealerListHTML	+= "<option value='" + intDealerId + "'>" + this._arrCallCentres[intDealerId].firstName + " " + this._arrCallCentres[intDealerId].lastName + "</option>\n";
		}
		
		// Generate Popup HTML
		var strHTML	= "\n" + 
		"<form method='post' action='' enctype='multipart/form-data'>\n" + 
		"	<div class='GroupedContent'>\n" + 
		"		<table class='form-data' style='width:100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td>Dealer:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_ProposedUpload_Dealer' name='dealer_id' onchange='Flex.Telemarketing.ProposedUpload.updatePermittedVendors()'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"							" + strDealerListHTML + "\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"				<tr>\n" + 
		"					<td>Vendor:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_ProposedUpload_Vendor' name='vendor_id'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"				<tr>\n" + 
		"					<td>File to wash:</td>\n" + 
		"					<td>\n" + 
		"						<input type='file' id='Telemarketing_ProposedUpload_File' name='file_data' />\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" + 
		"	</div>\n" + 
		"	<div style='width:100%; margin: 0 auto; text-align:center;'>\n" + 
		"		<input type='button' id='Telemarketing_ProposedUpload_Submit' value='Submit' onclick='Flex.Telemarketing.ProposedUpload.submit()' style='margin-left:3px'></input> \n" + 
		"		<input type='button' id='Telemarketing_ProposedUpload_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px'></input>\n" + 
		"	</div>\n" + 
		"</form>\n\n";
		
		// Render the Popup
		this._renderPopup(this.objPopupUpload, strHTML, objResponse);
		
		// Set an Event Handler for the Dealer Combo
		/*Event.observe($ID('Telemarketing_ProposedUpload_Dealer'), 'change', this.updatePermittedVendors.bind(this));
		Event.observe($ID('Telemarketing_ProposedUpload_Dealer'), 'click', this.updatePermittedVendors.bind(this));
		Event.observe($ID('Telemarketing_ProposedUpload_Dealer'), 'keyup', this.updatePermittedVendors.bind(this));*/
	}
});

Flex.Telemarketing.ProposedUpload = (Flex.Telemarketing.ProposedUpload == undefined) ? new Telemarketing_ProposedUpload() : Flex.Telemarketing.ProposedUpload;